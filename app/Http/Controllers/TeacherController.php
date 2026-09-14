<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Models\ReportCard;
use App\Models\AuditLog;
use App\Services\AnnouncementService;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeacherController extends Controller
{
    public function settings()
    {
        return view('teacher.settings', [
            'user' => auth()->user(),
            'teacher' => auth()->user()->teacher,
        ]);
    }

    public function roster(Request $request, int $class)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 404);

        $schoolClass = SchoolClass::with(['gradeLevel', 'classSubjects.subject'])
            ->where('class_id', $class)
            ->where(function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->teacher_id)
                    ->orWhereHas('classSubjects', fn ($subjects) => $subjects->where('teacher_id', $teacher->teacher_id));
            })->firstOrFail();

        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : Term::current();
        $term ??= $terms->first();

        $students = $schoolClass->students()
            ->with(['user', 'parentUser'])
            ->orderBy('last_name')->orderBy('first_name')->get();
        $classSubjectIds = $schoolClass->classSubjects->pluck('class_subject_id');
        $grades = $term ? Grade::inTerm($term)->whereIn('class_subject_id', $classSubjectIds)->get() : collect();
        $attendance = $term ? Attendance::whereIn('class_subject_id', $classSubjectIds)
            ->whereIn('student_id', $students->pluck('student_id'))
            ->whereBetween('date', [$term->start_date, $term->end_date])
            ->get()->groupBy('student_id') : collect();

        $rows = $students->map(function ($student) use ($grades, $attendance) {
            $studentGrades = $grades->where('student_id', $student->student_id);
            $scores = $studentGrades->map->percentage->filter(fn ($score) => $score !== null);
            $records = $attendance->get($student->student_id, collect())->where('status', '!=', 'Excused');
            $present = $records->whereIn('status', ['Present', 'Late'])->count();

            return [
                'student' => $student,
                'average' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
                'attendance' => $records->count() ? round(($present / $records->count()) * 100) : null,
                'status' => $student->user?->is_active === false ? 'Inactive' : 'Active',
            ];
        });

        return view('teacher.roster', compact('teacher', 'schoolClass', 'terms', 'term', 'rows'));
    }

    /**
     * Read-only student profile, reached from a teacher's class roster.
     *
     * Shares its content (resources/views/students/profile-content.blade.php)
     * with the admin student profile — same tabs, minus Financials, which
     * stays admin-only. Scoped to students in a class the teacher actually
     * teaches (homeroom or subject-assigned), same check as roster().
     */
    public function studentProfile(Student $student)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($teacher, 404);

        $schoolClass = $student->schoolClass;
        abort_unless($schoolClass, 404);

        $isAssigned = (int) $schoolClass->teacher_id === (int) $teacher->teacher_id
            || $schoolClass->classSubjects()->where('teacher_id', $teacher->teacher_id)->exists();

        abort_unless($isAssigned, 403);

        $student->load(
            'schoolClass',
            'grades.classSubject.subject',
            'grades.recordedByTeacher',
            'attendance.classSubject.subject',
            'attendance.recordedByTeacher',
            'user'
        );

        return view('teacher.student-profile', compact('student'));
    }

    public function performance(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : Term::current();
        $term ??= $terms->first();

        $assignments = $teacher
            ? ClassSubject::with(['schoolClass.gradeLevel', 'subject', 'teacher'])
                ->where('teacher_id', $teacher->teacher_id)
                ->get()
            : collect();
        $selected = $request->filled('assignment_id')
            ? $assignments->firstWhere('class_subject_id', (int) $request->assignment_id)
            : $assignments->first();

        if (! $selected || ! $term) {
            return view('teacher.performance', [
                'teacher' => $teacher, 'terms' => $terms, 'term' => $term,
                'assignments' => $assignments, 'selectedAssignment' => $selected,
                'subjects' => collect(), 'rankings' => collect(), 'distribution' => collect(),
                'kpis' => null, 'isFinalized' => false, 'canFinalize' => false,
            ]);
        }

        $class = $selected->schoolClass;
        $classSubjects = $assignments->where('class_id', $class->class_id)->values();
        $classSubjectIds = $classSubjects->pluck('class_subject_id');
        $students = Student::where('class_id', $class->class_id)
            ->orderBy('last_name')->orderBy('first_name')->get();
        $grades = Grade::inTerm($term)
            ->where('assessment_type', 'EXAM')
            ->whereIn('class_subject_id', $classSubjectIds)
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get()
            ->keyBy(fn ($grade) => $grade->student_id.'-'.$grade->class_subject_id);
        $caGrades = Grade::inTerm($term)
            ->where('assessment_type', 'CA')
            ->whereIn('class_subject_id', $classSubjectIds)
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get()
            ->keyBy(fn ($grade) => $grade->student_id.'-'.$grade->class_subject_id);

        $rankings = $students->map(function ($student) use ($classSubjects, $grades, $caGrades) {
            $scores = [];
            $deltas = [];
            foreach ($classSubjects as $subject) {
                $key = $student->student_id.'-'.$subject->class_subject_id;
                $exam = $grades->get($key);
                $ca = $caGrades->get($key);
                $scores[$subject->subject?->subject_name ?? 'Subject'] = $exam?->percentage;
                if ($exam && $ca) {
                    $deltas[] = $exam->percentage - $ca->percentage;
                }
            }
            $available = collect($scores)->filter(fn ($score) => $score !== null);
            $average = $available->isNotEmpty() ? round($available->avg(), 1) : null;

            return [
                'student' => $student,
                'scores' => $scores,
                'average' => $average,
                'letter' => $average === null ? null : $this->performanceLetter($average),
                'trend' => empty($deltas) ? 'flat' : ($deltas[0] >= 2 ? 'up' : ($deltas[0] <= -2 ? 'down' : 'flat')),
            ];
        })->sortByDesc(fn ($row) => $row['average'] ?? -1)->values();

        $rankings = $rankings->values()->map(function ($row, $index) {
            $row['rank'] = $row['average'] === null ? null : $index + 1;
            return $row;
        });
        $graded = $rankings->filter(fn ($row) => $row['average'] !== null);
        $averages = $graded->pluck('average');
        $distribution = collect(['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0]);
        foreach ($graded as $row) {
            $bucket = match ($row['letter']) {
                'A+', 'A' => 'A', 'B+', 'B' => 'B', 'C+', 'C' => 'C', 'D' => 'D', default => 'F',
            };
            $distribution->put($bucket, $distribution->get($bucket) + 1);
        }

        $subjects = $classSubjects->map(function ($subject) use ($students, $grades) {
            $scores = $students->map(fn ($student) => $grades->get($student->student_id.'-'.$subject->class_subject_id)?->percentage)->filter(fn ($score) => $score !== null);
            return ['name' => $subject->subject?->subject_name ?? 'Subject', 'average' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0];
        });
        $attendance = Attendance::whereIn('student_id', $students->pluck('student_id'))
            ->whereIn('class_subject_id', $classSubjectIds)
            ->whereBetween('date', [$term->start_date, $term->end_date])
            ->where('status', '!=', 'Excused')->get();
        $present = $attendance->whereIn('status', ['Present', 'Late'])->count();
        $attendanceRate = $attendance->count() > 0 ? round(($present / $attendance->count()) * 100, 1) : 0;

        return view('teacher.performance', [
            'teacher' => $teacher, 'terms' => $terms, 'term' => $term,
            'assignments' => $assignments, 'selectedAssignment' => $selected,
            'class' => $class, 'subjects' => $subjects, 'rankings' => $rankings,
            'distribution' => $distribution, 'attendanceRate' => $attendanceRate,
            'isFinalized' => app(ReportCardService::class)->isLocked($class->class_id, $term->term_id),
            'canFinalize' => (int) $class->teacher_id === (int) $teacher?->teacher_id,
            'kpis' => [
                'average' => $averages->isNotEmpty() ? round($averages->avg(), 1) : 0,
                'passed' => $graded->filter(fn ($row) => ($row['average'] ?? 0) >= 50)->count(),
                'graded' => $graded->count(),
                'top' => $graded->first(), 'lowest' => $graded->last(),
            ],
        ]);
    }

    public function finalizeGrades(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $assignment = ClassSubject::with('schoolClass')->where('class_subject_id', $request->integer('assignment_id'))->where('teacher_id', $teacher?->teacher_id)->firstOrFail();
        abort_unless((int) $assignment->schoolClass->teacher_id === (int) $teacher->teacher_id, 403);
        $term = Term::findOrFail($request->integer('term_id'));

        try {
            app(ReportCardService::class)->finalize($assignment->schoolClass, $term, $teacher);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('notification', "Grades finalized for {$assignment->schoolClass->class_name}, {$term->name}.");
    }

    public function unfinalizeRequest(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $validated = $request->validate([
            'assignment_id' => ['required', 'integer'],
            'term_id' => ['required', 'exists:terms,term_id'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        $assignment = ClassSubject::with('schoolClass')->where('class_subject_id', $validated['assignment_id'])->where('teacher_id', $teacher?->teacher_id)->firstOrFail();

        AuditLog::create([
            'user_id' => auth()->id(), 'auditable_type' => SchoolClass::class,
            'auditable_id' => $assignment->schoolClass->class_id, 'action' => 'unfinalize_requested',
            'old_values' => ['term_id' => (int) $validated['term_id']], 'new_values' => null,
            'reason' => $validated['reason'], 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(),
        ]);

        return back()->with('notification', 'The unfinalize request was recorded for administrator review. Grades remain locked.');
    }

    private function performanceLetter(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+', $percentage >= 80 => 'A', $percentage >= 75 => 'B+',
            $percentage >= 70 => 'B', $percentage >= 65 => 'C+', $percentage >= 60 => 'C',
            $percentage >= 50 => 'D', default => 'F',
        };
    }

    public function timetable(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : Term::current();
        $term ??= $terms->first();

        $days = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);

        $classes = collect();
        if ($teacher && $term) {
            $slots = TimetableSlot::with([
                'schoolClass.gradeLevel.periods',
                'subject',
                'period',
                'schoolClass.students',
            ])->where('teacher_id', $teacher->teacher_id)
                ->where('term_id', $term->term_id)
                ->get();

            $byClass = $slots->groupBy('school_class_id');
            foreach ($byClass as $classId => $classSlots) {
                $schoolClass = $classSlots->first()->schoolClass;
                if (! $schoolClass) continue;

                $periods = $schoolClass->gradeLevel?->periods
                    ?? $classSlots->pluck('period')->filter()->unique('id')->all();
                if (is_array($periods)) {
                    $periods = collect($periods);
                }

                $map = $classSlots->keyBy(fn ($s) => $s->day_of_week.'-'.$s->period_id);
                $map->each(function ($slot) { $slot->icon = $slot->subject ? $this->subjectIcon($slot->subject->subject_name) : 'school'; });
                $dayCounts = $days->mapWithKeys(fn ($d) => [
                    $d => $classSlots->where('day_of_week', $d)->count(),
                ]);

                $classes->push([
                    'class'     => $schoolClass,
                    'periods'   => $periods->sortBy('order')->values(),
                    'slots'     => $map,
                    'roster'    => $schoolClass->students->count(),
                    'dayCounts' => $dayCounts,
                ]);
            }
        }

        // Upcoming slot: the earliest day/period combination that is not in the past.
        $upNext = null;
        foreach ($classes as $card) {
            foreach ($card['slots'] as $slot) {
                if (! $slot->period || $slot->period->is_break) continue;
                $order = (int) ($slot->period->order ?? 0);
                $candidate = [
                    'slot' => $slot,
                    'class' => $card['class'],
                    'day' => $slot->day_of_week,
                    'order' => $order,
                ];
                if (! $upNext) {
                    $upNext = $candidate;
                }
            }
        }

        // Legend: subjects taught and their period-per-week counts.
        $legend = collect();
        $subjectCounts = [];
        foreach ($classes as $card) {
            foreach ($card['slots'] as $slot) {
                if (! $slot->subject || $slot->period?->is_break) continue;
                $key = $slot->subject->subject_id;
                $subjectCounts[$key] = ($subjectCounts[$key] ?? 0) + 1;
            }
        }
        foreach ($classes as $card) {
            foreach ($card['slots'] as $slot) {
                if (! $slot->subject || $slot->period?->is_break) continue;
                $legend->push([
                    'subject' => $slot->subject->subject_name,
                    'icon'    => $this->subjectIcon($slot->subject->subject_name),
                    'periods' => $subjectCounts[$slot->subject->subject_id] ?? 0,
                ]);
            }
        }
        $legend = $legend->unique('subject')->sortBy('subject')->values();

        // Workload / summary metrics.
        $taughtSlots = 0;
        $freeSlots = 0;
        $totalHours = 0.0;
        foreach ($classes as $card) {
            foreach ($card['slots'] as $slot) {
                $period = $slot->period;
                if (! $period || $period->is_break) continue;
                if ($slot->subject) {
                    $taughtSlots++;
                    if ($period->start_time && $period->end_time) {
                        $mins = (($period->end_time->format('H')*60) + $period->end_time->format('i'))
                            - (($period->start_time->format('H')*60) + $period->start_time->format('i'));
                        $totalHours += $mins / 60;
                    }
                } else {
                    $freeSlots++;
                }
            }
        }
        $rosteredClasses = $classes->count();
        $activeStudents = $classes->sum('roster');

        $metrics = [
            'hours'       => round($totalHours, 1),
            'hours_pct'   => $totalHours > 0 ? (int) round(($totalHours / max(40, $totalHours)) * 100) : 0,
            'classes'     => $rosteredClasses,
            'students'    => $activeStudents,
            'prep'        => $freeSlots,
            'taught'      => $taughtSlots,
        ];

        return view('teacher.timetable', compact(
            'teacher',
            'terms',
            'term',
            'classes',
            'days',
            'upNext',
            'legend',
            'metrics',
        ));
    }

    /**
     * Show the teacher portal dashboard (schedule, KPIs, tasks, events).
     */
    public function dashboard()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // The banner and most KPI tiles degrade gracefully if the teacher has
        // not been assigned any ClassSubject rows yet.
        $assignments = $teacher
            ? ClassSubject::with(['schoolClass.gradeLevel', 'subject', 'teacher'])
                ->where('teacher_id', $teacher->teacher_id)
                ->get()
            : collect();

        // Unique rostered classes (dedupe by class_id to avoid double counting
        // a class that appears in several ClassSubject rows).
        $rosteredClasses = $assignments
            ->pluck('schoolClass')
            ->filter()
            ->unique('class_id')
            ->values();

        // Per-class student headcount and grand total.
        $classSizes = $rosteredClasses->mapWithKeys(function ($class) {
            return [$class->class_id => $class->students()->count()];
        });
        $totalStudents = array_sum($classSizes->all());

        // Resolve the active term (or fall back to the first seeded term).
        $term = Term::current() ?: Term::first();
        $termName = $term?->name ?? 'Term 1';
        $year = now()->year;
        $currentTerm = $term;

        $classSubjectIds = $assignments->pluck('class_subject_id');
        $studentIds = Student::whereIn('class_id', $rosteredClasses->pluck('class_id'))
            ->pluck('student_id');

        // Pending marks = enrolled students without an EXAM grade this term/year.
        $gradedCount = Grade::whereIn('class_subject_id', $classSubjectIds)
            ->whereIn('student_id', $studentIds)
            ->where('assessment_type', 'EXAM')
            ->where('term', $termName)
            ->where('academic_year', $year)
            ->distinct('student_id')
            ->count('student_id');
        $pendingMarks = max($studentIds->count() - $gradedCount, 0);

        // Attendance for today across the teacher's classes.
        $attendanceRecords = Attendance::whereIn('student_id', $studentIds)
            ->whereIn('class_subject_id', $classSubjectIds)
            ->where('date', now()->toDateString())
            ->get();
        $attendanceTotal   = $attendanceRecords->count();
        $attendancePresent = $attendanceRecords->whereIn('status', ['Present', 'Late'])->count();
        $attendanceRate    = $attendanceTotal > 0
            ? round(($attendancePresent / $attendanceTotal) * 100)
            : 0;

        // Greeting uses the teacher's first name, falling back to the login name.
        $greetingName = $teacher?->first_name ?? $user->name;

        // Recent grade-entry activity recorded by this teacher.
        $recentActivity = $this->recentActivity($teacher);

        // Pending instructional tasks derived from live ClassSubject data.
        $pendingTasks = $this->pendingTasks($assignments);

        // Upcoming school events (holidays from the calendar, or a fallback).
        $upcomingEvents = $this->upcomingEvents();

        return view('teacher.dashboard', compact(
            'teacher',
            'currentTerm',
            'greetingName',
            'assignments',
            'rosteredClasses',
            'classSizes',
            'totalStudents',
            'pendingMarks',
            'attendanceRate',
            'attendancePresent',
            'attendanceTotal',
            'recentActivity',
            'pendingTasks',
            'upcomingEvents',
        ));
    }

    /**
     * My Classes — the teacher's rostered sections with quick stats and actions.
     */
    public function classes()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return view('teacher.classes', [
                'classes' => collect(),
                'totals'  => ['students' => 0, 'mean' => 0, 'attendance' => 0, 'facilities' => 0],
            ]);
        }

        // Subject assignments vs. homeroom classes. A class is shown once; the
        // homeroom flag wins for the visual accent, and the first rostered
        // subject provides the subject badge/icon.
        $assignments = ClassSubject::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->teacher_id)
            ->get();

        $homeroomIds = $teacher->homeroomClasses()->pluck('school_classes.class_id');

        // Group assignments by class for stable, deduplicated output.
        $byClass = $assignments->filter(fn ($a) => $a->schoolClass)
            ->groupBy(fn ($a) => $a->class_id);

        $classes = collect();

        foreach ($byClass as $classId => $group) {
            $schoolClass = $group->first()->schoolClass;
            if (! $schoolClass) {
                continue;
            }
            $classes->push($this->classCard($schoolClass, $group, $teacher, $homeroomIds));
        }

        // Any homeroom class with no explicit subject assignment is still listed.
        $missingHomeroom = $homeroomIds->diff($classes->pluck('class_id'));
        foreach ($missingHomeroom as $classId) {
            $schoolClass = SchoolClass::find($classId);
            if ($schoolClass) {
                $classes->push($this->classCard($schoolClass, collect(), $teacher, $homeroomIds));
            }
        }

        $classes = $classes->sortBy('name')->values();

        // KPI strip totals.
        $totals = [
            'students'   => $classes->sum('roster'),
            'mean'       => round((float) $classes->avg('avg_percent'), 1),
            'attendance' => round((float) $classes->avg('attendance_pct'), 1),
            'facilities' => $classes->count(),
        ];

        // Term context for the selector and editorial note.
        $currentTerm = Term::current() ?: Term::first();
        $terms = $currentTerm
            ? Term::where('academic_year_id', $currentTerm->academic_year_id)->orderBy('start_date')->get()
            : collect();

        return view('teacher.classes', compact('classes', 'totals', 'currentTerm', 'terms', 'teacher'));
    }

    /**
     * Build a single class card's presentation data.
     */
    private function classCard($schoolClass, $group, $teacher, $homeroomIds): array
    {
        $isHomeroom = $homeroomIds->contains($schoolClass->class_id);

        $primary = $group->first();
        $subject = $primary?->subject;
        $subjectName = $subject?->subject_name ?? 'Homeroom';

        // Class-subject id(s) used to scope grades / attendance.
        $classSubjectIds = $group->pluck('class_subject_id');

        $roster = $schoolClass->students()->count();

        // Class-average %.
        $avgPercent = 0;
        if ($classSubjectIds->isNotEmpty()) {
            $avgPercent = (float) Grade::whereIn('class_subject_id', $classSubjectIds)
                ->where('assessment_type', 'EXAM')
                ->avg('score');
        }

        // Attendance % across the class.
        $attTotal = 0;
        $attPresent = 0;
        if ($classSubjectIds->isNotEmpty()) {
            $attendance = Attendance::whereIn('class_subject_id', $classSubjectIds);
            $attTotal = (clone $attendance)->count();
            $attPresent = (clone $attendance)->whereIn('status', ['Present', 'Late'])->count();
        }
        $attendancePct = $attTotal > 0 ? round(($attPresent / $attTotal) * 100) : 0;

        return [
            'class_id'      => $schoolClass->class_id,
            'name'          => $schoolClass->display_name,
            'grade_level'   => $schoolClass->grade_level_name,
            'grade'         => (string) preg_replace('/\D+/', '', (string) $schoolClass->grade_level_name),
            'section'       => 'SEC-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $schoolClass->class_name)),
            'subject'       => $subjectName,
            'subject_icon'  => $this->subjectIcon($subjectName),
            'is_homeroom'   => $isHomeroom,
            'roster'        => $roster,
            'avg_percent'   => round($avgPercent),
            'avg_grade'     => $this->letterGrade((float) $avgPercent),
            'attendance_pct'=> $attendancePct,
            'marks_url'     => $primary ? route('teacher.marks', ['assignment_id' => $primary->class_subject_id]) : route('teacher.marks'),
        ];
    }

    /**
     * Map a subject name to a Material Symbols icon.
     */
    private function subjectIcon(string $name): string
    {
        $name = strtolower($name);
        return match (true) {
            str_contains($name, 'physics')   => 'science',
            str_contains($name, 'chem')      => 'biotech',
            str_contains($name, 'bio')       => 'biotech',
            str_contains($name, 'math')      => 'calculate',
            str_contains($name, 'english'),
            str_contains($name, 'literature') => 'menu_book',
            str_contains($name, 'hist')      => 'history_edu',
            str_contains($name, 'geo')       => 'map',
            str_contains($name, 'comput')    => 'computer',
            str_contains($name, 'sport'),
            str_contains($name, 'physical')  => 'sports_soccer',
            str_contains($name, 'art')       => 'palette',
            str_contains($name, 'music')     => 'music_note',
            default                          => 'school',
        };
    }

    /**
     * Derive a letter grade from a percentage (mirrors Grade::letter_grade).
     */
    private function letterGrade(float $pct): string
    {
        return match (true) {
            $pct >= 90 => 'A+',
            $pct >= 80 => 'A',
            $pct >= 75 => 'B+',
            $pct >= 70 => 'B',
            $pct >= 65 => 'C+',
            $pct >= 60 => 'C',
            $pct >= 50 => 'D',
            default    => 'F',
        };
    }

    /**
     * Build the recent activity feed from grade records entered by the teacher.
     */
    private function recentActivity($teacher): \Illuminate\Support\Collection
    {
        if (!$teacher) {
            return collect();
        }

        $recentGrades = Grade::with(['student.user'])
            ->where('recorded_by', $teacher->teacher_id)
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        $avatarColors = ['bg-primary/10 text-primary', 'bg-secondary-fixed text-secondary', 'bg-error-container text-error', 'bg-surface-container-high text-on-surface'];
        $iconColors   = ['text-secondary', 'text-primary', 'text-error', 'text-on-surface-variant'];

        return $recentGrades->map(function ($grade, $i) use ($avatarColors, $iconColors) {
            $student = $grade->student;
            $initials = $student
                ? strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1))
                : '??';
            return [
                'initials'   => $initials,
                'avatarBg'   => $avatarColors[$i % count($avatarColors)],
                'avatarText' => $i % 2 === 0 ? 'text-on-primary' : 'text-on-secondary',
                'student'    => $student?->full_name ?? 'Student',
                'message'    => 'score updated with ' . $grade->score . '% for ' . $grade->assessment_type,
                'meta'       => optional($grade->created_at)->diffForHumans() ?? 'Recently',
                'icon'       => 'school',
                'iconColor'  => $iconColors[$i % count($iconColors)],
            ];
        });
    }

    /**
     * Build pending instructional tasks from the teacher's rostered classes.
     */
    private function pendingTasks($assignments): \Illuminate\Support\Collection
    {
        if ($assignments->isEmpty()) {
            return collect();
        }

        return $assignments->values()->take(4)->map(function ($asn, $i) {
            $marked = Grade::where('class_subject_id', $asn->class_subject_id)
                ->where('assessment_type', 'EXAM')
                ->exists();
            return [
                'title'      => $asn->subject?->subject_name ?? 'Subject',
                'subtitle'   => ($asn->schoolClass?->display_name ?? 'Class') . ' · ' . now()->format('l'),
                'href'       => route('teacher.marks'),
                'badge'      => $marked ? 'Review' : 'Enter marks',
                'badgeClass' => $i % 2 === 0
                    ? 'bg-error-container text-error'
                    : 'bg-secondary-fixed text-on-secondary-fixed',
            ];
        });
    }

    /**
     * Build a small list of upcoming school events for the current month.
     */
    private function upcomingEvents(): array
    {
        $today = now()->startOfDay();

        // Prefer the academic calendar (holidays) when data exists.
        $holidays = \App\Models\Holiday::where('date', '>=', $today)
            ->orderBy('date')
            ->limit(5)
            ->get();

        if ($holidays->isNotEmpty()) {
            return $holidays->map(function ($h) {
                $colors = [
                    'bg-secondary-fixed text-secondary',
                    'bg-error-container text-error',
                    'bg-primary/10 text-primary',
                    'bg-surface-container-high text-on-surface',
                ];
                $i = 0;
                return [
                    'month'      => $h->date->format('M'),
                    'day'        => $h->date->format('j'),
                    'title'      => $h->name ?? 'School holiday',
                    'tag'        => 'Holiday',
                    'detail'     => $h->date->format('l'),
                    'monthColor' => $colors[$i % count($colors)],
                    'iconColor'  => 'text-secondary',
                    'icon'       => 'event',
                ];
            })->all();
        }

        // Fallback placeholder events so the panel never looks empty.
        $fallback = now();
        return [
            [
                'month'      => $fallback->format('M'),
                'day'        => $fallback->day,
                'title'      => 'Staff Meeting',
                'tag'        => 'Meeting',
                'detail'     => $fallback->format('l') . ' · Faculty Room',
                'monthColor' => 'bg-secondary-fixed text-secondary',
                'iconColor'  => 'text-secondary',
                'icon'       => 'meeting_room',
            ],
            [
                'month'      => $fallback->addDay()->format('M'),
                'day'        => $fallback->day,
                'title'      => 'Grade Entry Due',
                'tag'        => 'Deadline',
                'detail'     => $fallback->format('l') . ' · Submit Term marks',
                'monthColor' => 'bg-error-container text-error',
                'iconColor'  => 'text-error',
                'icon'       => 'assignment_late',
            ],
        ];
    }

    /**
     * Show mark entry form for teacher
     */
    public function marks(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return view('teacher.marks', [
                'assignments' => collect(), 'terms' => collect(), 'term' => null,
                'assignment' => null, 'students' => collect(), 'isLocked' => false,
            ])->with('notification', 'Teacher profile not found.');
        }

        // Get teacher's assignments with their classes and subjects
        $assignments = ClassSubject::with(['schoolClass', 'subject', 'teacher'])
            ->where('teacher_id', $teacher->teacher_id)
            ->get();

        // Term selector — same calendar (Phase 3) convention as Class Performance
        // and the roster/timetable pages, so "Enter Marks" and "Class Performance"
        // are always looking at the same term.
        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : Term::current();
        $term ??= $terms->first();

        if ($assignments->isEmpty()) {
            return view('teacher.marks', [
                'assignments' => $assignments, 'terms' => $terms, 'term' => $term,
                'assignment' => null, 'students' => collect(), 'isLocked' => false,
            ])->with('notification', 'No assignments found for this teacher.');
        }

        // Get selected assignment or use first one
        $selectedAssignmentId = $request->query('assignment_id');
        $assignment = $selectedAssignmentId
            ? $assignments->firstWhere('class_subject_id', $selectedAssignmentId)
            : $assignments->first();

        if (!$assignment) {
            $assignment = $assignments->first();
        }

        // Same (term-name, academic-year) pair storeMarks() writes with — matches
        // whether or not a Phase 3 calendar Term is set up yet, so what's shown
        // here is always exactly what a save would update.
        $termLabel = $term?->name ?? 'Term 1';
        $yearLabel = (int) ($term?->academicYear?->label ?? now()->year);

        $studentModels = Student::where('class_id', $assignment->schoolClass->class_id)
            ->with('user')
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        $grades = Grade::where('class_subject_id', $assignment->class_subject_id)
            ->where('assessment_type', 'EXAM')
            ->where('term', $termLabel)
            ->where('academic_year', $yearLabel)
            ->whereIn('student_id', $studentModels->pluck('student_id'))
            ->get()
            ->keyBy('student_id');

        $students = $studentModels->values()->map(function ($student, $i) use ($grades) {
            $grade = $grades->get($student->student_id);
            $mark = $grade?->score !== null ? (float) $grade->score : null;

            return [
                'id' => $student->student_id,
                'index' => $i + 1,
                'name' => $student->full_name,
                'initials' => mb_strtoupper(mb_substr($student->first_name ?? ' ', 0, 1).mb_substr($student->last_name ?? ' ', 0, 1)),
                'mark' => $mark,
                'letter' => $mark === null ? null : $this->performanceLetter($mark),
            ];
        });

        $isLocked = $term
            ? app(ReportCardService::class)->isLocked((int) $assignment->schoolClass->class_id, (int) $term->term_id)
            : false;

        return view('teacher.marks', compact(
            'assignments',
            'terms',
            'term',
            'assignment',
            'students',
            'isLocked'
        ));
    }

    /**
     * Teacher attendance roster (Record Attendance page)
     */
    public function attendance(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $assignments = collect();
        if ($teacher) {
            $assignments = ClassSubject::with(['schoolClass', 'subject'])
                ->where('teacher_id', $teacher->teacher_id)
                ->orderBy('class_subject_id')
                ->get();
        }

        if ($assignments->isEmpty()) {
            return view('teacher.attendance', ['assignments' => collect(), 'students' => collect()])
                ->with('notification', 'No class assignments found for this teacher.');
        }

        $assignment = $request->filled('assignment_id')
            ? $assignments->firstWhere('class_subject_id', (int) $request->query('assignment_id'))
            : $assignments->first();
        $assignment = $assignment ?: $assignments->first();

        $rosterDate = $request->query('date') ?: now()->toDateString();

        $statusMap = ['Present' => 'P', 'Absent' => 'A', 'Late' => 'L', 'Excused' => 'E'];

        $students = Student::where('class_id', $assignment->schoolClass->class_id)
            ->with('user')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->values()
            ->map(function ($student, $i) use ($assignment, $rosterDate, $statusMap) {
                $attendance = Attendance::where('student_id', $student->student_id)
                    ->where('class_subject_id', $assignment->class_subject_id)
                    ->where('date', $rosterDate)
                    ->first();

                return [
                    'id'        => $student->student_id,
                    'name'      => $student->full_name,
                    'initials'  => mb_strtoupper(mb_substr($student->first_name ?? ' ', 0, 1).mb_substr($student->last_name ?? ' ', 0, 1)),
                    'admission' => $student->student_number ?? ('ID: '.$student->student_id),
                    'status'    => $statusMap[$attendance?->status] ?? 'P',
                    'remarks'   => $attendance?->remarks ?? '',
                    'index'     => $i + 1,
                ];
            });

        return view('teacher.attendance', compact('assignments', 'assignment', 'rosterDate', 'students'));
    }

    /**
     * Persist the attendance roster for a class + date
     */
    public function storeAttendance(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return back()->withErrors('Teacher profile not found.');
        }

        $assignment = ClassSubject::where('class_subject_id', $request->input('assignment_id'))
            ->where('teacher_id', $teacher->teacher_id)
            ->first();

        if (!$assignment) {
            return back()->withErrors('Unauthorized assignment.');
        }

        $statuses  = $request->input('status', []);
        $remarks   = $request->input('remarks', []);
        $date      = $request->input('date') ?: now()->toDateString();

        $statusMap = ['P' => 'Present', 'A' => 'Absent', 'L' => 'Late', 'E' => 'Excused'];

        try {
            $saved = 0;
            foreach ($statuses as $studentId => $short) {
                if (!isset($statusMap[$short])) {
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'student_id'       => $studentId,
                        'class_subject_id' => $assignment->class_subject_id,
                        'date'             => $date,
                    ],
                    [
                        'status'      => $statusMap[$short],
                        'remarks'     => $remarks[$studentId] ?? null,
                        'recorded_by' => $teacher->teacher_id,
                    ]
                );
                $saved++;
            }

            return back()->with('notification', "Attendance saved for {$saved} students ({$date}).");
        } catch (\Exception $e) {
            return back()->withErrors('Failed to save attendance: '.$e->getMessage());
        }
    }

    /**
     * Store marks and attendance records
     */
    public function storeMarks(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->withErrors('Teacher profile not found.');
        }

        $assignmentId = $request->input('assignment_id');

        $assignment = ClassSubject::where('class_subject_id', $assignmentId)
            ->where('teacher_id', $teacher->teacher_id)
            ->first();

        if (!$assignment) {
            return back()->withErrors('Unauthorized assignment.');
        }

        // Phase 11 — finalized terms are locked. An admin can unfinalize if a
        // correction is genuinely needed.
        $currentTerm = $request->filled('term_id')
            ? \App\Models\Term::find($request->integer('term_id'))
            : (\App\Models\Term::current() ?? \App\Models\Term::first());

        if ($currentTerm && app(\App\Services\ReportCardService::class)
                    ->isLocked((int) $assignment->schoolClass->class_id, (int) $currentTerm->term_id)) {
            return back()->withErrors(
                'Grades for this class are finalized for ' . $currentTerm->name .
                ' and can no longer be edited. Ask an administrator to unfinalize first.'
            );
        }

        $marks = $request->input('marks', []);
        $attendance = $request->input('attendance', []);

        $statusMap = [
            'P' => 'Present',
            'A' => 'Absent',
            'L' => 'Late',
        ];

        try {
            foreach ($marks as $studentId => $mark) {
                // Validate mark
                $mark = (int) $mark;
                if ($mark < 0 || $mark > 100) {
                    continue;
                }

                // Create or update grade record
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_subject_id' => $assignmentId,
                        'assessment_type' => 'EXAM',
                        'term' => $currentTerm?->name ?? 'Term 1',
                        'academic_year' => (int) ($currentTerm?->academicYear?->label ?? now()->year),
                    ],
                    [
                        'score' => $mark,
                        'max_score' => 100.00,
                        'recorded_by' => $teacher->teacher_id,
                        // Populate the calendar FK so report cards and term
                        // reporting can filter without the legacy string match.
                        'term_id' => $currentTerm?->term_id,
                        'academic_year_id' => $currentTerm?->academic_year_id,
                    ]
                );
            }

            foreach ($attendance as $studentId => $status) {
                // Validate attendance status
                if (!isset($statusMap[$status])) {
                    continue;
                }
                $dbStatus = $statusMap[$status];

                // Create or update attendance record
                Attendance::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'class_subject_id' => $assignmentId,
                        'date' => now()->toDateString(),
                    ],
                    [
                        'status' => $dbStatus,
                        'recorded_by' => $teacher->teacher_id,
                    ]
                );
            }

            return back()->with('notification', 'Records saved successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to save records: ' . $e->getMessage());
        }
    }

    /**
     * Phase 5 — teacher-side announcements.
     *
     * Teachers have no authoring route (that stays admin-only); this is the
     * same read/mark-read feed the parent and student portals use, scoped by
     * Announcement::visibleTo() to school-wide notices only, since a teacher
     * doesn't sit in any single class/grade-level audience.
     */
    public function announcements()
    {
        $service = app(AnnouncementService::class);

        return view('teacher.announcements', [
            'announcements' => $service->feedFor(auth()->user()),
            'unreadCount'   => $service->unreadCount(auth()->user()),
        ]);
    }

    public function readAnnouncement(Announcement $announcement)
    {
        $visible = Announcement::visibleTo(auth()->user())
            ->where('announcements.announcement_id', $announcement->announcement_id)
            ->firstOrFail();

        app(AnnouncementService::class)->markRead($visible, auth()->user());

        return back();
    }

    public function readAllAnnouncements()
    {
        $count = app(AnnouncementService::class)->markAllRead(auth()->user());

        return back()->with('notification', $count > 0
            ? "Marked {$count} announcement(s) as read."
            : 'Nothing new to mark.');
    }
}