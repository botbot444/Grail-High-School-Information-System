<?php

namespace App\View\Composers;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\Term;
use Illuminate\View\View;

/**
 * Supplies the student portal chrome (sidebar profile card, header term badge,
 * assignments-due badge) to every student view, so individual controllers don't
 * each have to remember to pass them.
 */
class StudentPortalComposer
{
    private ?Student $student = null;

    private bool $resolved = false;

    public function compose(View $view): void
    {
        $student = $this->student();

        $view->with([
            'navStudent'         => $student,
            'navStudentInitials' => $student ? $this->initials($student) : null,
            'navTerm'            => Term::current(),
            'navDueAssignments'  => $student ? $this->dueCount($student) : null,
        ]);
    }

    private function student(): ?Student
    {
        if ($this->resolved) {
            return $this->student;
        }

        $this->resolved = true;

        if (! auth()->check()) {
            return $this->student = null;
        }

        return $this->student = Student::with('schoolClass')
            ->where('user_id', auth()->id())
            ->first();
    }

    private function initials(Student $student): string
    {
        return strtoupper(mb_substr($student->first_name ?? '', 0, 1) . mb_substr($student->last_name ?? '', 0, 1)) ?: 'S';
    }

    /**
     * Open assignments the student has not submitted yet. Shown as the red pill
     * on the Assignments nav item.
     */
    private function dueCount(Student $student): ?int
    {
        if (! $student->class_id) {
            return null;
        }

        $count = Assignment::published()
            ->forClass($student->class_id)
            ->whereDoesntHave('submissions', fn ($query) => $query->where('student_id', $student->student_id))
            ->where('due_at', '>=', now()->subMonths(3))
            ->count();

        return $count > 0 ? $count : null;
    }
}
