<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassSubject;
use App\Models\Teacher;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Teacher-side assignment authoring and marking.
 *
 * Scoping rule throughout: a teacher may only touch assignments whose
 * class_subject they are the assigned teacher for.
 */
class AssignmentController extends Controller
{
    private function currentTeacher(): Teacher
    {
        return Teacher::where('user_id', auth()->id())->firstOrFail();
    }

    /** The class-subjects this teacher actually teaches — the only valid targets. */
    private function teachableClassSubjects(Teacher $teacher)
    {
        return ClassSubject::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->teacher_id)
            ->get();
    }

    private function ownedAssignment(Teacher $teacher, int $assignmentId): Assignment
    {
        return Assignment::forTeacher($teacher->teacher_id)
            ->with(['classSubject.schoolClass', 'classSubject.subject', 'term'])
            ->where('assignment_id', $assignmentId)
            ->firstOrFail();
    }

    public function index(Request $request): View
    {
        $teacher = $this->currentTeacher();

        $assignments = Assignment::forTeacher($teacher->teacher_id)
            ->with(['classSubject.schoolClass', 'classSubject.subject'])
            ->withCount([
                'submissions',
                'submissions as graded_count' => fn ($q) => $q->whereNotNull('graded_at'),
            ])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('due_at')
            ->get();

        return view('teacher.assignments.index', [
            'teacher'     => $teacher,
            'assignments' => $assignments,
        ]);
    }

    public function create(): View
    {
        $teacher = $this->currentTeacher();

        return view('teacher.assignments.create', [
            'teacher'        => $teacher,
            'classSubjects'  => $this->teachableClassSubjects($teacher),
            'terms'          => Term::with('academicYear')->orderByDesc('start_date')->get(),
            'assignment'     => new Assignment(['max_score' => 100, 'allows_file_upload' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $data    = $this->validated($request, $teacher);

        $assignment = new Assignment($data);
        $assignment->created_by = $teacher->teacher_id;
        $assignment->published_at = $data['status'] === Assignment::STATUS_PUBLISHED ? now() : null;
        $assignment->save();

        return redirect()
            ->route('teacher.assignments.index')
            ->with('notification', "Assignment \"{$assignment->title}\" saved.");
    }

    public function edit(int $assignment): View
    {
        $teacher = $this->currentTeacher();

        return view('teacher.assignments.edit', [
            'teacher'       => $teacher,
            'assignment'    => $this->ownedAssignment($teacher, $assignment),
            'classSubjects' => $this->teachableClassSubjects($teacher),
            'terms'         => Term::with('academicYear')->orderByDesc('start_date')->get(),
        ]);
    }

    public function update(Request $request, int $assignment): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $model   = $this->ownedAssignment($teacher, $assignment);
        $data    = $this->validated($request, $teacher);

        // Stamp published_at the first time it goes live, and clear it if pulled back to draft.
        if ($data['status'] === Assignment::STATUS_PUBLISHED && ! $model->published_at) {
            $data['published_at'] = now();
        } elseif ($data['status'] === Assignment::STATUS_DRAFT) {
            $data['published_at'] = null;
        }

        $model->fill($data)->save();

        return redirect()
            ->route('teacher.assignments.index')
            ->with('notification', "Assignment \"{$model->title}\" updated.");
    }

    public function destroy(int $assignment): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $model   = $this->ownedAssignment($teacher, $assignment);
        $title   = $model->title;

        $model->delete(); // soft delete — the audit trail keeps the record

        return redirect()
            ->route('teacher.assignments.index')
            ->with('notification', "Assignment \"{$title}\" removed.");
    }

    /** Marking screen: every submission for one assignment. */
    public function submissions(int $assignment): View
    {
        $teacher = $this->currentTeacher();
        $model   = $this->ownedAssignment($teacher, $assignment);

        $model->load(['submissions.student']);

        // Students in the class who have not submitted at all.
        $submittedIds = $model->submissions->pluck('student_id');
        $missing = \App\Models\Student::where('class_id', $model->classSubject->class_id)
            ->whereNotIn('student_id', $submittedIds)
            ->orderBy('last_name')
            ->get();

        return view('teacher.assignments.submissions', [
            'teacher'    => $teacher,
            'assignment' => $model,
            'missing'    => $missing,
        ]);
    }

    public function grade(Request $request, int $assignment, int $submission): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $model   = $this->ownedAssignment($teacher, $assignment);

        $record = AssignmentSubmission::where('assignment_id', $model->assignment_id)
            ->where('submission_id', $submission)
            ->firstOrFail();

        $validated = $request->validate([
            'score'    => ['required', 'numeric', 'min:0', 'max:' . $model->max_score],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ], [
            'score.max' => "Score cannot exceed this assignment's maximum of {$model->max_score}.",
        ]);

        $record->fill($validated);
        $record->graded_by = $teacher->teacher_id;
        $record->graded_at = now();
        $record->save();

        return back()->with('notification', 'Marked ' . ($record->student?->full_name ?? 'submission') . '.');
    }

    private function validated(Request $request, Teacher $teacher): array
    {
        return $request->validate([
            'class_subject_id' => [
                'required',
                // Rule::in over the teacher's own class-subjects is what stops a
                // teacher posting an assignment onto someone else's class.
                Rule::in($this->teachableClassSubjects($teacher)->pluck('class_subject_id')->all()),
            ],
            'term_id'            => ['nullable', 'exists:terms,term_id'],
            'title'              => ['required', 'string', 'max:255'],
            'instructions'       => ['nullable', 'string', 'max:10000'],
            'status'             => ['required', Rule::in([Assignment::STATUS_DRAFT, Assignment::STATUS_PUBLISHED])],
            'due_at'             => ['required', 'date'],
            'max_score'          => ['required', 'numeric', 'min:1', 'max:1000'],
            'allows_file_upload' => ['nullable', 'boolean'],
        ]);
    }
}
