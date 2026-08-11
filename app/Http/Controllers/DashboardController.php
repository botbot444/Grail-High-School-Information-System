<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Term;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('teacher')) {
            return redirect()->route('teacher.marks');
        }
        if ($user->hasRole('parent')) {
            return redirect()->route('parent.dashboard');
        }
        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        $teacher = $user->teacher;
        $assignments = $teacher
            ? ClassSubject::with(['schoolClass.students', 'subject'])
                ->where('teacher_id', $teacher->teacher_id)
                ->get()
            : collect();

        return view('dashboard', compact('assignments'));
    }

    public function adminDashboard()
    {
        $currentAcademicYear = Cache::remember('current_academic_year', 3600, function () {
            return AcademicYear::current()->first();
        });

        $currentTerm = Cache::remember('current_term', 3600, function () use ($currentAcademicYear) {
            if (!$currentAcademicYear) return null;
            return Term::where('academic_year_id', $currentAcademicYear->year_id)
                ->current()
                ->first();
        });

        $todayCollections = Cache::remember('today_collections_' . date('Ymd'), 300, function () {
            return Payment::whereDate('payment_date', today())->sum('amount');
        });

        $todayPayments = Cache::remember('today_payments_count_' . date('Ymd'), 300, function () {
            return Payment::whereDate('payment_date', today())->count();
        });

        $urgentFees = Cache::remember('urgent_fees_count', 300, function () {
            return Fee::where('due_date', '>', today())
                ->where('due_date', '<=', today()->addDays(7))
                ->whereIn('status', ['Pending', 'Partially Paid'])
                ->count();
        });

        // ── Dashboard statistics ────────────────────────────────────────────────
        $totalStudents = Student::count();
        $totalStaff    = Teacher::count();
        $totalParents  = ParentProfile::count();
        $totalClasses  = SchoolClass::count();
        $totalSubjects = Subject::count();

        // Recently edited teachers (eager-load classSubjects → subject for N+1 safety)
        $recentTeachers = Teacher::with('classSubjects.subject')
            ->latest('updated_at')
            ->take(5)
            ->get();

        // Recent registrations with a derived fee summary for the badge
        $students = Student::with(['schoolClass', 'fees'])
            ->latest('enrolment_date')
            ->take(5)
            ->get()
            ->map(function ($student) {
                $fee = $student->fees->sortByDesc('updated_at')->first();

                return [
                    'name'       => $student->full_name,
                    'class'      => $student->schoolClass?->display_name ?? 'Unassigned',
                    'fee_status' => $fee ? strtolower($fee->status) : 'cleared',
                    'balance'    => $fee ? (float) $fee->balance : 0,
                ];
            });

        return view('admin.dashboard', compact(
            'currentAcademicYear',
            'currentTerm',
            'todayCollections',
            'todayPayments',
            'urgentFees',
            'totalStudents',
            'totalStaff',
            'totalParents',
            'totalClasses',
            'totalSubjects',
            'recentTeachers',
            'students'
        ));
    }
}



