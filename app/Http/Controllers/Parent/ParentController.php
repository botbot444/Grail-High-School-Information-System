<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ParentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        // Build a per-child summary collection used by the Dashboard + My Children tabs
        $children = $students->map(function ($student) {
            return $this->childSummary($student);
        });

        // Primary child used for the dashboard overview (first child)
        $primary = $children->first();

        // Aggregate metrics across all children
        $attendanceRate = $primary['attendance_rate'] ?? 0;
        $gpa = $primary['gpa'] ?? 0;
        $feeBalance = $primary['fee_balance'] ?? 0;
        $totalFees = $primary['total_fees'] ?? 0;
        $pendingAssignments = (int) $children->sum('pending_assignments');

        // Recent results for the primary child
        $results = $primary['results'] ?? collect();

        // Performance trend (monthly avg percentage) for the dashboard chart
        $performanceTrend = $this->performanceTrend($students);

        return view('parent.dashboard', compact(
            'parentProfile',
            'students',
            'children',
            'primary',
            'attendanceRate',
            'gpa',
            'feeBalance',
            'totalFees',
            'pendingAssignments',
            'performanceTrend',
            'results'
        ));
    }

    /**
     * Compute the summary metrics for a single child.
     *
     * @return array<string, mixed>
     */
    private function childSummary(Student $student): array
    {
        // GPA on a 4.0 scale derived from the average of recorded grade percentages
        $percentages = $student->grades->map->percentage->filter(fn ($p) => $p > 0);
        $gpa = $percentages->isNotEmpty() ? round(min(($percentages->avg() / 100) * 4, 4.0), 2) : 0;

        // Attendance rate (% of records marked Present)
        $attendanceTotal = $student->attendance()->count();
        $attendancePresent = $student->attendance()->where('status', 'Present')->count();
        $attendanceRate = $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100) : 0;

        // Fee balance
        $totalFees = (float) $student->fees()->sum('amount_due');
        $paidFees = (float) $student->fees()->sum('amount_paid');
        $feeBalance = max(0, round($totalFees - $paidFees, 2));

        // Pending assignments approximated from grade records with no recorded score
        $pendingAssignments = $student->grades()->whereNull('score')->count();

        // Recent results (latest recorded grades, with subject names)
        $recentResults = $student->grades()
            ->with('classSubject.subject')
            ->latest()
            ->take(6)
            ->get();

        return [
            'student'              => $student,
            'gpa'                  => $gpa,
            'attendance_rate'      => $attendanceRate,
            'total_fees'           => $totalFees,
            'fee_balance'          => $feeBalance,
            'fee_status'           => $feeBalance > 0 ? 'Pending' : 'Cleared',
            'pending_assignments'  => $pendingAssignments,
            'results'              => $recentResults,
        ];
    }

    /**
     * Build a simple monthly performance trend (avg grade percentage) for the
     * primary child's dashboard chart. Returns a collection of
     * ['label' => 'Sep', 'value' => 78] ordered by month; falls back to empty.
     *
     * @return \Illuminate\Support\Collection<int, array{label: string, value: float}>
     */
    private function performanceTrend(Collection $students): Collection
    {
        $primary = $students->first();

        if (! $primary) {
            return collect();
        }

        $grades = $primary->grades->filter(fn ($grade) => $grade->percentage > 0);

        if ($grades->isEmpty()) {
            return collect();
        }

        // Group by the calendar month the grade was recorded (created_at) and average the percentage
        $grouped = $grades->groupBy(fn ($grade) => optional($grade->created_at)->format('Y-m'));

        $months = $grouped
            ->map(function ($items) {
                return [
                    'month' => optional($items->first()->created_at)->format('M'),
                    'value' => round($items->avg('percentage'), 1),
                ];
            })
            ->values();

        return $months;
    }
}
