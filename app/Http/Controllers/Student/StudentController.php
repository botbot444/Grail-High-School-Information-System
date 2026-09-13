<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Term;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Student portal.
 *
 * Every action resolves the student from the authenticated user rather than
 * from a route parameter, so there is no student id a user could tamper with.
 */
class StudentController extends Controller
{
    /** Resolve the signed-in user's student record, eager-loading what the page needs. */
    private function currentStudent(array $with = []): Student
    {
        return Student::with($with)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        $student = $this->currentStudent(['schoolClass.gradeLevel']);

        // NB: `term` is a plain string column on grades and shadows the term()
        // relation, so it is not eager-loaded here — filter on term_id instead.
        $results = $student->grades()
            ->with(['classSubject.subject'])
            ->latest()
            ->take(6)
            ->get();

        $attendance = $this->attendanceTotals($student);

        $termAverage = $student->grades()
            ->get()
            ->filter(fn ($grade) => (float) $grade->max_score > 0)
            ->avg(fn ($grade) => $grade->percentage);

        $totalFees  = (float) $student->fees()->sum('amount_due');
        $paidFees   = (float) $student->fees()->sum('amount_paid');
        $feeBalance = $totalFees - $paidFees;

        $upcoming = \App\Models\Assignment::published()
            ->when($student->class_id, fn ($q) => $q->forClass($student->class_id))
            ->with(['classSubject.subject', 'submissions' => fn ($q) => $q->where('student_id', $student->student_id)])
            ->where('due_at', '>=', now()->startOfDay())
            ->orderBy('due_at')
            ->take(4)
            ->get();

        $todaySlots = $this->todaysSlots($student);

        return view('student.dashboard', [
            'student'       => $student,
            'results'       => $results,
            'attendance'    => $attendance,
            'termAverage'   => $termAverage !== null ? round($termAverage, 1) : null,
            'feeBalance'    => $feeBalance,
            'totalFees'     => $totalFees,
            'feeStatus'     => $feeBalance > 0 ? 'Outstanding' : 'Cleared',
            'upcoming'      => $upcoming,
            'todaySlots'    => $todaySlots,
        ]);
    }

    // ── Results ───────────────────────────────────────────────────────────────

