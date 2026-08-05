<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ParentProfile;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    /**
     * Parent Portal Dashboard - Show their child's progress
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Get the parent profile and associated students
        $parentProfile = ParentProfile::where('user_id', $user->id)->first();
        
        if (!$parentProfile) {
            return redirect('/')->with('notification', 'Parent profile not found.');
        }

        // Get all students linked to this parent
        $students = Student::where('parent_user_id', $user->id)
            ->with(['schoolClass', 'grades.classSubject.subject', 'attendance', 'fees'])
            ->get();

        // For now, display the first student (parents can have multiple children)
        $student = $students->first();

        if (!$student) {
            return view('parent.dashboard', compact('parentProfile', 'students', 'student'))
                ->with('notification', 'No students linked to your account.');
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

        return view('parent.dashboard', compact(
            'parentProfile',
            'students',
            'student',
            'results',
            'attendanceRate',
            'feeBalance',
            'totalFees'
        ));
    }
}
