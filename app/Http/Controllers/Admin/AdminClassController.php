<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class AdminClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with(['teacher', 'subjects'])->paginate(20);
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $teachers = Teacher::with('user')->get();
        $subjects = Subject::all();
        return view('admin.classes.create', compact('teachers', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'teacher_id' => 'nullable|exists:teachers,teacher_id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,subject_id',
        ]);

        $class = SchoolClass::create([
            'class_name' => $validated['class_name'],
            'grade_level' => $validated['grade_level'],
            'teacher_id' => $validated['teacher_id'],
        ]);

        // Sync subjects if provided
        if (!empty($validated['subject_ids'])) {
            $class->subjects()->sync($validated['subject_ids']);
        }

        return redirect()->route('admin.classes.index')->with('notification', 'Class created');
    }

    public function show(SchoolClass $class)
    {
        $class->load('teacher', 'subjects');
        return view('admin.classes.show', ['class' => $class]);
    }

    public function edit(SchoolClass $class)
    {
        $teachers = Teacher::with('user')->get();
        $subjects = Subject::all();
        $assignedSubjects = $class->subjects()->pluck('subject_id')->toArray();
        return view('admin.classes.edit', compact('class', 'teachers', 'subjects', 'assignedSubjects'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'teacher_id' => 'nullable|exists:teachers,teacher_id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,subject_id',
        ]);

        $class->update([
            'class_name' => $validated['class_name'],
            'grade_level' => $validated['grade_level'],
            'teacher_id' => $validated['teacher_id'],
        ]);

        // Sync subjects if provided
        if (!empty($validated['subject_ids'])) {
            $class->subjects()->sync($validated['subject_ids']);
        } else {
            // If no subjects provided, detach all subjects
            $class->subjects()->detach();
        }

        return redirect()->route('admin.classes.show', $class)->with('notification', 'Class updated');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('admin.classes.index')->with('notification', 'Class deleted');
    }
}
