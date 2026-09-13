<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $existing = TimetableSlot::where([
            'school_class_id' => $validated['school_class_id'],
            'day_of_week' => $validated['day_of_week'],
            'period_id' => $validated['period_id'],
            'term_id' => $validated['term_id'],
        ])->first();

        if (! $validated['subject_id'] && ! $validated['teacher_id']) {
            $existing?->delete();
        } elseif ($existing) {
            $existing->update($validated);
        } else {
            TimetableSlot::create($validated);
        }

        return back()->with('notification', 'Timetable cell saved.');
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

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,class_id'],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'subject_id')],
            'teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'teacher_id')],
            'period_id' => ['required', 'exists:periods,id'],
            'day_of_week' => ['required', Rule::in(self::DAYS)],
            'term_id' => ['required', 'exists:terms,term_id'],
        ]);

        if ($validated['subject_id'] && ! \App\Models\ClassSubject::where('class_id', $validated['school_class_id'])
            ->where('subject_id', $validated['subject_id'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'subject_id' => 'The selected subject is not assigned to this class.',
            ]);
        }

        return $validated;
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