<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Concerns\RendersReportCards;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Fee;
use App\Models\Grade;
use App\Models\ParentProfile;
use App\Models\ReportCard;
use App\Models\SchoolSetting;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Models\User;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ParentController extends Controller
{
    use RendersReportCards;

    public function timetable(Request $request)
    {
        $children = $this->getChildren();
        $selectedChild = $this->getSelectedChild($children);
        if (! $selectedChild) {
            return redirect()->route('parent.dashboard');
        }

        $student = Student::with('schoolClass.gradeLevel.periods')->findOrFail($selectedChild->student_id);
        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : Term::current();
        $term ??= $terms->first();
        $slots = $student->class_id && $term
            ? TimetableSlot::with(['subject', 'teacher', 'period'])
                ->where('school_class_id', $student->class_id)
                ->where('term_id', $term->term_id)
                ->get()
            : collect();

        $periods = $student->schoolClass?->gradeLevel
            ? $student->schoolClass->gradeLevel->periods()->orderBy('order')->get()
            : collect();

        return view('parent.timetable', $this->parentLayoutVars($student, $children, 'Timetable') + [
            'student' => $student,
            'terms'   => $terms,
            'term'    => $term,
            'slots'   => $slots,
            'periods' => $periods,
        ]);
    }

    /** Resolve the logged-in parent profile (table: parents). */
    private function getParent(): ?ParentProfile
    {
        return ParentProfile::where('user_id', auth()->user()->id)->first();
    }

    /**
     * Students linked to this parent, with the relations the portals need.
     */
    private function getChildren(): \Illuminate\Database\Eloquent\Collection
    {
        return Student::where('parent_user_id', auth()->user()->id)
            ->with(['schoolClass.gradeLevel', 'schoolClass.teacher', 'user'])
            ->get();
    }

    /**
     * The child selected for single-child views. Honours session('selected_child_id');
     * falls back to the first child owned by the parent.
     */
    private function getSelectedChild(?\Illuminate\Database\Eloquent\Collection $children = null)
    {
        $children = $children ?? $this->getChildren();
        if ($children->isEmpty()) {
            return null;
        }

        // An explicit ?child_id= that is not this parent's child is a tampering
        // attempt, not a typo: reject it outright rather than quietly showing a
        // different child's data (Phase 4 exit checklist).
        if (request()->filled('child_id')) {
            $requested = $children->firstWhere('student_id', (int) request()->input('child_id'));

            abort_if($requested === null, 403);

            return $requested;
        }

        // A stale session selection (e.g. a child who has since left) falls back
        // quietly — the parent did not ask for it on this request.
        $sessionId = session('selected_child_id');

        return $sessionId
            ? ($children->firstWhere('student_id', (int) $sessionId) ?? $children->first())
            : $children->first();
    }

    /** Current academic term (is_current) joined to its academic year. */
    private function getCurrentTerm(): ?Term
    {
        return Term::where('is_current', true)->with('academicYear')->first()
            ?? Term::with('academicYear')->orderBy('start_date', 'desc')->first();
    }

    /** Shared layout variables for every parent page. */
    private function parentLayoutVars(?Student $selectedChild = null, $children = null, ?string $title = null)
    {
        $children = $children ?? $this->getChildren();
        return [
            'parentProfile'  => $this->getParent(),
            'students'       => $children,
            'selectedChild'  => $selectedChild,
            'selectedChildId'=> $selectedChild ? $selectedChild->student_id : null,
            'currentTerm'    => $this->getCurrentTerm(),
            'title'          => $title,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);
        $vars = $this->parentLayoutVars($selected, $children, 'Parent Portal');

        // Per-child high-level summaries (My Children cards).
        $childSummaries = $children->map(function ($student) {
            $grades = $student->grades()->get();
            $percentages = $grades->map->percentage->filter(fn ($p) => $p > 0);
            $gpa = $percentages->isNotEmpty() ? round(min(($percentages->avg() / 100) * 4, 4.0), 2) : 0;

            $attRate = $this->attendanceRate($student);

            $totalFees = (float) $student->fees()->sum('amount_due');
            $paidFees  = (float) $student->fees()->sum('amount_paid');
            $balance   = max(0, round($totalFees - $paidFees, 2));

            return [
                'student'            => $student,
                'gpa'                => $gpa,
                'attendance_rate'    => $attRate,
                'total_fees'         => $totalFees,
                'fee_balance'        => $balance,
                'fee_status'         => $balance > 0 ? 'Pending' : 'Cleared',
                'assessments'        => $grades->count(),
            ];
        });

        $vars['children']             = $childSummaries;
        $vars['attendanceRate']       = $selected ? $this->attendanceRate($selected) : 0;
        $vars['performanceTrend']     = $this->performanceTrend($children);
        $vars['recentResults']        = $selected ? Grade::where('student_id', $selected->student_id)
            ->with('classSubject.subject')->latest()->take(6)->get() : collect();
        // Grade rows are always scored, so "pending" work cannot exist — show the
        // latest recorded assessments instead.
        $vars['upcomingAssessments']  = $selected ? $selected->grades()->with('classSubject.subject')
            ->orderByDesc('created_at')->take(5)->get() : collect();

        $vars['overdueFees']  = $this->overdueFees($children);
        $vars['overdueTotal'] = (float) $vars['overdueFees']->sum(fn ($fee) => max(0, (float) $fee->amount_due - (float) $fee->amount_paid));

        return view('parent.dashboard', $vars);
    }

    /**
     * Overdue fees across every child (Phase 7).
     *
     * Uses Fee::scopeOverdue() — past due_date with an outstanding balance —
     * rather than trusting the stored status string, so the banner is correct
     * even if the nightly fees:flag-overdue command has not run yet.
     */
    private function overdueFees($children): \Illuminate\Support\Collection
    {
        if ($children->isEmpty()) {
            return collect();
        }

        return Fee::overdue()
            ->whereIn('student_id', $children->pluck('student_id'))
            ->with('student')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Attendance rate for a child, in the term given (or the current one).
     *
     * Delegates to ReportCardService so this portal cannot disagree with the
     * report card. Counting it here by hand is what produced 86% on one screen
     * and 100% on another for the same pupil: this page counted raw lesson rows
     * rather than calendar days, and treated a late arrival as an absence.
     */
    private function attendanceRate(Student $student, ?Term $term = null): int
    {
        $term ??= Term::current() ?? Term::orderByDesc('start_date')->first();

        if (! $term) {
            return 0;
        }

        return (int) round(app(ReportCardService::class)->attendanceSummary($student, $term)['rate'] ?? 0);
    }

    /** Monthly average grade percentage for the dashboard chart. */
    private function performanceTrend($children): \Illuminate\Support\Collection
    {
        $primary = $children->first();
        if (! $primary) {
            return collect();
        }
        $grades = $primary->grades()->get()->filter(fn ($g) => ($g->percentage ?? 0) > 0);
        if ($grades->isEmpty()) {
            return collect();
        }
        return $grades->groupBy(fn ($g) => optional($g->created_at)->format('Y-m'))
            ->map(fn ($items) => [
                'label' => optional($items->first()->created_at)->format('M'),
                'value' => round($items->avg('percentage'), 1),
            ])->values();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // My Children
    // ─────────────────────────────────────────────────────────────────────────
    public function children()
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);

        $childSummaries = $children->map(function ($student) {
            $grades = $student->grades()->get();
            $percentages = $grades->map->percentage->filter(fn ($p) => $p > 0);
            $gpa = $percentages->isNotEmpty() ? round(min(($percentages->avg() / 100) * 4, 4.0), 2) : 0;
            $attRate = $this->attendanceRate($student);
            $totalFees = (float) $student->fees()->sum('amount_due');
            $paidFees  = (float) $student->fees()->sum('amount_paid');
            $balance   = max(0, round($totalFees - $paidFees, 2));

            // Total assessments recorded for this child (grade rows are always scored).
            $assessments = $student->grades()->count();

            return [
                'student'           => $student,
                'gpa'               => $gpa,
                'attendance_rate'    => $attRate,
                'total_fees'        => $totalFees,
                'fee_balance'       => $balance,
                'fee_status'        => $balance > 0 ? 'Pending' : 'Cleared',
                'assessments'       => $assessments,
            ];
        });

        return view('parent.children', $this->parentLayoutVars($selected, $children, 'My Children') + [
            'children' => $childSummaries,
        ]);
    }

    /** Persist the selected child into the session then return to the previous page. */
    public function switchChild(Request $request)
    {
        $children = $this->getChildren();
        $id = (int) $request->input('child_id');
        if ($children->contains('student_id', $id)) {
            session(['selected_child_id' => $id]);
        }
        return back();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Attendance
// ─────────────────────────────────────────────────────────────────────
    public function attendance(Request $request)
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);
        if (! $selected) {
            return redirect()->route('parent.dashboard');
        }

        $student = Student::with(['schoolClass.teacher', 'user'])->findOrFail($selected->student_id);

        $records = $student->attendance()->orderBy('date', 'desc')->paginate(15);

        // These figures used to be counted here, by hand, and disagreed with the
        // report card for the same child: this page counted raw lesson rows
        // rather than calendar days, treated a late arrival as an absence, and
        // ignored the term window — so a pupil reading 100% on their report card
        // read 86% here. Attendance is now asked of the same service the report
        // card uses, which is the whole reason that service exists.
        $term = Term::current() ?? Term::orderByDesc('start_date')->first();
        $summary = $term
            ? app(ReportCardService::class)->attendanceSummary($student, $term)
            : ['present' => 0, 'absent' => 0, 'late' => 0, 'recorded' => 0, 'rate' => null];

        $attTotal    = $summary['recorded'];
        $daysPresent = $summary['present'];
        $absent      = $summary['absent'];
        $late        = $summary['late'];
        $rate        = $summary['rate'] ?? 0;

        // Monthly attendance rate for the chart (last 6 months present/absent).
        $trend = $student->attendance()->orderBy('date', 'desc')->take(60)->get()
            ->groupBy(fn ($r) => optional($r->date)->format('M Y'))
            ->map(fn ($g) => [
                'label' => $g->first()->date ? \Carbon\Carbon::parse($g->first()->date)->format('M') : '',
                'present' => $g->where('status', 'Present')->count(),
                'absent'  => $g->whereIn('status', ['Absent', 'Late'])->count(),
            ])->take(6)->reverse()->values();

        return view('parent.attendance', $this->parentLayoutVars($selected, $children, 'Attendance') + [
            'student'        => $student,
            'records'        => $records,
            'attendanceRate' => $rate,
            'daysPresent'    => $daysPresent,
            'totalDays'      => $attTotal,
            'absentDays'     => $absent,
            'lateDays'       => $late,
            'term'           => $term,
            'recentTrend'    => $trend,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Performance
    // ─────────────────────────────────────────────────────────────────────
    public function performance(Request $request)
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);
        if (! $selected) {
            return redirect()->route('parent.dashboard');
        }

        $student = Student::with(['schoolClass', 'user'])->findOrFail($selected->student_id);

        // Per-subject aggregation: avg percentage across all grade records for that class_subject.
        $bySubject = Grade::where('student_id', $student->student_id)
            ->with('classSubject.subject')
            ->get()
            ->groupBy(fn ($g) => $g->classSubject ? $g->classSubject->class_subject_id : 0);

        $subjects = $bySubject->map(function ($grades, $key) {
            $percentages = $grades->map->percentage->filter(fn ($p) => $p > 0);
            $avg = $percentages->isNotEmpty() ? round($percentages->avg(), 1) : 0;
            $subject = $grades->first()->classSubject->subject ?? null;
            return [
                'subject' => $subject ? $subject->subject_name : '—',
                'avg'     => $avg,
                'min'     => $percentages->isNotEmpty() ? round($percentages->min(), 1) : 0,
                'max'     => $percentages->isNotEmpty() ? round($percentages->max(), 1) : 0,
            ];
        })->sortByDesc('avg')->values();

        // Detailed row list (test/exam per record) for the breakdown table.
        $results = Grade::where('student_id', $student->student_id)
            ->with('classSubject.subject')
            ->orderByDesc('created_at')
            ->get();

        $overallAvg = $results->map->percentage->filter(fn ($p) => $p > 0);
        $gpa = $overallAvg->isNotEmpty() ? round(min(($overallAvg->avg() / 100) * 4, 4.0), 2) : 0;

        // Overall rank: share of students with a higher GPA (same class) — best-effort, 0 if none.
        $rank = 0;
        $rivals = Student::where('class_id', $student->class_id)->get()
            ->map(function ($s) {
                $p = $s->grades()->get()->map->percentage->filter(fn ($x) => $x > 0);
                return $p->isNotEmpty() ? ($p->avg() / 100) * 4 : 0;
            })->filter(fn ($x) => $x > $gpa)->count() + 1;
        $rank = $rivals;

        return view('parent.performance', $this->parentLayoutVars($selected, $children, 'Performance') + [
            'student' => $student,
            'subjects' => $subjects,
            'results' => $results,
            'gpa' => $gpa,
            'rank' => $rank,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Reports
    // ─────────────────────────────────────────────────────────────────────
    public function reports(Request $request)
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);
        if (! $selected) {
            return redirect()->route('parent.dashboard');
        }

        $student = Student::with(['schoolClass'])->findOrFail($selected->student_id);

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $years = AcademicYear::orderByDesc('start_date')->get();
        $termId = $request->query('term_id');
        $yearId = $request->query('academic_year_id');

        $termFilter = $terms;
        if ($yearId) {
            $termFilter = $terms->where('academic_year_id', $yearId);
        }
        $pickedTerm = $termId ? $terms->firstWhere('term_id', $termId) : $termFilter->first();

        // Build a report-card summary per applicable term.
        // Finalized cards for this child, keyed by term, so each summary row can
        // offer a download only where the class teacher has actually finalized.
        $finalizedCards = ReportCard::where('student_id', $selected->student_id)
            ->finalized()
            ->get()
            ->keyBy('term_id');

        $reportCards = $termFilter->map(function ($term) use ($selected, $finalizedCards) {
            $grades = Grade::where('student_id', $selected->student_id)
                ->where(function ($q) use ($term) {
                    $q->where('term_id', $term->term_id)
                      ->orWhere('term', $term->name);
                })->get();
            $percentages = $grades->map->percentage->filter(fn ($p) => $p > 0);
            $avg = $percentages->isNotEmpty() ? round($percentages->avg(), 1) : 0;

            $attRate = $this->attendanceRate($selected, $term);

            return [
                'term'        => $term,
                'year'        => $term->academicYear,
                'average'     => $avg,
                'attendance'  => $attRate,
                'class'       => $selected->schoolClass,
                'subjects'    => $grades->where('class_subject_id', '!=', null)->groupBy('class_subject_id')->count(),
                'card'        => $finalizedCards->get($term->term_id),
            ];
        })->filter(fn ($r) => $r['average'] > 0 || $r['attendance'] > 0)->values();

        return view('parent.reports', $this->parentLayoutVars($selected, $children, 'Reports') + [
            'student'    => $student,
            'terms'      => $terms,
            'years'      => $years,
            'reportCards' => $reportCards,
            'selectedTerm' => $pickedTerm,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Assignments
    // ─────────────────────────────────────────────────────────────────────
    public function assignments()
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);
        if (! $selected) {
            return redirect()->route('parent.dashboard');
        }

        $student = Student::with(['schoolClass'])->findOrFail($selected->student_id);

        // Real assignments set for the subjects this child's class takes, with the
        // child's own submission attached so the parent can see what is outstanding.
        $assignments = collect();

        if ($student->class_id) {
            $assignments = Assignment::published()
                ->forClass($student->class_id)
                ->with([
                    'classSubject.subject',
                    'classSubject.teacher',
                    'submissions' => fn ($q) => $q->where('student_id', $student->student_id),
                ])
                ->orderByDesc('due_at')
                ->take(40)
                ->get()
                ->map(function (Assignment $assignment) {
                    $submission = $assignment->submissions->first();
                    $assignment->setAttribute('child_submission', $submission);
                    $assignment->setAttribute('child_status', $assignment->statusForStudent($submission));

                    return $assignment;
                });
        }

        return view('parent.assignments', $this->parentLayoutVars($selected, $children, 'Assignments') + [
            'student'     => $student,
            'assignments' => $assignments,
            'counts'      => [
                'pending'   => $assignments->where('child_status', 'Pending')->count(),
                'overdue'   => $assignments->where('child_status', 'Overdue')->count(),
                'submitted' => $assignments->where('child_status', 'Submitted')->count(),
                'graded'    => $assignments->where('child_status', 'Graded')->count(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Fees
    // ─────────────────────────────────────────────────────────────────────
    public function fees()
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);
        if (! $selected) {
            return redirect()->route('parent.dashboard');
        }

        $student = Student::with(['schoolClass'])->findOrFail($selected->student_id);
        $fees = $student->fees()->orderBy('due_date', 'desc')->get();

        $totalDue = (float) $fees->sum('amount_due');
        $totalPaid = (float) $fees->sum('amount_paid');
        $balance = max(0, round($totalDue - $totalPaid, 2));
        $nextDue = $fees->where('status', '!=', 'Cleared')->sortBy('due_date')->first();

        // Payment history across this student's fees.
        $payments = Payment::whereIn('fee_id', $fees->pluck('fee_id'))
            ->orderBy('payment_date', 'desc')->get();

        // Proof-of-payment submissions this parent has made for this student,
        // whatever their review status — pending, approved or rejected.
        $submissions = \App\Models\PaymentSubmission::whereIn('fee_id', $fees->pluck('fee_id'))
            ->orderByDesc('created_at')->get();

        return view('parent.fees', $this->parentLayoutVars($selected, $children, 'Fees') + [
            'student' => $student,
            'fees'    => $fees,
            'totalDue' => $totalDue,
            'totalPaid' => $totalPaid,
            'balance' => $balance,
            'nextDue' => $nextDue,
            'payments'=> $payments,
            'submissions' => $submissions,
            'overdueCount' => $fees->where('status', 'Overdue')->count(),
            'overdueFees'  => $this->overdueFees($children->where('student_id', $student->student_id)),
            // Bank / mobile money details for the "How to pay" panel.
            'settings'     => SchoolSetting::all_settings(),
            // Overpayment carried forward — Student::grantCredit()/applyAvailableCredit().
            'creditBalance' => (float) $student->credit_balance,
        ]);
    }

    /**
     * Parent fee detail landing for notification CTAs: verify the fee belongs
     * to one of this parent's children, remember that child in the session,
     * then land on the Fees page with it pre-selected.
     */
    public function showFee(Fee $fee)
    {
        $children = $this->getChildren();

        // A parent may only open fees belonging to their own children.
        $owned = $children->firstWhere('student_id', $fee->student_id);
        if (! $owned) {
            abort(404);
        }

        session(['selected_child_id' => $owned->student_id]);

        return redirect()->route('parent.fees', ['child_id' => $owned->student_id]);
    }

    /**
     * Phase 11 — preview or download a child's finalized report card.
     *
     * Ownership is enforced by resolving the card through this parent's own
     * children, so a term id for someone else's child is a 404, not a leak.
     */
    public function reportCard(Request $request, int $student, int $term)
    {
        $owned = $this->getChildren()->firstWhere('student_id', $student);

        if (! $owned) {
            abort(404);
        }

        $card = ReportCard::where('student_id', $owned->student_id)
            ->where('term_id', $term)
            ->finalized()
            ->with('term')
            ->firstOrFail();

        $owned->loadMissing('schoolClass.gradeLevel');

        return $request->boolean('download')
            ? $this->downloadReportCard($owned, $card->term)
            : $this->renderReportCard($owned, $card->term);
    }

    /**
     * Print-friendly receipt for a single payment (Phase 7).
     *
     * Reuses the same view the bursar sees. The scoping check is the important
     * part: the payment's fee must belong to one of this parent's own children,
     * otherwise it is a 404 — a parent guessing payment ids learns nothing.
     */
    public function receipt(Payment $payment)
    {
        $payment->load(['fee.student', 'fee.feeItems', 'recordedBy']);

        $owned = $this->getChildren()->firstWhere('student_id', $payment->fee?->student_id);

        if (! $owned) {
            abort(404);
        }

        return view('admin.fees.receipt', [
            'payment'   => $payment,
            'backUrl'   => route('parent.fees', ['child_id' => $owned->student_id]),
            'backLabel' => 'Back to Fees',
        ]);
    }

    /**
     * Phase 5 — announcements across every child.
     *
     * A parent with children in two grade levels sees notices aimed at both,
     * because the visibility scope resolves their whole set of classes.
     */
    public function announcements()
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);
        $service  = app(\App\Services\AnnouncementService::class);

        return view('parent.announcements', $this->parentLayoutVars($selected, $children, 'Announcements') + [
            'announcements' => $service->feedFor(auth()->user()),
            'unreadCount'   => $service->unreadCount(auth()->user()),
        ]);
    }

    public function readAnnouncement(Announcement $announcement)
    {
        $visible = Announcement::visibleTo(auth()->user())
            ->where('announcements.announcement_id', $announcement->announcement_id)
            ->firstOrFail();

        app(\App\Services\AnnouncementService::class)->markRead($visible, auth()->user());

        return back();
    }

    public function readAllAnnouncements()
    {
        $count = app(\App\Services\AnnouncementService::class)->markAllRead(auth()->user());

        return back()->with('notification', $count > 0
            ? "Marked {$count} announcement(s) as read."
            : 'Nothing new to mark.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Settings
    // ─────────────────────────────────────────────────────────────────────
    public function settings()
    {
        $children = $this->getChildren();
        $selected = $this->getSelectedChild($children);

        $overallAttendance = 0;
        $attended = 0; $total = 0;
        foreach ($children as $child) {
            $attended += $this->attendanceRate($child);
            $total++;
        }
        $overallAttendance = $total > 0 ? (int) round($attended / $total) : 0;

        $totalFees = 0; $paidFees = 0;
        foreach ($children as $child) {
            $totalFees += (float) $child->fees()->sum('amount_due');
            $paidFees += (float) $child->fees()->sum('amount_paid');
        }

        return view('parent.settings', $this->parentLayoutVars($selected, $children, 'Settings') + [
            'children' => $children,
            'overallAttendance' => $overallAttendance,
            'totalFees' => $totalFees,
            'paidFees'  => $paidFees,
            'balance'   => max(0, round($totalFees - $paidFees, 2)),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        $profile = $this->getParent();
        if ($profile) {
            $profile->update([
                'phone'   => $data['phone'],
                'address' => $data['address'],
            ]);
        }

        return redirect()->route('parent.settings')->with('notification', 'Settings updated successfully.');
    }
}