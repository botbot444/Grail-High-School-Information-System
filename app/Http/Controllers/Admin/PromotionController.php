<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PromotionMapping;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentPromotion;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Phase 6 — year-end promotion, admin only.
 */
class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $service)
    {
    }

    /** Overview: pick a class to promote, see the guards, review past batches. */
    public function index(Request $request): View
    {
        $years = AcademicYear::orderByDesc('start_date')->get();
        $targetYear = $request->filled('academic_year_id')
            ? $years->firstWhere('year_id', (int) $request->academic_year_id)
            : $years->first();

        $classes = SchoolClass::with('gradeLevel')
            ->withCount(['students' => fn ($q) => $q->where('status', Student::STATUS_ENROLLED)])
            ->orderBy('class_name')
            ->get();

        $mappings = PromotionMapping::all();

        return view('admin.promotions.index', [
            'years'      => $years,
            'targetYear' => $targetYear,
            'classes'    => $classes->map(fn (SchoolClass $class) => [
                'class'   => $class,
                'default' => $this->service->defaultFor($class, $mappings, $classes),
            ]),
            'blockers'   => $this->service->blockers($targetYear),
            'batches'    => $this->service->batches(),
            'allClasses' => $classes,
        ]);
    }

    /** The run screen for one class: every enrolled student, with a default choice. */
    public function show(Request $request, int $class): View
    {
        $schoolClass = SchoolClass::with('gradeLevel')->findOrFail($class);

        $years = AcademicYear::orderByDesc('start_date')->get();
        $targetYear = $request->filled('academic_year_id')
            ? $years->firstWhere('year_id', (int) $request->academic_year_id)
            : $years->first();

        $classes = SchoolClass::with('gradeLevel')->orderBy('class_name')->get();
        $default = $this->service->defaultFor($schoolClass, PromotionMapping::all(), $classes);

        $students = Student::where('class_id', $schoolClass->class_id)
            ->where('status', Student::STATUS_ENROLLED)
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        return view('admin.promotions.show', [
            'schoolClass' => $schoolClass,
            'students'    => $students,
            'classes'     => $classes,
            'years'       => $years,
            'targetYear'  => $targetYear,
            'default'     => $default,
            'blockers'    => $this->service->blockers($targetYear),
        ]);
    }

    public function store(Request $request, int $class): RedirectResponse
    {
        $schoolClass = SchoolClass::findOrFail($class);

        $validated = $request->validate([
            'academic_year_id'         => ['required', 'exists:academic_years,year_id'],
            'students'                 => ['required', 'array', 'min:1'],
            'students.*.outcome'       => ['required', Rule::in([
                StudentPromotion::PROMOTED,
                StudentPromotion::RETAINED,
                StudentPromotion::GRADUATED,
            ])],
            'students.*.to_class_id'   => ['nullable', 'integer', 'exists:school_classes,class_id'],
        ], [
            'students.required' => 'This class has no enrolled students to promote.',
        ]);

        $targetYear = AcademicYear::findOrFail((int) $validated['academic_year_id']);

        // Only students actually in this class may be moved by this screen.
        $allowed = Student::where('class_id', $schoolClass->class_id)
            ->where('status', Student::STATUS_ENROLLED)
            ->pluck('student_id')
            ->all();

        $decisions = collect($validated['students'])
            ->filter(fn ($row, $studentId) => in_array((int) $studentId, $allowed, true))
            ->map(fn ($row) => [
                'outcome'     => $row['outcome'],
                'to_class_id' => $row['to_class_id'] ?? null,
            ])
            ->all();

        try {
            $result = $this->service->run($decisions, $targetYear, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('admin.promotions.index', ['academic_year_id' => $targetYear->year_id])
            ->with('notification', sprintf(
                '%s: %d promoted, %d retained, %d graduated into %s.',
                $schoolClass->class_name,
                $result['promoted'],
                $result['retained'],
                $result['graduated'],
                $targetYear->label
            ));
    }

    public function rollback(Request $request, string $batch): RedirectResponse
    {
        try {
            $count = $this->service->rollback($batch, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('notification', "Rolled back {$count} student(s) to their previous class.");
    }

    // ── Mappings ──────────────────────────────────────────────────────────────

    public function mappings(): View
    {
        $classes = SchoolClass::with('gradeLevel')->orderBy('class_name')->get();

        return view('admin.promotions.mappings', [
            'classes'  => $classes,
            'mappings' => PromotionMapping::all()->keyBy('from_class_id'),
        ]);
    }

    public function saveMappings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mappings'                => ['array'],
            'mappings.*.to_class_id'  => ['nullable', 'integer', 'exists:school_classes,class_id'],
            'mappings.*.graduates'    => ['nullable', 'boolean'],
        ]);

        $classIds = SchoolClass::pluck('class_id');
        $saved = 0;

        foreach ($validated['mappings'] ?? [] as $fromClassId => $row) {
            if (! $classIds->contains((int) $fromClassId)) {
                continue;
            }

            $graduates = (bool) ($row['graduates'] ?? false);
            $toClassId = $graduates ? null : ($row['to_class_id'] ?? null);

            // A class mapped to itself would promote nobody anywhere.
            if (! $graduates && (int) $toClassId === (int) $fromClassId) {
                return back()->withErrors([
                    'mappings' => 'A class cannot be mapped to itself — use Retain on the promotion screen instead.',
                ]);
            }

            if (! $graduates && ! $toClassId) {
                PromotionMapping::where('from_class_id', $fromClassId)->delete();

                continue;
            }

            PromotionMapping::updateOrCreate(
                ['from_class_id' => (int) $fromClassId],
                ['to_class_id' => $toClassId, 'graduates' => $graduates]
            );

            $saved++;
        }

        return back()->with('notification', "Saved {$saved} promotion mapping(s).");
    }
}
