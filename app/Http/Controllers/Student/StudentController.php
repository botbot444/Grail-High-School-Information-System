<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Student Portal Dashboard - Show academic progress
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Get the student profile
        $student = \App\Models\Student::where('user_id', $user->id)
            ->with(['schoolClass', 'grades.classSubject.subject', 'attendance', 'fees'])
            ->first();

        if (!$student) {
            return redirect('/')->with('notification', 'Student profile not found.');
        }

        // Get student's latest grades
        $results = $student->grades()
            ->with('classSubject.subject')
            ->latest()
            ->take(10)
            ->get();

        // Calculate attendance rate
        $attendanceTotal = $student->attendance()->count();
        $attendancePresent = $student->attendance()->where('status', 'Present')->count();
        $attendanceRate = $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100) : 0;

        // Calculate fee balance
        $totalFees = $student->fees()->sum('amount_due');
        $paidFees = $student->fees()->cleared()->sum('amount_paid');
        $feeBalance = $totalFees - $paidFees;
        $feeStatus = $feeBalance > 0 ? 'Pending' : 'Cleared';

        return view('student.dashboard', compact(
            'student',
            'results',
            'attendanceRate',
            'feeBalance',
            'totalFees',
            'feeStatus'
        ));
    }
}
