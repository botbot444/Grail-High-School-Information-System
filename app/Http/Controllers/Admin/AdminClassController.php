<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminClassController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::with(['teacher.user', 'subjects', 'gradeLevel'])
            ->withCount('students')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%' . $request->search . '%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('class_name', 'like', $term)
                        ->orWhereHas('teacher', function ($t) use ($term) {
                            $t->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term);
                        });
                });
            })
            ->when($request->filled('grade_level_id'), fn ($query) => $query->where('grade_level_id', $request->grade_level_id))
            ->when($request->status === 'active', fn ($query) => $query->whereNotNull('teacher_id'))
            ->when($request->status === 'needs-teacher', fn ($query) => $query->whereNull('teacher_id'))
            ->orderBy('grade_level_id')
            ->orderBy('class_name')
            ->paginate(20)
            ->withQueryString();

        $totalClasses = SchoolClass::count();
        $totalStudents = SchoolClass::withCount('students')->get()->sum('students_count');
        $needsTeacherCount = SchoolClass::whereNull('teacher_id')->count();
        $homeroomCoverage = $totalClasses > 0
            ? round((($totalClasses - $needsTeacherCount) / $totalClasses) * 100, 1)
            : 0;

        $gradeLevels = GradeLevel::orderBy('order')->get();

        return view('admin.classes.index', compact(
            'classes', 'totalClasses', 'totalStudents', 'needsTeacherCount', 'homeroomCoverage', 'gradeLevels'
        ));
    }

    public function create()
    {
        $teachers = Teacher::with('user')->orderBy('first_name')->orderBy('last_name')->get();
        $subjects = Subject::orderBy('subject_name')->get();
        $gradeLevels = GradeLevel::orderBy('order')->get();

        return view('admin.classes.create', compact('teachers', 'subjects', 'gradeLevels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Soft-deleted classes don't hold their name hostage — see the
            // whereNull('deleted_at') below and destroy()'s dependency guard,
            // which is what should make a class's name reusable again.
            'class_name' => ['required', 'string', 'max:255',
                Rule::unique('school_classes', 'class_name')->whereNull('deleted_at')],
            'grade_level_id' => 'required|exists:grade_levels,grade_level_id',
            'teacher_id' => 'nullable|exists:teachers,teacher_id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,subject_id',
        ]);

        $gradeLevel = GradeLevel::findOrFail($validated['grade_level_id']);

        $class = SchoolClass::create([
            'class_name' => $validated['class_name'],
            'grade_level' => $gradeLevel->name,
            'grade_level_id' => $gradeLevel->grade_level_id,
            'teacher_id' => $validated['teacher_id'] ?? null,
        ]);

        if (!empty($validated['subject_ids'])) {
            $class->subjects()->sync($validated['subject_ids']);
        }

        return redirect()->route('admin.classes.index')->with('notification', 'Class created successfully.');
    }

    public function show(SchoolClass $class)
    {
        $class->load([
            'teacher.user',
            'subjects',
            'gradeLevel',
            // Who actually stands in front of each subject in this class.
            'classSubjects.subject',
            'classSubjects.teacher',
        ])->loadCount('students');

        return view('admin.classes.show', ['class' => $class]);
    }

    public function edit(SchoolClass $class)
    {
        $teachers = Teacher::with('user')->orderBy('first_name')->orderBy('last_name')->get();
        $subjects = Subject::orderBy('subject_name')->get();
        $gradeLevels = GradeLevel::orderBy('order')->get();
        // Specify the table to avoid ambiguity with the pivot table's subject_id
        $assignedSubjects = $class->subjects()->pluck('subjects.subject_id')->toArray();
        $class->load('teacher.user')->loadCount('students');

        return view('admin.classes.edit', compact('class', 'teachers', 'subjects', 'gradeLevels', 'assignedSubjects'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:255',
                Rule::unique('school_classes', 'class_name')->ignore($class->class_id, 'class_id')->whereNull('deleted_at')],
            'grade_level_id' => 'required|exists:grade_levels,grade_level_id',
            'teacher_id' => 'nullable|exists:teachers,teacher_id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,subject_id',
        ]);

        $gradeLevel = GradeLevel::findOrFail($validated['grade_level_id']);

        $class->update([
            'class_name' => $validated['class_name'],
            'grade_level' => $gradeLevel->name,
            'grade_level_id' => $gradeLevel->grade_level_id,
            'teacher_id' => $validated['teacher_id'] ?? null,
        ]);

        if (!empty($validated['subject_ids'])) {
            $class->subjects()->sync($validated['subject_ids']);
        } else {
            $class->subjects()->detach();
        }

        return redirect()->route('admin.classes.show', $class)->with('notification', 'Class updated successfully.');
    }

    public function destroy(SchoolClass $class)
    {
        // Soft-deleting a class with students still enrolled doesn't remove
        // their class_id — it just makes the class invisible everywhere that
        // looks it up, silently breaking every screen that shows their class.
        // That's what happened to 9A: the class disappeared from the roster
        // but the students, timetable and subject assignments stayed pointed
        // at it. Same shape of guard as Term/AcademicYear destroy().
        if ($class->students()->exists()) {
            return back()->withErrors('Cannot delete a class with students still enrolled. Move them to another class first.');
        }

        // Same problem, different dependency: a class with no students can
        // still have teacher-subject assignments (class_subjects rows)
        // pointing at it. Soft-deleting it then makes the class invisible
        // everywhere teachers look it up, crashing their marks/attendance/
        // performance pages on a null ->schoolClass reference.
        if ($class->classSubjects()->exists()) {
            return back()->withErrors('Cannot delete a class with subject/teacher assignments still attached. Remove those assignments first.');
        }

        $class->delete();
        return redirect()->route('admin.classes.index')->with('notification', 'Class deleted.');
    }
}
