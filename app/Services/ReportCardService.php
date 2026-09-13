<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\ReportCardComment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Term;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Phase 11 — the single implementation of report card assembly and the
 * finalize-grades workflow.
 *
 * Every portal (admin, teacher, parent, student) renders from build() so the
 * same student's card cannot disagree between two screens, and so the term
 * averaging used here is the one Phase 9's reporting can reuse rather than
 * growing a second copy.
 */
class ReportCardService
{
    /**
     * Assemble everything a report card renders, for one student in one term.
     *
     * @return array{
     *     student: Student, term: Term, schoolClass: ?SchoolClass,
     *     subjects: Collection, attendance: array, termAverage: ?float,
     *     reportCard: ?ReportCard, nextTerm: ?Term
     * }
     */
    public function build(Student $student, Term $term): array
    {
        $student->loadMissing('schoolClass.gradeLevel');

        $subjects   = $this->subjectRows($student, $term);
        $average    = $this->averageOf($subjects);
        $reportCard = ReportCard::where('student_id', $student->student_id)
            ->where('term_id', $term->term_id)
            ->with('finalizedByTeacher')
            ->first();

        return [
            'student'     => $student,
            'term'        => $term,
            'schoolClass' => $student->schoolClass,
            'subjects'    => $subjects,
            'attendance'  => $this->attendanceSummary($student, $term),
            'termAverage' => $average,
            'reportCard'  => $reportCard,
            'nextTerm'    => $this->nextTerm($term),
        ];
    }

    /**
     * One row per subject the student took this term: CA, exam, total, letter
     * grade and the subject teacher's comment.
     */
    public function subjectRows(Student $student, Term $term): Collection
    {
        $grades = Grade::where('student_id', $student->student_id)
            ->inTerm($term)
            ->with(['classSubject.subject', 'classSubject.teacher'])
            ->get();

        $comments = ReportCardComment::where('student_id', $student->student_id)
            ->where('term_id', $term->term_id)
            ->get()
            ->keyBy('class_subject_id');

        return $grades
            ->groupBy('class_subject_id')
            ->map(function (Collection $subjectGrades, $classSubjectId) use ($comments) {
                $classSubject = $subjectGrades->first()->classSubject;

                $ca   = $subjectGrades->firstWhere('assessment_type', 'CA');
                $exam = $subjectGrades->firstWhere('assessment_type', 'EXAM');

                // Total is the mean of whichever components exist, as a
                // percentage, so a CA-only subject still reports sensibly.
                $components = collect([$ca, $exam])
                    ->filter()
                    ->filter(fn (Grade $g) => (float) $g->max_score > 0);

                $total = $components->isNotEmpty()
                    ? round($components->avg(fn (Grade $g) => $g->percentage), 1)
                    : null;

                return [
                    'class_subject_id' => (int) $classSubjectId,
                    'subject'          => $classSubject?->subject?->subject_name ?? 'Unassigned',
                    'teacher'          => $classSubject?->teacher?->full_name,
                    'ca_score'         => $ca?->score !== null ? (float) $ca->score : null,
                    'ca_max'           => $ca?->max_score !== null ? (float) $ca->max_score : null,
                    'exam_score'       => $exam?->score !== null ? (float) $exam->score : null,
                    'exam_max'         => $exam?->max_score !== null ? (float) $exam->max_score : null,
                    'total'            => $total,
                    'letter'           => $total !== null ? $this->letterFor($total) : null,
                    'comment'          => $comments->get($classSubjectId)?->comment,
                ];
            })
            ->sortBy('subject')
            ->values();
    }

    /** Mean of the per-subject totals, or null when nothing is marked. */
    public function averageOf(Collection $subjects): ?float
    {
        $totals = $subjects->pluck('total')->filter(fn ($t) => $t !== null);

        return $totals->isNotEmpty() ? round($totals->avg(), 2) : null;
    }

    /**
     * Days present/absent/late within the term window, against the term's
     * school-day count (Phase 3 already excludes weekends and holidays).
     */
    public function attendanceSummary(Student $student, Term $term): array
    {
        $records = Attendance::where('student_id', $student->student_id)
            ->when(
                $term->start_date && $term->end_date,
                fn ($q) => $q->whereBetween('date', [$term->start_date, $term->end_date])
            )
            ->get(['status', 'date']);

        // A student can have several subject registers per day; collapse to one
        // mark per calendar day so "days present" means days, not lessons.
        $byDay = $records->groupBy(fn ($r) => $r->date?->format('Y-m-d'));

        $present = $absent = $late = 0;

        foreach ($byDay as $day) {
            $statuses = $day->pluck('status');

            if ($statuses->contains('Absent') && ! $statuses->contains('Present') && ! $statuses->contains('Late')) {
                $absent++;
            } elseif ($statuses->contains('Late')) {
                $late++;
            } elseif ($statuses->contains('Present')) {
                $present++;
            }
        }

        $recorded  = $present + $absent + $late;
        $schoolDays = $term->school_days ?? 0;

        return [
            'present'     => $present,
            'absent'      => $absent,
            'late'        => $late,
            'recorded'    => $recorded,
            'school_days' => $schoolDays,
            'rate'        => $recorded > 0 ? round((($present + $late) / $recorded) * 100, 1) : null,
        ];
    }

