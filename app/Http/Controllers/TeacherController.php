<?php

namespace App\Http\Controllers;

use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Show mark entry form for teacher
     */
    public function marks(Request $request)
    {
        $user = auth()->user();
        
        // Get teacher's assignments with their classes and subjects
        $assignments = ClassSubject::with(['schoolClass', 'subject', 'teacher'])
            ->where('teacher_id', $user->id)
            ->get();

        if ($assignments->isEmpty()) {
            return view('teacher.marks')
                ->with('notification', 'No assignments found for this teacher.');
        }

        // Get selected assignment or use first one
        $selectedAssignmentId = $request->query('assignment_id');
        $assignment = $selectedAssignmentId 
            ? $assignments->firstWhere('class_subject_id', $selectedAssignmentId)
            : $assignments->first();

        if (!$assignment) {
            $assignment = $assignments->first();
        }

        // Get students in the selected class
        $students = Student::where('class_id', $assignment->schoolClass->class_id)
            ->with('user')
            ->get()
            ->map(function ($student) use ($assignment) {
                // Get current grade/mark for this student in this subject
                $grade = Grade::where('student_id', $student->student_id)
                    ->where('class_subject_id', $assignment->class_subject_id)
                    ->latest()
                    ->first();

                // Get current attendance for this student
                $attendance = Attendance::where('student_id', $student->student_id)
                    ->where('class_subject_id', $assignment->class_subject_id)
                    ->latest()
                    ->first();

                return [
                    'id' => $student->student_id,
                    'name' => $student->full_name,
                    'mark' => $grade?->marks ?? 0,
                    'attendance' => $attendance?->status ?? 'P',
                ];
            });

        return view('teacher.marks', compact(
            'assignments',
            'assignment',
            'students'
        ));
    }

    /**
     * Store marks and attendance records
     */
    public function storeMarks(Request $request)
    {
        $user = auth()->user();
        $assignmentId = $request->input('assignment_id');

        $assignment = ClassSubject::where('class_subject_id', $assignmentId)
            ->where('teacher_id', $user->id)
            ->first();

        if (!$assignment) {
            return back()->withErrors('Unauthorized assignment.');
        }

        $marks = $request->input('marks', []);
        $attendance = $request->input('attendance', []);

        try {
            foreach ($marks as $studentId => $mark) {
                // Validate mark
                $mark = (int) $mark;
                if ($mark < 0 || $mark > 100) {
                    continue;
                }

                // Create or update grade record
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_subject_id' => $assignmentId,
                    ],
                    [
                        'marks' => $mark,
                        'recorded_by' => $user->id,
                    ]
                );
            }

            foreach ($attendance as $studentId => $status) {
                // Validate attendance status
                if (!in_array($status, ['P', 'A', 'L'])) {
                    continue;
                }

                // Create or update attendance record
                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_subject_id' => $assignmentId,
                        'date' => now()->toDateString(),
                    ],
                    [
                        'status' => $status,
                        'recorded_by' => $user->id,
                    ]
                );
            }

            return back()->with('notification', 'Records saved successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to save records: ' . $e->getMessage());
        }
    }
}