<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Student;
use App\Models\Term;
use App\Services\ReportCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Shared report card rendering for all four portals.
 *
 * Each portal decides WHO may see a card; this trait decides WHAT is rendered,
 * so the preview and the PDF can never drift apart.
 */
trait RendersReportCards
{
    protected function reportCardService(): ReportCardService
    {
        return app(ReportCardService::class);
    }

    /** On-screen preview (same template, HTML chrome). */
    protected function renderReportCard(Student $student, Term $term): View
    {
        return view('reports.report-card', $this->reportCardService()->build($student, $term) + [
            'forPdf' => false,
        ]);
    }

    /** Downloadable PDF, named so a folder of them stays sortable. */
    protected function downloadReportCard(Student $student, Term $term): Response
    {
        $data = $this->reportCardService()->build($student, $term) + ['forPdf' => true];

        $filename = sprintf(
            'report-card-%s-%s-%s.pdf',
            str($student->student_number ?: $student->student_id)->slug(),
            str($student->full_name)->slug(),
            str($term->name)->slug()
        );

        return Pdf::loadView('reports.report-card', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}
