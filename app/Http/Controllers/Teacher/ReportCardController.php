<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\RendersReportCards;
use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\ReportCard;
use App\Models\ReportCardComment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Term;
use App\Services\ReportCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Phase 11 — teacher side: per-subject comments, the class-teacher comment, and
 * the "Finalize Class Grades" workflow that assigns rank.
 *
 * Two distinct teacher roles appear here:
 *   • subject teacher — writes a remark for their own subject
 *   • class teacher (homeroom) — writes the overall comment and finalizes
 */
class ReportCardController extends Controller
{
    use RendersReportCards;

    public function __construct(private readonly ReportCardService $service)
    {
    }

    private function currentTeacher(): Teacher
    {
        return Teacher::where('user_id', auth()->id())->firstOrFail();
    }

    /** Classes this teacher may finalize — the ones they are homeroom for. */
    private function homeroomClass(Teacher $teacher, int $classId): SchoolClass
    {
        return SchoolClass::where('class_id', $classId)
            ->where('teacher_id', $teacher->teacher_id)
            ->firstOrFail();
    }

    private function resolveTerm(Request $request, $terms): ?Term
    {
        return $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : (Term::current() ?? $terms->first());
    }

    // ── Overview ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $teacher = $this->currentTeacher();
        $terms   = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term    = $this->resolveTerm($request, $terms);

        $classes = SchoolClass::where('teacher_id', $teacher->teacher_id)
            ->withCount('students')
            ->with('gradeLevel')
            ->get()
            ->map(function (SchoolClass $class) use ($term) {
                $missing   = $term ? $this->service->missingGrades($class, $term) : collect();
                $finalized = $term ? ReportCard::where('class_id', $class->class_id)
                    ->where('term_id', $term->term_id)->finalized()->count() : 0;

                return [
                    'class'          => $class,
                    'missing_count'  => $missing->count(),
                    'missing'        => $missing,
                    'finalized'      => $finalized,
                    'is_finalized'   => $finalized > 0,
                ];
            });

