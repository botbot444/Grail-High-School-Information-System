<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\Fee;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Phase 9 — the figures behind every report.
 *
 * Term averages come from ReportCardService rather than being recomputed here,
 * so a student's average on the school-wide report always matches their report
 * card. That duplication was the specific risk flagged when this plan was first
 * reviewed.
 */
class AnalyticsService
{
    public const CACHE_TAG = 'reports';

    /** Bumped to invalidate every cached report at once. */
    private const CACHE_VERSION_KEY = 'reports.version';

    public function __construct(private readonly ReportCardService $reportCards)
    {
    }

    // ── Cache ─────────────────────────────────────────────────────────────────

    /**
     * Cache keys carry a version number. Clearing a report means bumping the
     * version, which works on every cache driver — unlike tags, which the
     * database and file drivers do not support.
     */
    public function cacheKey(string $name, array $filters = []): string
    {
        $version = Cache::get(self::CACHE_VERSION_KEY, 1);

        return self::CACHE_TAG . ".v{$version}.{$name}." . md5(json_encode($filters));
    }

    public static function flush(): void
    {
        Cache::put(self::CACHE_VERSION_KEY, Cache::get(self::CACHE_VERSION_KEY, 1) + 1);
    }

    public function remember(string $name, array $filters, callable $builder, int $seconds = 3600)
    {
        return Cache::remember($this->cacheKey($name, $filters), $seconds, $builder);
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    /**
     * Attendance across a date range, optionally narrowed to one class, subject
     * or student.
     *
     * A student can have several subject registers on one day, so rows are
     * collapsed to one mark per calendar day: "days present" means days.
     */
    public function attendanceReport(array $filters): array
    {
        [$from, $to] = $this->resolveRange($filters);

        $records = Attendance::query()
            ->with(['student.schoolClass', 'classSubject.subject'])
            ->when($from && $to, fn ($q) => $q->whereBetween('date', [$from, $to]))
            ->when($filters['class_id'] ?? null, fn ($q, $id) => $q->whereHas('student', fn ($s) => $s->where('class_id', $id)))
            ->when($filters['class_subject_id'] ?? null, fn ($q, $id) => $q->where('class_subject_id', $id))
            ->when($filters['student_id'] ?? null, fn ($q, $id) => $q->where('student_id', $id))
            ->get();

        $perStudent = $records
            ->groupBy('student_id')
            ->map(function (Collection $rows) {
                $student = $rows->first()->student;
                $daily = $this->collapseToDays($rows);

                $recorded = array_sum($daily);

                return [
                    'student'  => $student,
                    'class'    => $student?->schoolClass?->class_name,
                    'present'  => $daily['present'],
                    'absent'   => $daily['absent'],
                    'late'     => $daily['late'],
                    'recorded' => $recorded,
                    'rate'     => $recorded > 0
                        ? round((($daily['present'] + $daily['late']) / $recorded) * 100, 1)
                        : null,
                ];
            })
            ->filter(fn ($row) => $row['student'] !== null)
            ->sortBy(fn ($row) => $row['rate'] ?? 101)
            ->values();

        $byClass = $perStudent
            ->groupBy('class')
            ->map(fn (Collection $rows, $class) => [
                'class'    => $class ?: 'Unassigned',
                'students' => $rows->count(),
                'present'  => $rows->sum('present'),
                'absent'   => $rows->sum('absent'),
                'late'     => $rows->sum('late'),
                'rate'     => $rows->whereNotNull('rate')->isNotEmpty()
                    ? round($rows->whereNotNull('rate')->avg('rate'), 1)
                    : null,
            ])
            ->sortBy('class')
            ->values();

        $bySubject = $records
            ->groupBy(fn (Attendance $a) => $a->classSubject?->subject?->subject_name ?? 'General')
            ->map(fn (Collection $rows, $subject) => [
                'subject'  => $subject,
                'records'  => $rows->count(),
                'present'  => $rows->where('status', 'Present')->count(),
                'absent'   => $rows->where('status', 'Absent')->count(),
                'late'     => $rows->where('status', 'Late')->count(),
                'rate'     => $rows->count() > 0
                    ? round((($rows->where('status', 'Present')->count() + $rows->where('status', 'Late')->count()) / $rows->count()) * 100, 1)
                    : null,
            ])
            ->sortByDesc('records')
            ->values();

        $term = isset($filters['term_id']) ? Term::find($filters['term_id']) : null;

        return [
            'from'        => $from,
            'to'          => $to,
            'term'        => $term,
            'school_days' => $term?->school_days,
            'per_student' => $perStudent,
            'by_class'    => $byClass,
            'by_subject'  => $bySubject,
            'summary'     => [
                'students' => $perStudent->count(),
                'present'  => $perStudent->sum('present'),
                'absent'   => $perStudent->sum('absent'),
                'late'     => $perStudent->sum('late'),
                'rate'     => $perStudent->whereNotNull('rate')->isNotEmpty()
                    ? round($perStudent->whereNotNull('rate')->avg('rate'), 1)
                    : null,
            ],
        ];
    }

    /** @return array{present: int, absent: int, late: int} */
    private function collapseToDays(Collection $rows): array
    {
        $tally = ['present' => 0, 'absent' => 0, 'late' => 0];

        foreach ($rows->groupBy(fn (Attendance $a) => $a->date?->format('Y-m-d')) as $day) {
            $statuses = $day->pluck('status');

            if ($statuses->contains('Late')) {
                $tally['late']++;
            } elseif ($statuses->contains('Present')) {
                $tally['present']++;
            } elseif ($statuses->contains('Absent')) {
                $tally['absent']++;
            }
        }

        return $tally;
    }

    // ── Fee aging ─────────────────────────────────────────────────────────────

    public const AGING_BUCKETS = ['Not yet due', '1–30 days', '31–60 days', '61–90 days', '90+ days'];

    /** Which bucket a fee falls into, by how long it has been past due. */
    public function agingBucket(?Carbon $dueDate, Carbon $asOf): string
    {
        if (! $dueDate || $dueDate->greaterThanOrEqualTo($asOf)) {
            return 'Not yet due';
        }

        $days = (int) $dueDate->diffInDays($asOf);

        return match (true) {
            $days <= 30 => '1–30 days',
            $days <= 60 => '31–60 days',
            $days <= 90 => '61–90 days',
            default     => '90+ days',
        };
    }

    public function agingReport(array $filters): array
    {
        $asOf = now()->startOfDay();

        $fees = Fee::query()
            ->with(['student.schoolClass'])
            ->whereRaw('(amount_due - amount_paid) > 0.009')
            ->when($filters['class_id'] ?? null, fn ($q, $id) => $q->whereHas('student', fn ($s) => $s->where('class_id', $id)))
            ->get();

        $rows = $fees->map(function (Fee $fee) use ($asOf) {
            $balance = round((float) $fee->amount_due - (float) $fee->amount_paid, 2);
            $days = $fee->due_date && $fee->due_date->lessThan($asOf)
                ? (int) $fee->due_date->diffInDays($asOf)
                : 0;

            return [
                'fee'         => $fee,
                'student'     => $fee->student,
                'class'       => $fee->student?->schoolClass?->class_name ?? 'Unassigned',
                'description' => $fee->description ?: 'School fees',
                'due_date'    => $fee->due_date,
                'days_late'   => $days,
                'balance'     => $balance,
                'bucket'      => $this->agingBucket($fee->due_date, $asOf),
            ];
        })->sortByDesc('days_late')->values();

        $buckets = collect(self::AGING_BUCKETS)->mapWithKeys(function (string $bucket) use ($rows) {
            $inBucket = $rows->where('bucket', $bucket);

            return [$bucket => [
                'count'   => $inBucket->count(),
                'balance' => round($inBucket->sum('balance'), 2),
            ]];
        });

        $byClass = $rows
            ->groupBy('class')
            ->map(fn (Collection $classRows, $class) => [
                'class'    => $class,
                'fees'     => $classRows->count(),
                'students' => $classRows->pluck('student.student_id')->filter()->unique()->count(),
                'balance'  => round($classRows->sum('balance'), 2),
                'worst'    => $classRows->max('days_late'),
            ])
            ->sortByDesc('balance')
            ->values();

        return [
            'as_of'   => $asOf,
            'rows'    => $rows,
            'buckets' => $buckets,
            'by_class'=> $byClass,
            'summary' => [
                'fees'     => $rows->count(),
                'students' => $rows->pluck('student.student_id')->filter()->unique()->count(),
                'balance'  => round($rows->sum('balance'), 2),
                'overdue'  => round($rows->where('bucket', '!=', 'Not yet due')->sum('balance'), 2),
            ],
        ];
    }

    // ── Performance ───────────────────────────────────────────────────────────

    /** Pass mark for "pass rate" figures. */
    public const PASS_MARK = 50.0;

    public function lowPerformerThreshold(): float
    {
        return (float) (SchoolSetting::get('report_low_threshold') ?? 40);
    }

    /**
     * Every enrolled student's term average, via ReportCardService so the
     * figure matches their report card exactly.
     *
     * @return Collection<int, array{student: Student, average: ?float}>
     */
    public function studentAverages(Term $term, ?int $classId = null): Collection
    {
        return Student::query()
            ->with(['schoolClass.gradeLevel'])
            ->where('status', Student::STATUS_ENROLLED)
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->get()
            ->map(fn (Student $student) => [
                'student' => $student,
                'average' => $this->reportCards->averageOf($this->reportCards->subjectRows($student, $term)),
            ]);
    }

    /** Pass rate over a set of averages, ignoring students with no marks. */
    public function passRate(Collection $averages): ?float
    {
        $scored = $averages->filter(fn ($a) => $a !== null);

        if ($scored->isEmpty()) {
            return null;
        }

        return round(($scored->filter(fn ($a) => $a >= self::PASS_MARK)->count() / $scored->count()) * 100, 1);
    }

    /**
     * Teacher class-performance: distribution, per-subject averages and a
     * term-by-term trend for one class.
     */
    public function classPerformance(SchoolClass $class, Term $term, Collection $allTerms): array
    {
        $rows = $this->studentAverages($term, $class->class_id);
        $averages = $rows->pluck('average');
        $scored = $averages->filter(fn ($a) => $a !== null);

        $bands = [
            '75–100' => fn ($a) => $a >= 75,
            '65–74'  => fn ($a) => $a >= 65 && $a < 75,
            '50–64'  => fn ($a) => $a >= 50 && $a < 65,
            '40–49'  => fn ($a) => $a >= 40 && $a < 50,
            'Under 40' => fn ($a) => $a < 40,
        ];

        $distribution = collect($bands)->map(fn ($test, $label) => $scored->filter($test)->count());

        // Per-subject averages for this class this term.
        $classSubjects = ClassSubject::with(['subject', 'teacher'])
            ->where('class_id', $class->class_id)
            ->get();

        $studentIds = $rows->pluck('student.student_id');

        $bySubject = $classSubjects->map(function (ClassSubject $cs) use ($term, $studentIds) {
            $grades = Grade::whereIn('student_id', $studentIds)
                ->where('class_subject_id', $cs->class_subject_id)
                ->inTerm($term)
                ->get()
                ->filter(fn (Grade $g) => (float) $g->max_score > 0);

            $percentages = $grades->map(fn (Grade $g) => $g->percentage);

            return [
                'subject'   => $cs->subject?->subject_name ?? 'Unassigned',
                'teacher'   => $cs->teacher?->full_name,
                'entries'   => $grades->count(),
                'average'   => $percentages->isNotEmpty() ? round($percentages->avg(), 1) : null,
                'pass_rate' => $percentages->isNotEmpty()
                    ? round(($percentages->filter(fn ($p) => $p >= self::PASS_MARK)->count() / $percentages->count()) * 100, 1)
                    : null,
            ];
        })->sortBy('subject')->values();

        // Trend: this class's average across every term that has marks.
        $trend = $allTerms
            ->sortBy('start_date')
            ->map(function (Term $t) use ($class) {
                $avgs = $this->studentAverages($t, $class->class_id)->pluck('average')->filter();

                return [
                    'term'    => $t->name,
                    'year'    => $t->academicYear?->label,
                    'average' => $avgs->isNotEmpty() ? round($avgs->avg(), 1) : null,
                ];
            })
            ->filter(fn ($row) => $row['average'] !== null)
            ->values();

        return [
            'class'        => $class,
            'term'         => $term,
            'students'     => $rows->sortByDesc('average')->values(),
            'distribution' => $distribution,
            'by_subject'   => $bySubject,
            'trend'        => $trend,
            'summary'      => [
                'students'  => $rows->count(),
                'marked'    => $scored->count(),
                'average'   => $scored->isNotEmpty() ? round($scored->avg(), 1) : null,
                'pass_rate' => $this->passRate($averages),
                'highest'   => $scored->isNotEmpty() ? round($scored->max(), 1) : null,
                'lowest'    => $scored->isNotEmpty() ? round($scored->min(), 1) : null,
            ],
        ];
    }

    /**
     * 9.x — school-wide performance, in the five sections the plan specifies.
     */
    public function schoolWideReport(Term $term): array
    {
        $rows = $this->studentAverages($term);
        $threshold = $this->lowPerformerThreshold();

        // ── Overview ─────────────────────────────────────────────────────────
        $overview = [
            'students'     => $rows->count(),
            'marked'       => $rows->whereNotNull('average')->count(),
            'grade_levels' => $rows->pluck('student.schoolClass.gradeLevel.grade_level_id')->filter()->unique()->count(),
            'classes'      => $rows->pluck('student.class_id')->filter()->unique()->count(),
            'average'      => $rows->pluck('average')->filter()->isNotEmpty()
                ? round($rows->pluck('average')->filter()->avg(), 1)
                : null,
            'pass_rate'    => $this->passRate($rows->pluck('average')),
        ];

        // ── By grade level ───────────────────────────────────────────────────
        $byGradeLevel = $rows
            ->groupBy(fn ($row) => $row['student']->schoolClass?->gradeLevel?->name ?? 'Unassigned')
            ->map(function (Collection $group, $name) {
                $scored = $group->filter(fn ($r) => $r['average'] !== null)->sortByDesc('average');

                return [
                    'grade_level' => $name,
                    'students'    => $group->count(),
                    'average'     => $scored->isNotEmpty() ? round($scored->avg('average'), 1) : null,
                    'pass_rate'   => $this->passRate($group->pluck('average')),
                    'top'         => $scored->first(),
                    'bottom'      => $scored->last(),
                ];
            })
            ->sortBy('grade_level')
            ->values();

        // ── By subject ───────────────────────────────────────────────────────
        $bySubject = ClassSubject::with(['subject', 'schoolClass'])
            ->get()
            ->groupBy(fn (ClassSubject $cs) => $cs->subject?->subject_name ?? 'Unassigned')
            ->map(function (Collection $classSubjects, $subject) use ($term) {
                $grades = Grade::whereIn('class_subject_id', $classSubjects->pluck('class_subject_id'))
                    ->inTerm($term)
                    ->with('classSubject.schoolClass')
                    ->get()
                    ->filter(fn (Grade $g) => (float) $g->max_score > 0);

                if ($grades->isEmpty()) {
                    return [
                        'subject' => $subject, 'students' => 0, 'average' => null,
                        'pass_rate' => null, 'top_class' => null, 'bottom_class' => null,
                    ];
                }

                $perClass = $grades
                    ->groupBy(fn (Grade $g) => $g->classSubject?->schoolClass?->class_name ?? '—')
                    ->map(fn (Collection $g) => round($g->avg(fn (Grade $x) => $x->percentage), 1))
                    ->sortDesc();

                $percentages = $grades->map(fn (Grade $g) => $g->percentage);

                return [
                    'subject'      => $subject,
                    'students'     => $grades->pluck('student_id')->unique()->count(),
                    'average'      => round($percentages->avg(), 1),
                    'pass_rate'    => round(($percentages->filter(fn ($p) => $p >= self::PASS_MARK)->count() / $percentages->count()) * 100, 1),
                    'top_class'    => $perClass->keys()->first(),
                    'top_score'    => $perClass->first(),
                    'bottom_class' => $perClass->keys()->last(),
                    'bottom_score' => $perClass->last(),
                ];
            })
            ->sortBy('subject')
            ->values();

        // ── Class summary, ranked ────────────────────────────────────────────
        $byClass = $rows
            ->groupBy(fn ($row) => $row['student']->class_id)
            ->map(function (Collection $group) {
                $class = $group->first()['student']->schoolClass;
                $scored = $group->filter(fn ($r) => $r['average'] !== null);

                return [
                    'class'     => $class?->class_name ?? 'Unassigned',
                    'teacher'   => $class?->teacher?->full_name,
                    'students'  => $group->count(),
                    'average'   => $scored->isNotEmpty() ? round($scored->avg('average'), 1) : null,
                    'pass_rate' => $this->passRate($group->pluck('average')),
                ];
            })
            ->sortByDesc(fn ($row) => $row['average'] ?? -1)
            ->values()
            ->map(fn ($row, $index) => $row + ['rank' => $row['average'] !== null ? $index + 1 : null]);

        // ── Low performers ───────────────────────────────────────────────────
        $lowPerformers = $rows
            ->filter(fn ($row) => $row['average'] !== null && $row['average'] < $threshold)
            ->sortBy('average')
            ->values();

        return [
            'term'           => $term,
            'threshold'      => $threshold,
            'pass_mark'      => self::PASS_MARK,
            'overview'       => $overview,
            'by_grade_level' => $byGradeLevel,
            'by_subject'     => $bySubject,
            'by_class'       => $byClass,
            'low_performers' => $lowPerformers,
            'generated_at'   => now(),
        ];
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /** @return array{0: ?Carbon, 1: ?Carbon} */
    private function resolveRange(array $filters): array
    {
        if (! empty($filters['from']) && ! empty($filters['to'])) {
            return [Carbon::parse($filters['from'])->startOfDay(), Carbon::parse($filters['to'])->endOfDay()];
        }

        if (! empty($filters['term_id']) && ($term = Term::find($filters['term_id']))) {
            return [$term->start_date?->copy()->startOfDay(), $term->end_date?->copy()->endOfDay()];
        }

        return [null, null];
    }
}
