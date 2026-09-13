<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RendersReportCards;
use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\ReportCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Phase 11 — admin side: browse and print report cards for any class, and the
 * unfinalize override the plan reserves for administrators.
 */
class ReportCardController extends Controller
{
    use RendersReportCards;

    public function __construct(private readonly ReportCardService $service)
    {
    }

    public function index(Request $request): View
    {
        $terms   = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term    = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : (Term::current() ?? $terms->first());

        $classes = SchoolClass::with(['gradeLevel', 'teacher'])
            ->withCount('students')
            ->orderBy('class_name')
            ->get()
            ->map(function (SchoolClass $class) use ($term) {
                $finalized = $term
                    ? ReportCard::where('class_id', $class->class_id)
                        ->where('term_id', $term->term_id)->finalized()->count()
                    : 0;

                return [
                    'class'      => $class,
                    'finalized'  => $finalized,
                    'is_finalized' => $finalized > 0,
                ];
            });

        $selectedClass = $request->filled('class_id')
            ? SchoolClass::with('gradeLevel')->find((int) $request->class_id)
            : null;

        $rows = collect();

        if ($selectedClass && $term) {
            $cards = ReportCard::where('class_id', $selectedClass->class_id)
                ->where('term_id', $term->term_id)
                ->with('finalizedByTeacher')
                ->get()
                ->keyBy('student_id');

            $rows = Student::where('class_id', $selectedClass->class_id)
                ->orderBy('last_name')->orderBy('first_name')
                ->get()
                ->map(fn (Student $student) => [
                    'student' => $student,
                    'card'    => $cards->get($student->student_id),
                ]);
        }

        return view('admin.report-cards.index', compact(
            'terms', 'term', 'classes', 'selectedClass', 'rows'
        ));
    }

    /** Preview or download any student's card. Admins are unrestricted by design. */
    public function show(Request $request, int $student)
    {
        $pupil = Student::findOrFail($student);
        $term  = Term::findOrFail($request->integer('term_id'));

        return $request->boolean('download')
            ? $this->downloadReportCard($pupil, $term)
            : $this->renderReportCard($pupil, $term);
    }

    /**
     * Unfinalize a class + term so marks can be corrected. Audit-logged via the
     * Auditable trait on ReportCard, with the admin's stated reason attached.
     */
    public function unfinalize(Request $request, int $class): RedirectResponse
    {
        $schoolClass = SchoolClass::findOrFail($class);

        $validated = $request->validate([
            'term_id' => ['required', 'exists:terms,term_id'],
            'reason'  => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'reason.required' => 'Give a reason — it is written to the audit log.',
            'reason.min'      => 'The reason needs to be a little more specific.',
        ]);

        $term  = Term::findOrFail((int) $validated['term_id']);
        $count = $this->service->unfinalize($schoolClass, $term, $validated['reason']);

        return back()->with(
            'notification',
            "Unfinalized {$count} report card(s) for {$schoolClass->class_name}, {$term->name}. Ranks cleared; marks can be edited again."
        );
    }
}
