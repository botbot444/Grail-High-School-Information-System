<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Role;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminTeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with(['user', 'classSubjects.subject', 'classSubjects.schoolClass'])
            ->paginate(20);

        $teacherCount = Teacher::count();
        $activeTeachers = Teacher::whereHas('classSubjects')->count();
        $pendingSetup = Teacher::whereDoesntHave('classSubjects')->count();
        $assignedClasses = Teacher::whereHas('classSubjects')->count();

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
            'email' => 'required|email|unique:users,email|unique:teachers,email',
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

        try {
            $user = User::create([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
                'password' => Hash::make('Teacher@1234'),
                'role_id' => $roleId,
                'email_verified_at' => now(),
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            // Assign homeroom classes if provided
            if (!empty($validated['class_ids'])) {
                SchoolClass::whereIn('class_id', $validated['class_ids'])
                    ->update(['teacher_id' => $teacher->teacher_id]);
            }

            // Assign subject teaching duties for selected subjects and classes
            $subjectIds = $validated['subject_ids'] ?? [];
            if (!empty($validated['class_ids']) && !empty($subjectIds)) {
                foreach ($validated['class_ids'] as $classId) {
                    foreach ($subjectIds as $subjectId) {
                        ClassSubject::updateOrCreate(
                            ['class_id' => $classId, 'subject_id' => $subjectId],
                            ['teacher_id' => $teacher->teacher_id]
                        );
                    }
                }
            }

            return redirect()->route('admin.teachers.index')
                ->with('notification', 'Teacher created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create teacher: ' . $e->getMessage());
        }
    }

    public function show(Teacher $teacher)
    {
        $teacher->load('user', 'classSubjects.subject', 'classSubjects.schoolClass');

        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $assignedClasses = SchoolClass::where('teacher_id', $teacher->teacher_id)->pluck('class_id')->toArray();
        $assignedSubjects = $teacher->classSubjects->pluck('subject_id')->unique()->toArray();

        return view('admin.teachers.edit', compact('teacher', 'classes', 'subjects', 'assignedClasses', 'assignedSubjects'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->user_id . '|unique:teachers,email,' . $teacher->teacher_id . ',teacher_id',
            'phone' => 'nullable|string|max:20',
            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:school_classes,class_id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,subject_id',
        ]);

        try {
            $teacher->user->update([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
            ]);

            $teacher->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            // Update homeroom assignments: unset previous homeroom classes not in new list
            $newClassIds = $validated['class_ids'] ?? [];
            SchoolClass::where('teacher_id', $teacher->teacher_id)
                ->when(!empty($newClassIds), fn($query) => $query->whereNotIn('class_id', $newClassIds))
                ->when(empty($newClassIds), fn($query) => $query)
                ->update(['teacher_id' => null]);
            if (!empty($newClassIds)) {
                SchoolClass::whereIn('class_id', $newClassIds)
                    ->update(['teacher_id' => $teacher->teacher_id]);
            }

            // Sync subject teaching assignments for the selected classes
            $selectedSubjects = $validated['subject_ids'] ?? [];
            $selectedPairs = [];
            if (!empty($newClassIds) && !empty($selectedSubjects)) {
                foreach ($newClassIds as $classId) {
                    foreach ($selectedSubjects as $subjectId) {
                        $selectedPairs[] = "{$classId}:{$subjectId}";
                        ClassSubject::updateOrCreate(
                            ['class_id' => $classId, 'subject_id' => $subjectId],
                            ['teacher_id' => $teacher->teacher_id]
                        );
                    }
                }
            }

            // Remove any existing teacher assignments that are no longer selected
            foreach ($teacher->classSubjects as $assignment) {
                $pair = "{$assignment->class_id}:{$assignment->subject_id}";
                if (!in_array($pair, $selectedPairs, true)) {
                    $assignment->delete();
                }
            }

            return redirect()->route('admin.teachers.show', $teacher)
                ->with('notification', 'Teacher updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update teacher: ' . $e->getMessage());
        }
    }

    public function destroy(Teacher $teacher)
    {
        try {
            $teacher->delete();
            $teacher->user?->delete();

            return redirect()->route('admin.teachers.index')
                ->with('notification', 'Teacher deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete teacher: ' . $e->getMessage());
        }
    }
}
