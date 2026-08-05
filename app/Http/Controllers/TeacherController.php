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
        $teacher = $user->teacher;

        if (!$teacher) {
            return view('teacher.marks', ['assignments' => collect()])
                ->with('notification', 'Teacher profile not found.');
        }
        
        // Get teacher's assignments with their classes and subjects
        $assignments = ClassSubject::with(['schoolClass', 'subject', 'teacher'])
            ->where('teacher_id', $teacher->teacher_id)
            ->get();

        if ($assignments->isEmpty()) {
            return view('teacher.marks', compact('assignments'))
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
                // Get current grade/mark for this student in this subject (Term 1, Exam)
                $grade = Grade::where('student_id', $student->student_id)
                    ->where('class_subject_id', $assignment->class_subject_id)
                    ->where('assessment_type', 'EXAM')
                    ->where('term', 'Term 1')
                    ->where('academic_year', now()->year)
                    ->first();

                // Get current attendance for this student
                $attendance = Attendance::where('student_id', $student->student_id)
                    ->where('class_subject_id', $assignment->class_subject_id)
                    ->where('date', now()->toDateString())
                    ->first();

                $statusMap = [
                    'Present' => 'P',
                    'Absent'  => 'A',
                    'Late'    => 'L',
                ];

                return [
                    'id' => $student->student_id,
                    'name' => $student->full_name,
                    'mark' => $grade?->score ?? 0,
                    'attendance' => $statusMap[$attendance?->status] ?? 'P',
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
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->withErrors('Teacher profile not found.');
        }

        $assignmentId = $request->input('assignment_id');

        $assignment = ClassSubject::where('class_subject_id', $assignmentId)
            ->where('teacher_id', $teacher->teacher_id)
            ->first();

        if (!$assignment) {
            return back()->withErrors('Unauthorized assignment.');
        }

        $marks = $request->input('marks', []);
        $attendance = $request->input('attendance', []);

        $statusMap = [
            'P' => 'Present',
            'A' => 'Absent',
            'L' => 'Late',
        ];

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
                        'assessment_type' => 'EXAM',
                        'term' => 'Term 1',
                        'academic_year' => now()->year,
                    ],
                    [
                        'score' => $mark,
                        'max_score' => 100.00,
                        'recorded_by' => $teacher->teacher_id,
                    ]
                );
            }

            foreach ($attendance as $studentId => $status) {
                // Validate attendance status
                if (!isset($statusMap[$status])) {
                    continue;
                }
                $dbStatus = $statusMap[$status];

                // Create or update attendance record
                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_subject_id' => $assignmentId,
                        'date' => now()->toDateString(),
                    ],
                    [
                        'status' => $dbStatus,
                        'recorded_by' => $teacher->teacher_id,
                    ]
                );
            }

            return back()->with('notification', 'Records saved successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to save records: ' . $e->getMessage());
        }
    }
}