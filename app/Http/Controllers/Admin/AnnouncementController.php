<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Phase 5 — admin authoring.
 *
 * Announcements are admin-only by design: teachers have no authoring route.
 */
class AnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementService $service)
    {
    }

    public function index(Request $request): View
    {
        $announcements = Announcement::with(['targets.targetable', 'author'])
            ->withCount('reads')
            ->when($request->filled('audience'), fn ($q) => $q->where('audience', $request->audience))
            ->orderByDesc('created_at')
            ->get();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'counts' => [
                'live'      => $announcements->where('state', 'Live')->count(),
                'draft'     => $announcements->where('state', 'Draft')->count(),
                'scheduled' => $announcements->where('state', 'Scheduled')->count(),
                'expired'   => $announcements->where('state', 'Expired')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', $this->formData(new Announcement([
            'audience'     => Announcement::AUDIENCE_ALL,
            'published_at' => now(),
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $announcement = DB::transaction(function () use ($validated) {
            $announcement = Announcement::create([
                'title'        => $validated['title'],
                'body'         => $validated['body'],
                'audience'     => $validated['audience'],
                'published_at' => $validated['published_at'] ?? null,
                'expires_at'   => $validated['expires_at'] ?? null,
                'created_by'   => auth()->id(),
            ]);

            $this->syncTargets($announcement, $validated);

            return $announcement;
        });

        return redirect()
            ->route('admin.announcements.index')
            ->with('notification', "Announcement \"{$announcement->title}\" saved — {$announcement->state}.");
    }

    public function edit(Announcement $announcement): View
    {
        $announcement->load('targets');

        return view('admin.announcements.edit', $this->formData($announcement));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($announcement, $validated) {
            $announcement->update([
                'title'        => $validated['title'],
                'body'         => $validated['body'],
                'audience'     => $validated['audience'],
                'published_at' => $validated['published_at'] ?? null,
                'expires_at'   => $validated['expires_at'] ?? null,
            ]);

            $this->syncTargets($announcement, $validated);
        });

        return redirect()
            ->route('admin.announcements.index')
            ->with('notification', "Announcement \"{$announcement->title}\" updated.");
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete(); // soft delete — the audit trail keeps the record

        return redirect()
            ->route('admin.announcements.index')
            ->with('notification', "Announcement \"{$title}\" removed.");
    }

    /**
     * "Who will see this?" — answers from the form's current selection, before
     * anything is saved, so the admin can check the aim before committing.
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audience'         => ['required', Rule::in(array_keys(Announcement::AUDIENCES))],
            'class_ids'        => ['array'],
            'class_ids.*'      => ['integer'],
            'grade_level_ids'  => ['array'],
            'grade_level_ids.*'=> ['integer'],
        ]);

        $reach = $this->service->reachSummary(
            $validated['audience'],
            $validated['class_ids'] ?? [],
            $validated['grade_level_ids'] ?? [],
        );

        return response()->json([
            'students' => $reach['students'],
            'parents'  => $reach['parents'],
            'classes'  => $reach['classes']->values(),
        ]);
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function formData(Announcement $announcement): array
    {
        return [
            'announcement'    => $announcement,
            'classes'         => SchoolClass::with('gradeLevel')->orderBy('class_name')->get(),
            'gradeLevels'     => GradeLevel::orderBy('order')->get(),
            'audiences'       => Announcement::AUDIENCES,
            'selectedClasses' => $announcement->exists
                ? $this->service->targetIds($announcement, SchoolClass::class)
                : [],
            'selectedLevels'  => $announcement->exists
                ? $this->service->targetIds($announcement, GradeLevel::class)
                : [],
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'body'         => ['required', 'string', 'max:10000'],
            'audience'     => ['required', Rule::in(array_keys(Announcement::AUDIENCES))],
            'published_at' => ['nullable', 'date'],
            'expires_at'   => ['nullable', 'date', 'after:published_at'],

            // At least one target is required unless the notice is school-wide —
            // otherwise it would be published to nobody.
            'class_ids'   => [
                Rule::requiredIf(fn () => $request->input('audience') === Announcement::AUDIENCE_CLASS),
                'array',
            ],
            'class_ids.*' => ['integer', 'exists:school_classes,class_id'],

            'grade_level_ids'   => [
                Rule::requiredIf(fn () => $request->input('audience') === Announcement::AUDIENCE_GRADE_LEVEL),
                'array',
            ],
            'grade_level_ids.*' => ['integer', 'exists:grade_levels,grade_level_id'],
        ], [
            'class_ids.required'       => 'Choose at least one class, or change the audience to the whole school.',
            'grade_level_ids.required' => 'Choose at least one grade level, or change the audience to the whole school.',
            'expires_at.after'         => 'The expiry date has to fall after the publish date.',
        ]);
    }

    /** Replace the target rows to match the submitted selection. */
    private function syncTargets(Announcement $announcement, array $validated): void
    {
        $announcement->targets()->delete();

        if ($announcement->audience === Announcement::AUDIENCE_ALL) {
            return;
        }

        $rows = $announcement->audience === Announcement::AUDIENCE_CLASS
            ? collect($validated['class_ids'] ?? [])->map(fn ($id) => [
                'targetable_type' => SchoolClass::class,
                'targetable_id'   => (int) $id,
            ])
            : collect($validated['grade_level_ids'] ?? [])->map(fn ($id) => [
                'targetable_type' => GradeLevel::class,
                'targetable_id'   => (int) $id,
            ]);

        foreach ($rows as $row) {
            AnnouncementTarget::create($row + ['announcement_id' => $announcement->announcement_id]);
        }
    }
}
