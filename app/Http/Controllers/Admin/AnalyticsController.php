<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Term;
use App\Services\AnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Phase 9 — attendance, fee aging and the school-wide performance report.
 *
 * Every action is gated by the existing viewReports / exportReports abilities,
 * the same ones the fee-collection report uses.
 */
class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics)
    {
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    public function attendance(Request $request): View
    {
        $this->authorize('viewReports', Student::class);

        $filters = $this->attendanceFilters($request);

        $report = $this->analytics->remember(
            'attendance',
            $filters,
            fn () => $this->analytics->attendanceReport($filters)
        );

        return view('admin.reports.attendance', [
            'report'        => $report,
            'filters'       => $filters,
            'terms'         => Term::with('academicYear')->orderByDesc('start_date')->get(),
            'classes'       => SchoolClass::orderBy('class_name')->get(),
            'classSubjects' => ClassSubject::with(['subject', 'schoolClass'])->get()
                ->sortBy(fn (ClassSubject $cs) => $cs->subject?->subject_name),
        ]);
    }

    public function exportAttendance(Request $request): StreamedResponse
    {
        $this->authorize('exportReports', Student::class);

        $filters = $this->attendanceFilters($request);
        $report = $this->analytics->attendanceReport($filters);

        return $this->csv('attendance_report.csv', function ($handle) use ($report) {
            fputcsv($handle, ['Attendance Report']);
            fputcsv($handle, ['Generated', now()->format('Y-m-d H:i:s')]);
            if ($report['from'] && $report['to']) {
                fputcsv($handle, ['Range', $report['from']->format('Y-m-d') . ' to ' . $report['to']->format('Y-m-d')]);
            }
            if ($report['school_days']) {
                fputcsv($handle, ['School days in term (excludes weekends and holidays)', $report['school_days']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['SUMMARY']);
            fputcsv($handle, ['Students', $report['summary']['students']]);
            fputcsv($handle, ['Days present', $report['summary']['present']]);
            fputcsv($handle, ['Days absent', $report['summary']['absent']]);
            fputcsv($handle, ['Days late', $report['summary']['late']]);
            fputcsv($handle, ['Average rate', ($report['summary']['rate'] ?? 0) . '%']);
            fputcsv($handle, []);

            fputcsv($handle, ['BY CLASS']);
            fputcsv($handle, ['Class', 'Students', 'Present', 'Absent', 'Late', 'Rate %']);
            foreach ($report['by_class'] as $row) {
                fputcsv($handle, [$row['class'], $row['students'], $row['present'], $row['absent'], $row['late'], $row['rate']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['BY SUBJECT']);
            fputcsv($handle, ['Subject', 'Records', 'Present', 'Absent', 'Late', 'Rate %']);
            foreach ($report['by_subject'] as $row) {
                fputcsv($handle, [$row['subject'], $row['records'], $row['present'], $row['absent'], $row['late'], $row['rate']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['BY STUDENT']);
            fputcsv($handle, ['Student', 'Student No.', 'Class', 'Present', 'Absent', 'Late', 'Days recorded', 'Rate %']);
            foreach ($report['per_student'] as $row) {
                fputcsv($handle, [
                    $row['student']->full_name,
                    $row['student']->student_number,
                    $row['class'],
                    $row['present'], $row['absent'], $row['late'],
                    $row['recorded'], $row['rate'],
                ]);
            }
        });
    }

    // ── Fee aging ─────────────────────────────────────────────────────────────

    public function aging(Request $request): View
    {
        $this->authorize('viewFinancials', Fee::class);

        $filters = ['class_id' => $request->integer('class_id') ?: null];

        $report = $this->analytics->remember(
            'aging',
            $filters,
            fn () => $this->analytics->agingReport($filters),
            600
        );

        return view('admin.reports.aging', [
            'report'  => $report,
            'filters' => $filters,
            'classes' => SchoolClass::orderBy('class_name')->get(),
        ]);
    }

    public function exportAging(Request $request): StreamedResponse
    {
        $this->authorize('exportReports', Fee::class);

        $report = $this->analytics->agingReport(['class_id' => $request->integer('class_id') ?: null]);

        return $this->csv('fee_aging_report.csv', function ($handle) use ($report) {
            fputcsv($handle, ['Fee Aging Report']);
            fputcsv($handle, ['As at', $report['as_of']->format('Y-m-d')]);
            fputcsv($handle, []);

            fputcsv($handle, ['AGING BUCKETS']);
            fputcsv($handle, ['Bucket', 'Fees', 'Balance (ZMW)']);
            foreach ($report['buckets'] as $bucket => $data) {
                fputcsv($handle, [$bucket, $data['count'], number_format($data['balance'], 2, '.', '')]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['BY CLASS']);
            fputcsv($handle, ['Class', 'Fees', 'Students', 'Balance (ZMW)', 'Worst days late']);
            foreach ($report['by_class'] as $row) {
                fputcsv($handle, [$row['class'], $row['fees'], $row['students'], number_format($row['balance'], 2, '.', ''), $row['worst']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['OUTSTANDING FEES']);
            fputcsv($handle, ['Student', 'Student No.', 'Class', 'Description', 'Due date', 'Days late', 'Balance (ZMW)', 'Bucket', 'Reference']);
            foreach ($report['rows'] as $row) {
                fputcsv($handle, [
                    $row['student']?->full_name,
                    $row['student']?->student_number,
                    $row['class'],
                    $row['description'],
                    $row['due_date']?->format('Y-m-d'),
                    $row['days_late'],
                    number_format($row['balance'], 2, '.', ''),
                    $row['bucket'],
                    $row['fee']->payment_reference,
                ]);
            }
        });
    }

    // ── School-wide performance (9.x) ─────────────────────────────────────────

    public function schoolWide(Request $request): View
    {
        $this->authorize('viewReports', Student::class);

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $this->resolveTerm($request, $terms);

        $report = $term
            ? $this->analytics->remember(
                'school-wide',
                ['term_id' => $term->term_id],
                fn () => $this->analytics->schoolWideReport($term)
            )
            : null;

        return view('admin.reports.school-wide', [
            'report' => $report,
            'terms'  => $terms,
            'term'   => $term,
        ]);
    }

    public function exportSchoolWide(Request $request): StreamedResponse
    {
        $this->authorize('exportReports', Student::class);

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $this->resolveTerm($request, $terms);

        abort_if($term === null, 404, 'No academic terms exist to report on.');

        $report = $this->analytics->schoolWideReport($term);
        $section = $request->query('section', 'all');

        return $this->csv("school_wide_{$section}.csv", function ($handle) use ($report, $section) {
            fputcsv($handle, ['School-Wide Performance Report']);
            fputcsv($handle, ['Term', $report['term']->name . ' ' . ($report['term']->academicYear?->label ?? '')]);
            fputcsv($handle, ['Generated', $report['generated_at']->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['Pass mark', $report['pass_mark'] . '%']);
            fputcsv($handle, []);

            if (in_array($section, ['all', 'overview'], true)) {
                fputcsv($handle, ['OVERVIEW']);
                foreach ([
                    'Total students' => $report['overview']['students'],
                    'With marks'     => $report['overview']['marked'],
                    'Grade levels'   => $report['overview']['grade_levels'],
                    'Classes'        => $report['overview']['classes'],
                    'Average'        => ($report['overview']['average'] ?? 0) . '%',
                    'Pass rate'      => ($report['overview']['pass_rate'] ?? 0) . '%',
                ] as $label => $value) {
                    fputcsv($handle, [$label, $value]);
                }
                fputcsv($handle, []);
            }

            if (in_array($section, ['all', 'grade-level'], true)) {
                fputcsv($handle, ['PERFORMANCE BY GRADE LEVEL']);
                fputcsv($handle, ['Grade level', 'Students', 'Average %', 'Pass rate %', 'Top student', 'Top %', 'Bottom student', 'Bottom %']);
                foreach ($report['by_grade_level'] as $row) {
                    fputcsv($handle, [
                        $row['grade_level'], $row['students'], $row['average'], $row['pass_rate'],
                        $row['top']['student']->full_name ?? '', $row['top']['average'] ?? '',
                        $row['bottom']['student']->full_name ?? '', $row['bottom']['average'] ?? '',
                    ]);
                }
                fputcsv($handle, []);
            }

            if (in_array($section, ['all', 'subject'], true)) {
                fputcsv($handle, ['PERFORMANCE BY SUBJECT']);
                fputcsv($handle, ['Subject', 'Students', 'Average %', 'Pass rate %', 'Top class', 'Top %', 'Bottom class', 'Bottom %']);
                foreach ($report['by_subject'] as $row) {
                    fputcsv($handle, [
                        $row['subject'], $row['students'], $row['average'], $row['pass_rate'],
                        $row['top_class'] ?? '', $row['top_score'] ?? '',
                        $row['bottom_class'] ?? '', $row['bottom_score'] ?? '',
                    ]);
                }
                fputcsv($handle, []);
            }

            if (in_array($section, ['all', 'class'], true)) {
                fputcsv($handle, ['CLASS PERFORMANCE SUMMARY']);
                fputcsv($handle, ['Rank', 'Class', 'Class teacher', 'Students', 'Average %', 'Pass rate %']);
                foreach ($report['by_class'] as $row) {
                    fputcsv($handle, [$row['rank'], $row['class'], $row['teacher'], $row['students'], $row['average'], $row['pass_rate']]);
                }
                fputcsv($handle, []);
            }

            if (in_array($section, ['all', 'low-performers'], true)) {
                fputcsv($handle, ['LOW-PERFORMING STUDENTS (below ' . $report['threshold'] . '%)']);
                fputcsv($handle, ['Student', 'Student No.', 'Class', 'Average %']);
                foreach ($report['low_performers'] as $row) {
                    fputcsv($handle, [
                        $row['student']->full_name,
                        $row['student']->student_number,
                        $row['student']->schoolClass?->class_name,
                        $row['average'],
                    ]);
                }
            }
        });
    }

    /** The low-performer threshold is admin-configurable, per the plan. */
    public function saveThreshold(Request $request): RedirectResponse
    {
        $this->authorize('viewReports', Student::class);

        $validated = $request->validate([
            'threshold' => ['required', 'numeric', 'min:1', 'max:99'],
        ]);

        SchoolSetting::set('report_low_threshold', (string) $validated['threshold']);
        AnalyticsService::flush();

        return back()->with('notification', "Low-performer threshold set to {$validated['threshold']}%.");
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function attendanceFilters(Request $request): array
    {
        return [
            'term_id'          => $request->integer('term_id') ?: null,
            'class_id'         => $request->integer('class_id') ?: null,
            'class_subject_id' => $request->integer('class_subject_id') ?: null,
            'student_id'       => $request->integer('student_id') ?: null,
            'from'             => $request->query('from') ?: null,
            'to'               => $request->query('to') ?: null,
        ];
    }

    private function resolveTerm(Request $request, $terms): ?Term
    {
        return $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : (Term::current() ?? $terms->first());
    }

    private function csv(string $filename, callable $writer): StreamedResponse
    {
        return response()->stream(function () use ($writer) {
            $handle = fopen('php://output', 'w');
            $writer($handle);
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