    public function results(Request $request): View
    {
        $student = $this->currentStudent(['schoolClass']);

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term  = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : null;

        $grades = $student->grades()
            ->with(['classSubject.subject', 'classSubject.teacher'])
            ->when($term, fn ($q) => $q->where('term_id', $term->term_id))
            ->get();

        // One row per subject, splitting continuous assessment from exams.
        $bySubject = $grades
            ->groupBy(fn ($grade) => $grade->classSubject?->subject?->subject_name ?? 'Unassigned')
            ->map(function ($subjectGrades, $subjectName) {
                $ca   = $subjectGrades->where('assessment_type', 'CA');
                $exam = $subjectGrades->where('assessment_type', 'EXAM');
                $overall = $subjectGrades->filter(fn ($g) => (float) $g->max_score > 0)->avg(fn ($g) => $g->percentage);

                return [
                    'subject'     => $subjectName,
                    'teacher'     => $subjectGrades->first()?->classSubject?->teacher?->full_name,
                    'ca_average'  => $ca->isNotEmpty() ? round($ca->avg(fn ($g) => $g->percentage), 1) : null,
                    'exam_average'=> $exam->isNotEmpty() ? round($exam->avg(fn ($g) => $g->percentage), 1) : null,
                    'overall'     => $overall !== null ? round($overall, 1) : null,
                    'entries'     => $subjectGrades->sortByDesc('created_at')->values(),
                ];
            })
            ->sortBy('subject')
            ->values();

        $overallAverage = $grades->filter(fn ($g) => (float) $g->max_score > 0)->avg(fn ($g) => $g->percentage);

        return view('student.results', [
            'student'        => $student,
            'terms'          => $terms,
            'term'           => $term,
            'bySubject'      => $bySubject,
            'overallAverage' => $overallAverage !== null ? round($overallAverage, 1) : null,
        ]);
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    public function attendance(Request $request): View
    {
        $student = $this->currentStudent(['schoolClass']);

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term  = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : Term::current();

        $query = $student->attendance()->with('classSubject.subject');

        if ($term && $term->start_date && $term->end_date) {
            $query->whereBetween('date', [$term->start_date, $term->end_date]);
        }

        $records = $query->orderByDesc('date')->get();

        $summary = [
            'present' => $records->where('status', 'Present')->count(),
            'absent'  => $records->where('status', 'Absent')->count(),
            'late'    => $records->where('status', 'Late')->count(),
            'total'   => $records->count(),
        ];
        $summary['rate'] = $summary['total'] > 0
            ? round(($summary['present'] / $summary['total']) * 100, 1)
            : null;

        return view('student.attendance', [
            'student'  => $student,
            'terms'    => $terms,
            'term'     => $term,
            'records'  => $records->take(120),
            'summary'  => $summary,
            'byMonth'  => $records->groupBy(fn ($r) => $r->date?->format('Y-m'))->map->count(),
        ]);
    }

    // ── Timetable ─────────────────────────────────────────────────────────────

    public function timetable(Request $request): View
    {
        $student = $this->currentStudent(['schoolClass.gradeLevel.periods']);

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term  = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : Term::current();
        $term ??= $terms->first();

        $slots = $student->class_id && $term
            ? TimetableSlot::with(['subject', 'teacher', 'period'])
                ->where('school_class_id', $student->class_id)
                ->where('term_id', $term->term_id)
                ->get()
            : collect();

        $periods = $student->schoolClass?->gradeLevel
            ? $student->schoolClass->gradeLevel->periods()->orderBy('order')->get()
            : collect();

        return view('student.timetable', compact('student', 'terms', 'term', 'slots', 'periods'));
    }

    // ── Report cards (Phase 11) ───────────────────────────────────────────────

    public function reportCards(): View
    {
        $student = $this->currentStudent(['schoolClass']);

        // Terms the student actually has grades for — the report card for each
        // becomes downloadable once Phase 11 wires dompdf to a layout.
        $terms = Term::with('academicYear')
            ->whereIn('term_id', $student->grades()->whereNotNull('term_id')->distinct()->pluck('term_id'))
            ->orderByDesc('start_date')
            ->get();

        return view('student.report-cards', compact('student', 'terms'));
    }

    // ── Announcements (Phase 5) ───────────────────────────────────────────────

    public function announcements(): View
    {
        return view('student.announcements', [
            'student' => $this->currentStudent(['schoolClass']),
        ]);
    }

    // ── Settings ──────────────────────────────────────────────────────────────

    public function settings(): View
    {
        return view('student.settings', [
            'student' => $this->currentStudent(['schoolClass.gradeLevel', 'user']),
        ]);
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function attendanceTotals(Student $student): array
    {
        $records = $student->attendance()->get(['status']);
        $total   = $records->count();
        $present = $records->where('status', 'Present')->count();

        return [
            'total'   => $total,
            'present' => $present,
            'absent'  => $records->where('status', 'Absent')->count(),
            'late'    => $records->where('status', 'Late')->count(),
            'rate'    => $total > 0 ? round(($present / $total) * 100, 1) : null,
        ];
    }

    /** Today's lessons, used by the dashboard "today" strip. */
    private function todaysSlots(Student $student)
    {
        $term = Term::current();

        if (! $student->class_id || ! $term) {
            return collect();
        }

        return TimetableSlot::with(['subject', 'teacher', 'period'])
            ->where('school_class_id', $student->class_id)
            ->where('term_id', $term->term_id)
            ->where('day_of_week', now()->format('l'))
            ->get()
            ->sortBy(fn ($slot) => $slot->period?->order ?? 0)
            ->values();
    }
}
