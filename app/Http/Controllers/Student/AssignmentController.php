<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Student-facing assignments: list, detail, and submission.
 *
 * Access control: an assignment is reachable only if it is published AND set for
 * a subject taught to this student's own class. Anything else 404s, so an id
 * from another class leaks nothing.
 */
class AssignmentController extends Controller
{
    private function currentStudent(): Student
    {
        return Student::with('schoolClass')
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    /** Published assignments visible to this student, or a 404. */
    private function visibleAssignment(Student $student, int $assignmentId): Assignment
    {
        abort_if(! $student->class_id, 404);

        return Assignment::published()
            ->forClass($student->class_id)
            ->with(['classSubject.subject', 'classSubject.teacher', 'term'])
            ->where('assignment_id', $assignmentId)
            ->firstOrFail();
    }

    public function index(Request $request): View
    {
        $student = $this->currentStudent();

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term  = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : null;

        $assignments = collect();
        $subjects    = collect();

        if ($student->class_id) {
            $assignments = Assignment::published()
                ->forClass($student->class_id)
                ->with([
                    'classSubject.subject',
                    'classSubject.teacher',
                    'submissions' => fn ($q) => $q->where('student_id', $student->student_id),
                ])
                ->when($term, fn ($q) => $q->where('term_id', $term->term_id))
                ->when($request->filled('subject_id'), fn ($q) => $q->whereHas(
                    'classSubject',
                    fn ($cs) => $cs->where('subject_id', (int) $request->subject_id)
                ))
                ->orderBy('due_at')
                ->get();

            $subjects = Subject::whereIn(
                'subject_id',
                \App\Models\ClassSubject::where('class_id', $student->class_id)->pluck('subject_id')
            )->orderBy('subject_name')->get();
        }

        // Decorate each assignment with this student's own status.
        $assignments = $assignments->map(function (Assignment $assignment) {
            $submission = $assignment->submissions->first();
            $assignment->setAttribute('student_submission', $submission);
            $assignment->setAttribute('student_status', $assignment->statusForStudent($submission));

            return $assignment;
        });

        return view('student.assignments.index', [
            'student'     => $student,
            'assignments' => $assignments,
            'subjects'    => $subjects,
            'terms'       => $terms,
            'term'        => $term,
            'counts'      => [
                'pending'   => $assignments->where('student_status', 'Pending')->count(),
                'submitted' => $assignments->where('student_status', 'Submitted')->count(),
                'graded'    => $assignments->where('student_status', 'Graded')->count(),
                'overdue'   => $assignments->where('student_status', 'Overdue')->count(),
            ],
        ]);
    }

    public function show(int $assignment): View
    {
        $student    = $this->currentStudent();
        $model      = $this->visibleAssignment($student, $assignment);
        $submission = $model->submissions()->where('student_id', $student->student_id)->first();

        return view('student.assignments.show', [
            'student'    => $student,
            'assignment' => $model,
            'submission' => $submission,
            'status'     => $model->statusForStudent($submission),
        ]);
    }

    public function submit(Request $request, int $assignment): RedirectResponse
    {
        $student = $this->currentStudent();
        $model   = $this->visibleAssignment($student, $assignment);

        $existing = $model->submissions()->where('student_id', $student->student_id)->first();

        // Once a teacher has marked the work, the student can no longer change it.
        if ($existing?->graded_at) {
            return back()->withErrors(['submission' => 'This assignment has already been graded and can no longer be changed.']);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
            'file'  => [
                $model->allows_file_upload ? 'nullable' : 'prohibited',
                'file',
                'max:10240', // 10 MB
                'mimes:pdf,doc,docx,txt,zip,png,jpg,jpeg,py,ipynb,csv,xlsx,pptx',
            ],
        ], [
            'file.prohibited' => 'This assignment does not accept file uploads.',
        ]);

        if (blank($validated['notes'] ?? null) && ! $request->hasFile('file')) {
            return back()->withErrors(['submission' => 'Add a note or attach a file before submitting.'])->withInput();
        }

        $submission = $existing ?? new AssignmentSubmission([
            'assignment_id' => $model->assignment_id,
            'student_id'    => $student->student_id,
        ]);

        if ($request->hasFile('file')) {
            // Replace rather than accumulate: one submission, one file.
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }

            $submission->file_path = $request->file('file')->store(
                "assignments/{$model->assignment_id}",
                'public'
            );
            $submission->original_filename = $request->file('file')->getClientOriginalName();
        }

        $submission->notes = $validated['notes'] ?? $submission->notes;
        $submission->submitted_at = now();
        $submission->save();

        $message = $model->isPastDue()
            ? 'Submitted after the deadline — your teacher will see it as late.'
            : 'Assignment submitted.';

        return redirect()
            ->route('student.assignments.show', $model->assignment_id)
            ->with('notification', $message);
    }
}