        return view('teacher.report-cards.index', compact('teacher', 'terms', 'term', 'classes'));
    }

    /** One class for one term: every student, their average, and rank if set. */
    public function show(Request $request, int $class): View
    {
        $teacher    = $this->currentTeacher();
        $schoolClass = $this->homeroomClass($teacher, $class);
        $terms      = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term       = $this->resolveTerm($request, $terms);

        abort_if($term === null, 404, 'No academic terms have been set up.');

        $students = Student::where('class_id', $schoolClass->class_id)
            ->orderBy('last_name')->orderBy('first_name')->get();

        $cards = ReportCard::where('class_id', $schoolClass->class_id)
            ->where('term_id', $term->term_id)
            ->get()
            ->keyBy('student_id');

        $rows = $students->map(function (Student $student) use ($term, $cards) {
            $card = $cards->get($student->student_id);

            return [
                'student' => $student,
                'card'    => $card,
                'average' => $card?->term_average !== null
                    ? (float) $card->term_average
                    : $this->service->averageOf($this->service->subjectRows($student, $term)),
            ];
        });

        return view('teacher.report-cards.show', [
            'teacher'     => $teacher,
            'schoolClass' => $schoolClass,
            'terms'       => $terms,
            'term'        => $term,
            'rows'        => $rows,
            'missing'     => $this->service->missingGrades($schoolClass, $term),
            'isFinalized' => $this->service->isLocked($schoolClass->class_id, $term->term_id),
        ]);
    }

    // ── Finalize ──────────────────────────────────────────────────────────────

    public function finalize(Request $request, int $class): RedirectResponse
    {
        $teacher     = $this->currentTeacher();
        $schoolClass = $this->homeroomClass($teacher, $class);
        $term        = Term::findOrFail($request->integer('term_id'));

        try {
            $count = $this->service->finalize($schoolClass, $term, $teacher);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with(
            'notification',
            "Finalized {$count} report card(s) for {$schoolClass->class_name}, {$term->name}. Ranks assigned and marks locked."
        );
    }

    // ── Comments ──────────────────────────────────────────────────────────────

    /**
     * Class-teacher comment for one student. Editable before and after
     * finalizing — locking is about marks, not about what a teacher wants to say.
     */
    public function saveOverallComment(Request $request, int $class, int $student): RedirectResponse
    {
        $teacher     = $this->currentTeacher();
        $schoolClass = $this->homeroomClass($teacher, $class);

        $validated = $request->validate([
            'term_id' => ['required', 'exists:terms,term_id'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $pupil = Student::where('student_id', $student)
            ->where('class_id', $schoolClass->class_id)
            ->firstOrFail();

        $card = ReportCard::firstOrNew([
            'student_id' => $pupil->student_id,
            'term_id'    => (int) $validated['term_id'],
        ]);

        $card->class_id = $schoolClass->class_id;
        $card->class_teacher_comment = $validated['comment'] ?: null;
        $card->audit_reason = 'Class teacher comment updated';
        $card->save();

        return back()->with('notification', "Comment saved for {$pupil->full_name}.");
    }

    /**
     * Subject remarks. A teacher may only write against a class_subject they
     * teach, which is what keeps one subject teacher out of another's column.
     */
    public function subjectComments(Request $request, int $classSubject): View
    {
        $teacher = $this->currentTeacher();

        $assignment = ClassSubject::with(['schoolClass', 'subject'])
            ->where('class_subject_id', $classSubject)
            ->where('teacher_id', $teacher->teacher_id)
            ->firstOrFail();

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term  = $this->resolveTerm($request, $terms);

        abort_if($term === null, 404, 'No academic terms have been set up.');

        $students = Student::where('class_id', $assignment->class_id)
            ->orderBy('last_name')->orderBy('first_name')->get();

        $comments = ReportCardComment::where('class_subject_id', $assignment->class_subject_id)
            ->where('term_id', $term->term_id)
            ->get()
            ->keyBy('student_id');

        return view('teacher.report-cards.subject-comments', [
            'teacher'    => $teacher,
            'assignment' => $assignment,
            'terms'      => $terms,
            'term'       => $term,
            'students'   => $students,
            'comments'   => $comments,
        ]);
    }

    public function saveSubjectComments(Request $request, int $classSubject): RedirectResponse
    {
        $teacher = $this->currentTeacher();

        $assignment = ClassSubject::where('class_subject_id', $classSubject)
            ->where('teacher_id', $teacher->teacher_id)
            ->firstOrFail();

        $validated = $request->validate([
            'term_id'    => ['required', 'exists:terms,term_id'],
            'comments'   => ['array'],
            'comments.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $validIds = Student::where('class_id', $assignment->class_id)->pluck('student_id');
        $saved = 0;

        foreach ($validated['comments'] ?? [] as $studentId => $text) {
            if (! $validIds->contains((int) $studentId)) {
                continue; // ignore ids posted for students outside this class
            }

            $existing = ReportCardComment::where('student_id', (int) $studentId)
                ->where('term_id', (int) $validated['term_id'])
                ->where('class_subject_id', $assignment->class_subject_id)
                ->first();

            if (blank($text)) {
                $existing?->delete();

                continue;
            }

            $comment = $existing ?? new ReportCardComment([
                'student_id'       => (int) $studentId,
                'term_id'          => (int) $validated['term_id'],
                'class_subject_id' => $assignment->class_subject_id,
            ]);

            $comment->comment    = $text;
            $comment->teacher_id = $teacher->teacher_id;
            $comment->save();

            $saved++;
        }

        return back()->with('notification', "Saved {$saved} remark(s).");
    }

    // ── Preview / download ────────────────────────────────────────────────────

    public function preview(Request $request, int $class, int $student)
    {
        $teacher     = $this->currentTeacher();
        $schoolClass = $this->homeroomClass($teacher, $class);
        $term        = Term::findOrFail($request->integer('term_id'));

        $pupil = Student::where('student_id', $student)
            ->where('class_id', $schoolClass->class_id)
            ->firstOrFail();

        return $request->boolean('download')
            ? $this->downloadReportCard($pupil, $term)
            : $this->renderReportCard($pupil, $term);
    }
}
