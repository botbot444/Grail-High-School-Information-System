<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
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
        $totalStaff    = Teacher::count();
        $totalParents  = ParentProfile::count();
        $totalClasses  = SchoolClass::count();
        $totalSubjects = Subject::count();

        // Calculate total fees collected (only cleared/fully-paid fees)
        $feesCollected = Fee::cleared()->sum('amount_paid');

        // 5 most recently updated teachers for the dashboard panel
        $recentTeachers = Teacher::with(['user', 'classSubjects.subject'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        // Get students with their class and fee status
        $students = Student::with('schoolClass')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($student) {
                $totalFees = $student->fees()->sum('amount_due');
                $paidFees  = $student->fees()->cleared()->sum('amount_paid');
                $balance   = $totalFees - $paidFees;

                return [
                    'id'         => $student->student_id,
                    'name'       => $student->full_name,
                    'class'      => $student->schoolClass?->class_name ?? 'N/A',
                    'balance'    => $balance,
                    'fee_status' => $balance > 0 ? 'pending' : 'cleared',
                ];
            });

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalStaff',
            'totalParents',
            'totalClasses',
            'totalSubjects',
            'feesCollected',
            'recentTeachers',
            'students'
        ));
    }

    /**
     * Display a listing of students (Resource: index)
     */
    public function index(Request $request)
    {
        $students = Student::with('schoolClass', 'user')
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id))
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student (Resource: create)
     */
    public function create()
    {
        $classes = SchoolClass::all();
        $parents = ParentProfile::with('user')->orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.students.create', compact('classes', 'parents'));
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
            'parent_user_id' => 'nullable|exists:users,id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'enrolment_date' => 'nullable|date',
        ]);

        if (! empty($validated['parent_user_id'])) {
            $validated['parent_user_id'] = (int) $validated['parent_user_id'];
        }

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
        $student->load(
            'schoolClass',
            'grades.classSubject.subject',
            'grades.recordedByTeacher',
            'attendance.classSubject.subject',
            'attendance.recordedByTeacher',
            'fees',
            'user'
        );

        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing a student (Resource: edit)
     */
    public function edit(Student $student)
    {
        // Relations used by the view: class (with homeroom teacher) for the
        // class dropdown, linked accounts for the read-only info panel, and
        // attendance records to compute the attendance-rate stat.
        $student->load('schoolClass.teacher', 'user', 'parentUser');

        $classes = SchoolClass::with('teacher')->get();

        // Attendance rate = (Present + Late) / total recorded sessions.
        // Late still counts as attended. Returns null when there are no
        // records yet so the view can show an em-dash instead of "0%".
        $total = $student->attendance()->count();
        $attendanceRate = null;

        if ($total > 0) {
            $attended = $student->attendance()->whereIn('status', ['Present', 'Late'])->count();
            $attendanceRate = round(($attended / $total) * 100, 1);
        }

        return view('admin.students.edit', compact('student', 'classes', 'attendanceRate'));
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

    /**
     * Display the administration settings page.
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Display the examinations page.
     */
    public function examinations()
    {
        return view('admin.examinations');
    }
}
