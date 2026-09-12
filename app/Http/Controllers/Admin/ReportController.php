<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Fee Collection Report Dashboard.
     */
    public function feeCollection(Request $request)
    {
        $this->authorize('viewReports', Fee::class);

        $filters = $this->validatedFilters($request);

        $academicYears = AcademicYear::orderByDesc('start_date')->get(['year_id', 'label']);
        $terms         = Term::orderBy('start_date')->get(['term_id', 'name']);
        $classes       = SchoolClass::orderBy('class_name')->get(['class_id', 'class_name']);

        $cacheKey = 'fee_report_' . md5(json_encode($filters) . (string) auth()->id());

        $reportData = Cache::remember($cacheKey, 600, function () use ($filters) {
            return $this->buildFeeReport($filters);
        });

        return view('admin.reports.fee-collection', compact('reportData', 'filters', 'academicYears', 'terms', 'classes'));
    }

    /**
     * Shared validation rule set for fee reports.
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,year_id',
            'term_id'          => 'nullable|exists:terms,term_id',
            'class_id'         => 'nullable|exists:school_classes,class_id',
            'date_from'        => 'nullable|date',
            'date_to'          => 'nullable|date|after_or_equal:date_from',
        ]);
    }

    /**
     * Apply the shared fee filters (fees table + student class).
     */
    private function applyFeeFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['academic_year_id'] ?? null, fn ($q, $y) => $q->where('academic_year_id', $y))
            ->when($filters['term_id'] ?? null, fn ($q, $t) => $q->where('term_id', $t))
            ->when($filters['class_id'] ?? null, fn ($q, $c) => $q->whereHas('student', fn ($sq) => $sq->where('class_id', $c)))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('due_date', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('due_date', '<=', $d));
    }

    /**
     * Apply the shared payment filters (through the fee).
     */
    private function applyPaymentFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['academic_year_id'] ?? null, fn ($q, $y) => $q->whereHas('fee', fn ($f) => $f->where('academic_year_id', $y)))
            ->when($filters['term_id'] ?? null, fn ($q, $t) => $q->whereHas('fee', fn ($f) => $f->where('term_id', $t)))
            ->when($filters['class_id'] ?? null, fn ($q, $c) => $q->whereHas('fee.student', fn ($s) => $s->where('class_id', $c)))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('payment_date', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('payment_date', '<=', $d));
    }

    /**
     * Build every dataset surfaced on the fee collection report. Each aggregate
     * uses a clone of the base builders so terminal operations never contaminate
     * one another.
     */
    private function buildFeeReport(array $filters): array
    {
        $feeBase = $this->applyFeeFilters(
            Fee::query()->with(['student.schoolClass', 'academicYear', 'term']),
            $filters
        );

        $paymentBase = $this->applyPaymentFilters(
            Payment::query()->with(['fee.student.schoolClass', 'fee.academicYear', 'fee.term']),
            $filters
        );

        $totalAmountDue = (float) (clone $feeBase)->sum('amount_due');
        $totalCollected = (float) (clone $paymentBase)->sum('amount');
        $collectionRate = $totalAmountDue > 0 ? round(($totalCollected / $totalAmountDue) * 100, 1) : 0.0;

        $summary = [
            'total_fees'        => (clone $feeBase)->count(),
            'total_amount_due'  => $totalAmountDue,
            'total_amount_paid' => (float) (clone $feeBase)->sum('amount_paid'),
            'total_balance'     => (float) (clone $feeBase)->sum(DB::raw('amount_due - amount_paid')),
            'total_payments'    => (clone $paymentBase)->count(),
            'total_collected'   => $totalCollected,
            'collection_rate'   => $collectionRate,
        ];

        $statusBreakdown = (clone $feeBase)
            ->selectRaw('status, COUNT(*) as count, SUM(amount_due) as amount')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->status => [
                'count'  => (int) $item->count,
                'amount' => (float) $item->amount,
            ]]);

        $trend = (clone $paymentBase)
            ->selectRaw('DATE(payment_date) as date, COUNT(*) as count, SUM(amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top payers are derived from fees (amount_paid) grouped per student so
        // the student relationship resolves correctly on a Fee model.
        $topPayers = (clone $feeBase)
            ->selectRaw('student_id, SUM(amount_paid) as total_paid')
            ->groupBy('student_id')
            ->orderByDesc('total_paid')
            ->limit(10)
            ->get();

        $overdueFees = Fee::query()
            ->where('due_date', '<', now())
            ->where('status', '!=', 'Cleared')
            ->when($filters['academic_year_id'] ?? null, fn ($q, $y) => $q->where('academic_year_id', $y))
            ->when($filters['term_id'] ?? null, fn ($q, $t) => $q->where('term_id', $t))
            ->with('student.schoolClass')
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        $paymentMethods = (clone $paymentBase)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as amount')
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->payment_method => [
                'count'  => (int) $item->count,
                'amount' => (float) $item->amount,
            ]]);

        return [
            'summary'          => $summary,
            'status_breakdown' => $statusBreakdown,
            'trend'            => $trend,
            'top_payers'       => $topPayers,
            'overdue_fees'     => $overdueFees,
            'payment_methods'  => $paymentMethods,
            'filters'          => $filters,
        ];
    }

    /**
     * Export fee collection report to CSV.
     */
    public function exportFeeCollection(Request $request)
    {
        $this->authorize('exportReports', Fee::class);

        $reportData = $this->buildFeeReport($this->validatedFilters($request));

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fee_collection_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($reportData) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Fee Collection Report']);
            fputcsv($handle, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['SUMMARY STATISTICS']);
            fputcsv($handle, ['Total Fees', $reportData['summary']['total_fees']]);
            fputcsv($handle, ['Total Amount Due', 'ZMW ' . number_format((float) $reportData['summary']['total_amount_due'], 2)]);
            fputcsv($handle, ['Total Collected', 'ZMW ' . number_format((float) $reportData['summary']['total_collected'], 2)]);
            fputcsv($handle, ['Collection Rate', $reportData['summary']['collection_rate'] . '%']);
            fputcsv($handle, []);

            fputcsv($handle, ['STATUS BREAKDOWN']);
            fputcsv($handle, ['Status', 'Count', 'Amount']);
            foreach ($reportData['status_breakdown'] as $status => $data) {
                fputcsv($handle, [$status, $data['count'], 'ZMW ' . number_format((float) $data['amount'], 2)]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['PAYMENT METHODS']);
            fputcsv($handle, ['Method', 'Count', 'Amount']);
            foreach ($reportData['payment_methods'] as $method => $data) {
                fputcsv($handle, [str_replace('_', ' ', $method) ?: 'Other', $data['count'], 'ZMW ' . number_format((float) $data['amount'], 2)]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['TOP PAYERS']);
            fputcsv($handle, ['Student', 'Class', 'Total Paid']);
            foreach ($reportData['top_payers'] as $payer) {
                fputcsv($handle, [
                    $payer->student->full_name ?? 'Unknown',
                    $payer->student->schoolClass->class_name ?? 'N/A',
                    'ZMW ' . number_format((float) $payer->total_paid, 2),
                ]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['OVERDUE FEES']);
            fputcsv($handle, ['Student', 'Class', 'Amount Due', 'Due Date', 'Days Overdue']);
            foreach ($reportData['overdue_fees'] as $fee) {
                fputcsv($handle, [
                    $fee->student->full_name ?? 'Unknown',
                    $fee->student->schoolClass->class_name ?? 'N/A',
                    'ZMW ' . number_format((float) $fee->amount_due, 2),
                    $fee->due_date->format('Y-m-d'),
                    $fee->due_date->diffInDays(now()),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Student financial summary.
     */
    public function studentFinancials(Request $request, $student)
    {
        $this->authorize('viewFinancials', Fee::class);

        $student = Student::with(['schoolClass', 'guardian'])->findOrFail($student);

        $fees = Fee::with(['feeItems', 'payments', 'academicYear', 'term'])
            ->where('student_id', $student->student_id)
            ->orderByDesc('due_date')
            ->get();

        $summary = [
            'total_fees'    => $fees->count(),
            'total_due'     => $fees->sum('amount_due'),
            'total_paid'    => $fees->sum('amount_paid'),
            'total_balance' => $fees->sum(fn ($fee) => $fee->balance),
            'cleared'       => $fees->where('status', 'Cleared')->count(),
            'pending'       => $fees->where('status', 'Pending')->count(),
            'partial'       => $fees->where('status', 'Partially Paid')->count(),
            'overdue'       => $fees->where('status', 'Overdue')->count(),
        ];

        return view('admin.students.financial-summary', compact('student', 'fees', 'summary'));
    }

    /**
     * Printable statement of account for a student.
     */
    public function statement(Request $request, $student)
    {
        $this->authorize('viewFinancials', Fee::class);

        $student = Student::with(['schoolClass', 'guardian'])->findOrFail($student);

        $fees = Fee::with(['feeItems', 'payments', 'academicYear', 'term'])
            ->where('student_id', $student->student_id)
            ->orderBy('due_date')
            ->get();

        return view('admin.students.statement', compact('student', 'fees'));
    }
}