    // ── Finalize workflow ─────────────────────────────────────────────────────

    /**
     * Students in a class who are missing marks for a subject taught to that
     * class this term. Finalizing is blocked while this is non-empty.
     *
     * @return Collection<int, array{student: Student, missing: array<int, string>}>
     */
    public function missingGrades(SchoolClass $class, Term $term): Collection
    {
        $classSubjects = ClassSubject::with('subject')
            ->where('class_id', $class->class_id)
            ->get();

        if ($classSubjects->isEmpty()) {
            return collect();
        }

        $students = Student::where('class_id', $class->class_id)->orderBy('last_name')->get();

        $graded = Grade::inTerm($term)
            ->whereIn('class_subject_id', $classSubjects->pluck('class_subject_id'))
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get()
            ->groupBy('student_id');

        return $students
            ->map(function (Student $student) use ($classSubjects, $graded) {
                $has = ($graded->get($student->student_id) ?? collect())
                    ->pluck('class_subject_id')
                    ->unique();

                $missing = $classSubjects
                    ->reject(fn (ClassSubject $cs) => $has->contains($cs->class_subject_id))
                    ->map(fn (ClassSubject $cs) => $cs->subject?->subject_name ?? 'Subject')
                    ->values()
                    ->all();

                return ['student' => $student, 'missing' => $missing];
            })
            ->filter(fn (array $row) => ! empty($row['missing']))
            ->values();
    }

    /**
     * Finalize a class's grades for a term: compute each student's average,
     * assign ranks, and write a report card per student.
     *
     * Ties share a rank and consume the positions below them (1, 1, 3, 4).
     *
     * @throws ValidationException when any student is missing marks
     */
    public function finalize(SchoolClass $class, Term $term, Teacher $teacher): int
    {
        $missing = $this->missingGrades($class, $term);

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'finalize' => sprintf(
                    '%d student(s) are missing marks — enter every subject before finalizing.',
                    $missing->count()
                ),
            ]);
        }

        $students = Student::where('class_id', $class->class_id)->get();

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'finalize' => 'This class has no students to finalize.',
            ]);
        }

        // Average first, then rank, so ranking works off one consistent pass.
        $averages = $students->mapWithKeys(fn (Student $student) => [
            $student->student_id => $this->averageOf($this->subjectRows($student, $term)),
        ]);

        $ranks = $this->rankFrom($averages);

        return DB::transaction(function () use ($students, $term, $class, $teacher, $averages, $ranks) {
            foreach ($students as $student) {
                $card = ReportCard::firstOrNew([
                    'student_id' => $student->student_id,
                    'term_id'    => $term->term_id,
                ]);

                $card->class_id     = $class->class_id;
                $card->term_average = $averages->get($student->student_id);
                $card->class_rank   = $ranks->get($student->student_id);
                $card->class_size   = $students->count();
                $card->finalized_at = now();
                $card->finalized_by = $teacher->teacher_id;
                $card->audit_reason = "Finalized {$class->class_name} for {$term->name}";
                $card->save();
            }

            return $students->count();
        });
    }

    /**
     * Admin override. Clears rank and finalization but keeps the comments, so
     * unfinalizing to fix one mark does not throw away teachers' writing.
     */
    public function unfinalize(SchoolClass $class, Term $term, string $reason): int
    {
        $cards = ReportCard::where('class_id', $class->class_id)
            ->where('term_id', $term->term_id)
            ->finalized()
            ->get();

        foreach ($cards as $card) {
            $card->class_rank   = null;
            $card->class_size   = null;
            $card->finalized_at = null;
            $card->finalized_by = null;
            $card->audit_reason = $reason;
            $card->save();
        }

        return $cards->count();
    }

    /** True when this class + term is locked against further mark entry. */
    public function isLocked(int $classId, int $termId): bool
    {
        return ReportCard::where('class_id', $classId)
            ->where('term_id', $termId)
            ->finalized()
            ->exists();
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /**
     * Competition ranking over student_id => average.
     * Equal averages share a rank; the next distinct average skips ahead.
     */
    private function rankFrom(Collection $averages): Collection
    {
        $sorted = $averages
            ->filter(fn ($avg) => $avg !== null)
            ->sortDesc();

        $ranks = collect();
        $position = 0;
        $awarded = 0;
        $previous = null;

        foreach ($sorted as $studentId => $average) {
            $position++;

            if ($previous === null || abs($average - $previous) > 0.0001) {
                $awarded = $position;
                $previous = $average;
            }

            $ranks->put($studentId, $awarded);
        }

        return $ranks;
    }

    /** Same thresholds as Grade::getLetterGradeAttribute(), applied to a total. */
    private function letterFor(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 75 => 'B+',
            $percentage >= 70 => 'B',
            $percentage >= 65 => 'C+',
            $percentage >= 60 => 'C',
            $percentage >= 50 => 'D',
            default           => 'F',
        };
    }

    /** The term that follows this one, for the "next term begins" line. */
    private function nextTerm(Term $term): ?Term
    {
        return Term::where('start_date', '>', $term->end_date)
            ->orderBy('start_date')
            ->first();
    }
}
