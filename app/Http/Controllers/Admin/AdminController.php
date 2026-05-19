<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin Dashboard - Display key metrics and student roster
     */
    public function dashboard()
    {
        $totalStudents = Student::count();
        $totalStaff = Teacher::count();
        
        // Calculate total fees collected
        $feesCollected = Fee::where('status', 'paid')
            ->sum('amount_paid');

        // Get students with their class and fee status
        $students = Student::with('schoolClass')
            ->get()
            ->map(function ($student) {
                $totalFees = $student->fees()->sum('amount_due');
                $paidFees = $student->fees()->where('status', 'paid')->sum('amount_paid');
                $balance = $totalFees - $paidFees;

                return [
                    'id' => $student->student_id,
                    'name' => $student->full_name,
                    'class' => $student->schoolClass?->class_name ?? 'N/A',
                    'balance' => $balance,
                    'fee_status' => $balance > 0 ? 'pending' : 'cleared',
                ];
            });

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalStaff',
            'feesCollected',
            'students'
        ));
    }

    /**
     * Display a listing of students (Resource: index)
     */
    public function index()
    {
        $students = Student::with('schoolClass', 'user')
            ->paginate(20);

        return view('admin.students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student (Resource: create)
     */
    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        return view('admin.students.create', compact('classes'));
    }

    /**
     * Store a newly created student (Resource: store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'student_number' => 'required|unique:students|string|max:50',
            'class_id' => 'required|exists:school_classes,class_id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'enrolment_date' => 'nullable|date',
        ]);

        try {
            Student::create($validated);
            return redirect()->route('admin.students.index')
                ->with('notification', 'Student created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create student: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific student (Resource: show)
     */
    public function show(Student $student)
    {
        $student->load('schoolClass', 'grades', 'attendance', 'fees', 'user');
        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing a student (Resource: edit)
     */
    public function edit(Student $student)
    {
        $classes = \App\Models\SchoolClass::all();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    /**
     * Update a student (Resource: update)
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'student_number' => 'required|string|max:50|unique:students,student_number,' . $student->student_id . ',student_id',
            'class_id' => 'required|exists:school_classes,class_id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'enrolment_date' => 'nullable|date',
        ]);

        try {
            $student->update($validated);
            return redirect()->route('admin.students.show', $student)
                ->with('notification', 'Student updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update student: ' . $e->getMessage());
        }
    }

    /**
     * Delete a student (Resource: destroy)
     */
    public function destroy(Student $student)
    {
        try {
            $student->delete();
            return redirect()->route('admin.students.index')
                ->with('notification', 'Student deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete student: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of teachers and staff.
     */
    public function teachers()
    {
        $teachers = Teacher::with(['user', 'classSubjects.subject', 'classSubjects.schoolClass'])
            ->paginate(20);

        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Display a listing of classes and subjects.
     */
    public function classes()
    {
        $classes = SchoolClass::with(['teacher', 'subjects'])
            ->paginate(20);

        return view('admin.classes.index', compact('classes'));
    }
}
