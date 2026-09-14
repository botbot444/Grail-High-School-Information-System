<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TimetableController extends Controller
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function index(Request $request)
    {
        $classes = SchoolClass::with('gradeLevel')->orderBy('class_name')->get();
        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $this->resolveTerm($request, $terms);
        $schoolClass = $this->resolveClass($request, $classes);

        $periods = $schoolClass?->gradeLevel?->periods()->orderBy('order')->get() ?? collect();
        $slots = $schoolClass && $term
            ? TimetableSlot::with(['subject', 'teacher', 'period'])
                ->where('school_class_id', $schoolClass->class_id)
                ->where('term_id', $term->term_id)
                ->get()
                ->keyBy(fn ($slot) => $slot->day_of_week.'-'.$slot->period_id)
            : collect();

        return view('admin.timetable.builder', [
            'classes' => $classes,
            'terms' => $terms,
            'schoolClass' => $schoolClass,
            'term' => $term,
            'periods' => $periods,
            'days' => self::DAYS,
            'slots' => $slots,
            'subjects' => $schoolClass?->subjects()->orderBy('subject_name')->get() ?? collect(),
            'teachers' => \App\Models\Teacher::orderBy('first_name')->orderBy('last_name')->get(),
        ]);
    }

    /**
     * Save the whole weekly grid for one class/term in a single request.
     *
     * The builder view submits every period/day cell at once (see
     * timetable/builder.blade.php) rather than one form per cell, so this
     * writes the full set of changes in one transaction instead of forcing
     * the admin to click a separate "Save" per cell.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,class_id'],
            'term_id' => ['required', 'exists:terms,term_id'],
            'cells' => ['nullable', 'array'],
            'cells.*' => ['array'],
            'cells.*.*.subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'subject_id')],
            'cells.*.*.teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'teacher_id')],
        ]);

        $classId = (int) $data['school_class_id'];
        $termId = (int) $data['term_id'];
        $cells = $data['cells'] ?? [];

        // Every selected subject must actually be offered by this class —
        // checked up front so a bad cell fails the whole save with a clear
        // message, rather than writing some cells and silently skipping others.
        $classSubjectIds = ClassSubject::where('class_id', $classId)->pluck('subject_id')->all();
        foreach ($cells as $day => $periodCells) {
            if (! in_array($day, self::DAYS, true)) {
                continue;
            }
            foreach ($periodCells as $cell) {
                $subjectId = $cell['subject_id'] ?? null;
                if ($subjectId && ! in_array((int) $subjectId, $classSubjectIds, true)) {
                    return back()->withErrors([
                        'timetable' => 'One or more selected subjects are not assigned to this class. No changes were saved.',
                    ])->withInput();
                }
            }
        }

        DB::transaction(function () use ($cells, $classId, $termId): void {
            foreach ($cells as $day => $periodCells) {
                if (! in_array($day, self::DAYS, true)) {
                    continue;
                }

                foreach ($periodCells as $periodId => $cell) {
                    $periodId = (int) $periodId;
                    $subjectId = $cell['subject_id'] ?? null;
                    $teacherId = $cell['teacher_id'] ?? null;

                    $existing = TimetableSlot::where([
                        'school_class_id' => $classId,
                        'day_of_week' => $day,
                        'period_id' => $periodId,
                        'term_id' => $termId,
                    ])->first();

                    if (! $subjectId && ! $teacherId) {
                        $existing?->delete();

                        continue;
                    }

                    $payload = [
                        'school_class_id' => $classId,
                        'day_of_week' => $day,
                        'period_id' => $periodId,
                        'term_id' => $termId,
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacherId,
                    ];

                    if ($existing) {
                        $existing->update($payload);
                    } else {
                        TimetableSlot::create($payload);
                    }
                }
            }
        });

        return redirect()->route('admin.timetable.index', ['class_id' => $classId, 'term_id' => $termId])
            ->with('notification', 'Timetable saved.');
    }

    public function copyToTerm(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,class_id'],
            'source_term_id' => ['required', 'exists:terms,term_id'],
            'target_term_id' => ['required', 'different:source_term_id', 'exists:terms,term_id'],
        ]);

        $targetExists = TimetableSlot::where('school_class_id', $validated['school_class_id'])
            ->where('term_id', $validated['target_term_id'])->exists();
        if ($targetExists) {
            return back()->withErrors('The target term already has timetable slots for this class.');
        }

        DB::transaction(function () use ($validated): void {
            $slots = TimetableSlot::where('school_class_id', $validated['school_class_id'])
                ->where('term_id', $validated['source_term_id'])->get();
            foreach ($slots as $slot) {
                TimetableSlot::create($slot->only([
                    'school_class_id', 'subject_id', 'teacher_id', 'period_id', 'day_of_week',
                ]) + ['term_id' => $validated['target_term_id']]);
            }
        });

        return back()->with('notification', 'Timetable copied to the selected term.');
    }

    public function clear(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,class_id'],
            'term_id' => ['required', 'exists:terms,term_id'],
        ]);

        TimetableSlot::where('school_class_id', $validated['school_class_id'])
            ->where('term_id', $validated['term_id'])
            ->get()
            ->each->delete();

        return back()->with('notification', 'The selected timetable was cleared.');
    }

    private function resolveTerm(Request $request, $terms): ?Term
    {
        if ($request->filled('term_id')) {
            return $terms->firstWhere('term_id', (int) $request->term_id) ?? Term::current();
        }

        return Term::current() ?? $terms->first();
    }

    private function resolveClass(Request $request, $classes): ?SchoolClass
    {
        return $request->filled('class_id')
            ? $classes->firstWhere('class_id', (int) $request->class_id)
            : $classes->first();
    }
}