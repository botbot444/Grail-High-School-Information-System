<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\GeneratesTemporaryPassword;
use App\Models\ClassSubject;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminTeacherController extends Controller
{
    use GeneratesTemporaryPassword;

    /** Timetable rows read in the order a week actually runs. */
    private const DAY_ORDER = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function index()
    {
        $teachers = Teacher::with(['user', 'subjects', 'homeroomClasses', 'classSubjects.subject', 'classSubjects.schoolClass'])
            ->paginate(20);

        $teacherCount = Teacher::count();
        $activeTeachers = Teacher::where(function ($query) {
            $query->whereHas('subjects')
                ->orWhereHas('homeroomClasses')
                ->orWhereHas('classSubjects');
        })->count();
        $pendingSetup = Teacher::whereDoesntHave('subjects')
            ->whereDoesntHave('homeroomClasses')
            ->whereDoesntHave('classSubjects')
            ->count();
        $assignedClasses = Teacher::where(function ($query) {
            $query->whereHas('homeroomClasses')->orWhereHas('classSubjects');
        })->count();

        return view('admin.teachers.index', compact('teachers', 'teacherCount', 'activeTeachers', 'pendingSetup', 'assignedClasses'));
    }

    public function create()
    {
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        return view('admin.teachers.create', compact('classes', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // A soft-deleted teacher's email is free to reuse — see destroy().
            'email' => ['required', 'email', 'unique:users,email',
                Rule::unique('teachers', 'email')->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:20',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,class_id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,subject_id',
        ]);

        $roleId = Role::where('name', 'teacher')->value('id');

        if (!$roleId) {
            return back()->withErrors('Teacher role is not defined.');
        }

        // Generated once here so it can be shown to the admin after the
        // transaction commits — it is never stored in readable form.
        $temporary = $this->temporaryPassword();

        try {
            $result = DB::transaction(function () use ($validated, $roleId, $temporary) {
                $user = User::create([
                    'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                    'email' => $validated['email'],
                    'password' => Hash::make($temporary),
                    'role' => 'teacher',
                    'role_id' => $roleId,
                    'email_verified_at' => now(),
                    // Forces the teacher onto their settings page at first
                    // login until they choose their own password.
                    'must_change_password' => true,
                ]);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ]);

                // Homeroom: which classes this teacher is form teacher for.
                if (!empty($validated['class_ids'])) {
                    SchoolClass::whereIn('class_id', $validated['class_ids'])
                        ->update(['teacher_id' => $teacher->teacher_id]);
                }

                // Subjects this teacher is qualified/assigned to teach, independent
                // of any specific class — this is what the "Teaching Subjects"
                // checkboxes on the form actually promise ("assigned to the teacher
                // independently of homeroom classes"), and what the teacher table
                // and profile read to show an "Assigned Subjects" chip. It is NOT
                // the same as a real class+subject teaching assignment below, which
                // is what actually drives the teacher's portal.
                $teacher->subjects()->sync($validated['subject_ids'] ?? []);

                // Teaching: the rows the teacher portal actually reads.
                $assignment = $this->syncTeachingAssignments(
                    $teacher,
                    $validated['class_ids'] ?? [],
                    $validated['subject_ids'] ?? []
                );

                return ['teacher' => $teacher, 'assignment' => $assignment];
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors('Failed to create teacher: ' . $e->getMessage());
        }

        $teacher = $result['teacher'];

        return redirect()->route('admin.teachers.show', $teacher)
            ->with('notification', "{$teacher->full_name} was added. Their one-time password is:")
            ->with('temporary_password', $temporary)
            ->with('temporary_password_for', $teacher->user_id)
            ->with('assignment_summary', $this->assignmentSummary($result['assignment']));
    }

    public function show(Teacher $teacher)
    {
        $teacher->load([
            'user',
            'homeroomClasses',
            'subjects',
            // Dependent-record counts come back with the rows, so the page can
            // say WHY an assignment can't be removed instead of failing on the
            // delete. Counted through the relations rather than a hand-written
            // join, so the table names stay the models' problem, not mine.
            'classSubjects' => fn ($query) => $query
                ->with(['subject', 'schoolClass'])
                ->withCount(['grades', 'attendanceRecords', 'assignments']),
        ]);

        $term = Term::current();
        $timetable = $term
            ? TimetableSlot::with(['subject', 'period', 'schoolClass'])
                ->where('teacher_id', $teacher->teacher_id)
                ->where('term_id', $term->term_id)
                ->get()
                ->sortBy(fn (TimetableSlot $slot) => [
                    array_search($slot->day_of_week, self::DAY_ORDER, true),
                    $slot->period?->start_time ?? '',
                ])
                ->values()
            : collect();

        // Everything that could still be assigned, plus who holds each
        // class/subject pair today (the pair is unique across the school).
        $classes = SchoolClass::orderBy('class_name')->get();
        $subjects = Subject::orderBy('subject_name')->get();
        $takenPairs = ClassSubject::with('teacher')
            ->get()
            ->keyBy(fn (ClassSubject $cs) => $cs->class_id . '-' . $cs->subject_id);

        return view('admin.teachers.show', compact(
            'teacher',
            'term',
            'timetable',
            'classes',
            'subjects',
            'takenPairs'
        ));
    }

    public function edit(Teacher $teacher)
    {
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $assignedClasses = SchoolClass::where('teacher_id', $teacher->teacher_id)->pluck('class_id')->toArray();
        // The "Assigned Subjects" checkboxes reflect the teacher_subjects
        // qualification list (what store()/update() now save them as), not the
        // class-paired teaching assignments managed separately on the show page.
        $assignedSubjects = $teacher->subjects()->pluck('subjects.subject_id')->toArray();

        return view('admin.teachers.edit', compact('teacher', 'classes', 'subjects', 'assignedClasses', 'assignedSubjects'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email,' . $teacher->user_id,
                Rule::unique('teachers', 'email')->ignore($teacher->teacher_id, 'teacher_id')->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:20',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,class_id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,subject_id',
        ]);

        try {
            $assignment = DB::transaction(function () use ($validated, $teacher) {
                $teacher->user?->update([
                    'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                    'email' => $validated['email'],
                ]);

                $teacher->update([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ]);

                // Homeroom: release classes no longer selected, claim the rest.
                $newClassIds = $validated['class_ids'] ?? [];
                SchoolClass::where('teacher_id', $teacher->teacher_id)
                    ->when(!empty($newClassIds), fn ($query) => $query->whereNotIn('class_id', $newClassIds))
                    ->update(['teacher_id' => null]);

                if (!empty($newClassIds)) {
                    SchoolClass::whereIn('class_id', $newClassIds)
                        ->update(['teacher_id' => $teacher->teacher_id]);
                }

                // Subject qualifications (teacher_subjects) are free to add and
                // remove here — unlike class_subjects rows, they carry no grades
                // or attendance that could be orphaned by unchecking a box.
                $teacher->subjects()->sync($validated['subject_ids'] ?? []);

                // Teaching assignments are only ever ADDED here. Removing one
                // can orphan grades and attendance, so that is a deliberate
                // action on the teacher's page, where the consequences show.
                return $this->syncTeachingAssignments($teacher, $newClassIds, $validated['subject_ids'] ?? []);
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors('Failed to update teacher: ' . $e->getMessage());
        }

        return redirect()->route('admin.teachers.show', $teacher)
            ->with('notification', 'Teacher updated.')
            ->with('assignment_summary', $this->assignmentSummary($assignment));
    }

    /**
     * Assign one subject, in one class, to this teacher.
     *
     * class_subjects is unique on (class_id, subject_id) — a subject is
     * taught once per class — so assigning a pair another teacher holds is
     * a hand-over, not a duplicate. It is allowed, but only when the admin
     * has confirmed it, because grades already recorded against that pair
     * follow it to the new teacher.
     */
    public function assignSubject(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,class_id',
            'subject_id' => 'required|exists:subjects,subject_id',
            'confirm_handover' => 'nullable|boolean',
        ]);

        $existing = ClassSubject::with('teacher')
            ->where('class_id', $validated['class_id'])
            ->where('subject_id', $validated['subject_id'])
            ->first();

        // A class can offer a subject before anyone teaches it (Admin\ClassController
        // lets a class declare its subjects with a teacher "decided later") — that's
        // not a holder to hand over from, so it doesn't need confirm_handover.
        $heldByAnotherTeacher = $existing && $existing->teacher_id !== null;

        if ($heldByAnotherTeacher && (int) $existing->teacher_id === (int) $teacher->teacher_id) {
            return back()->with('notification', 'That subject is already assigned to this teacher for that class.');
        }

        if ($heldByAnotherTeacher && !$request->boolean('confirm_handover')) {
            $holder = $existing->teacher?->full_name ?? 'another teacher';

            return back()->withErrors([
                'assignment' => "{$existing->subject?->subject_name} in {$existing->schoolClass?->display_name} is currently taught by {$holder}. Tick \"hand over\" to move it, along with any marks already recorded against it.",
            ]);
        }

        try {
            if ($existing) {
                $existing->update(['teacher_id' => $teacher->teacher_id]);
                $message = $heldByAnotherTeacher ? 'Assignment handed over to ' . $teacher->full_name . '.' : 'Subject assigned.';
            } else {
                ClassSubject::create([
                    'class_id' => $validated['class_id'],
                    'subject_id' => $validated['subject_id'],
                    'teacher_id' => $teacher->teacher_id,
                ]);
                $message = 'Subject assigned.';
            }
        } catch (\Exception $e) {
            return back()->withErrors(['assignment' => 'Could not assign that subject: ' . $e->getMessage()]);
        }

        return back()->with('notification', $message);
    }

    /**
     * Remove one class/subject assignment from this teacher.
     *
     * Refused when marks or attendance already reference it: the grade rows
     * point at class_subject_id, so deleting the row would either fail on the
     * foreign key or strand the marks, depending on the driver.
     */
    public function unassignSubject(Teacher $teacher, ClassSubject $classSubject)
    {
        if ((int) $classSubject->teacher_id !== (int) $teacher->teacher_id) {
            return back()->withErrors(['assignment' => 'That assignment does not belong to this teacher.']);
        }

        $grades = $classSubject->grades()->count();
        $attendance = $classSubject->attendanceRecords()->count();
        $assignments = $classSubject->assignments()->count();

        if ($grades || $attendance || $assignments) {
            $parts = [];
            if ($grades) {
                $parts[] = "{$grades} mark" . ($grades === 1 ? '' : 's');
            }
            if ($attendance) {
                $parts[] = "{$attendance} attendance record" . ($attendance === 1 ? '' : 's');
            }
            if ($assignments) {
                $parts[] = "{$assignments} assignment" . ($assignments === 1 ? '' : 's');
            }

            return back()->withErrors([
                'assignment' => 'Cannot remove this assignment — it already has ' . implode(', ', $parts) . ' recorded against it. Hand it to another teacher instead.',
            ]);
        }

        try {
            $classSubject->delete();
        } catch (\Exception $e) {
            return back()->withErrors(['assignment' => 'Could not remove that assignment: ' . $e->getMessage()]);
        }

        return back()->with('notification', 'Assignment removed.');
    }

    public function destroy(Teacher $teacher)
    {
        // Soft-deleting a teacher who's still a homeroom teacher or still
        // teaching a class-subject leaves those pointed at a now-invisible
        // record instead of removing the assignment — same shape of bug as
        // deleting a class with students still enrolled in it.
        if ($teacher->homeroomClasses()->exists()) {
            return back()->withErrors('Cannot delete a teacher who is still a homeroom teacher. Reassign their classes first.');
        }
        if ($teacher->classSubjects()->exists()) {
            return back()->withErrors('Cannot delete a teacher with active class-subject assignments. Reassign or remove them first.');
        }

        try {
            $teacher->delete();
            $teacher->user?->delete();

            return redirect()->route('admin.teachers.index')
                ->with('notification', 'Teacher deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete teacher: ' . $e->getMessage());
        }
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /**
     * Turn the form's class and subject tick-boxes into real teaching rows.
     *
     * The teacher portal — marks, attendance, assignments, report cards,
     * class performance — resolves everything through class_subjects, so a
     * teacher with none of these rows logs in to an empty portal no matter
     * what the admin ticked. The form gives a flat list of classes and a
     * flat list of subjects, so the pairing is every selected subject in
     * every selected class; anything more specific is set on the teacher's
     * own page.
     *
     * Never steals a pair another teacher already holds — those are returned
     * as skipped so the admin can hand them over deliberately.
     *
     * @return array{created: int, skipped: array<int, string>}
     */
    private function syncTeachingAssignments(Teacher $teacher, array $classIds, array $subjectIds): array
    {
        if (empty($classIds) || empty($subjectIds)) {
            return ['created' => 0, 'skipped' => []];
        }

        $created = 0;
        $skipped = [];

        $classes = SchoolClass::whereIn('class_id', $classIds)->get()->keyBy('class_id');
        $subjects = Subject::whereIn('subject_id', $subjectIds)->get()->keyBy('subject_id');

        foreach ($classIds as $classId) {
            foreach ($subjectIds as $subjectId) {
                $existing = ClassSubject::with('teacher')
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->first();

                if ($existing) {
                    if ($existing->teacher_id === null) {
                        // Offered by the class but nobody teaches it yet — claim it,
                        // rather than treating an empty slot as "already spoken for".
                        $existing->update(['teacher_id' => $teacher->teacher_id]);
                        $created++;
                    } elseif ((int) $existing->teacher_id !== (int) $teacher->teacher_id) {
                        $holder = $existing->teacher?->full_name ?? 'another teacher';
                        $label = ($subjects[$subjectId]->subject_name ?? 'Subject')
                            . ' in ' . ($classes[$classId]->display_name ?? 'that class');
                        $skipped[] = "{$label} (taught by {$holder})";
                    }

                    continue;
                }

                ClassSubject::create([
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacher->teacher_id,
                ]);
                $created++;
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @param array{created: int, skipped: array<int, string>} $assignment
     */
    private function assignmentSummary(array $assignment): ?string
    {
        $lines = [];

        if ($assignment['created'] > 0) {
            $lines[] = $assignment['created'] . ' teaching assignment'
                . ($assignment['created'] === 1 ? '' : 's') . ' created.';
        }

        if (!empty($assignment['skipped'])) {
            $lines[] = 'Left alone, already taught by someone else: '
                . implode('; ', $assignment['skipped']) . '.';
        }

        return empty($lines) ? null : implode(' ', $lines);
    }
}
