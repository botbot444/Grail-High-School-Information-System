<?php

namespace App\Http\Controllers;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Term;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function timetable(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $term = $request->filled('term_id')
            ? $terms->firstWhere('term_id', (int) $request->term_id)
            : Term::current();
        $term ??= $terms->first();

        $classes = $teacher && $term
            ? TimetableSlot::with([
                'schoolClass.gradeLevel.periods',
                'subject',
                'period',
            ])->where('teacher_id', $teacher->teacher_id)
                ->where('term_id', $term->term_id)
                ->get()
                ->groupBy('school_class_id')
            : collect();

        return view('teacher.timetable', compact('teacher', 'terms', 'term', 'classes'));
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
            return view('teacher.marks', ['assignments' => collect()])
                ->with('notification', 'Teacher profile not found.');
        }
        
        // Get teacher's assignments with their classes and subjects
        $assignments = ClassSubject::with(['schoolClass', 'subject', 'teacher'])
            ->where('teacher_id', $teacher->teacher_id)
            ->get();

        if ($assignments->isEmpty()) {
            return view('teacher.marks', compact('assignments'))
                ->with('notification', 'No assignments found for this teacher.');
        }

        // Get selected assignment or use first one
        $selectedAssignmentId = $request->query('assignment_id');
        $assignment = $selectedAssignmentId 
            ? $assignments->firstWhere('class_subject_id', $selectedAssignmentId)
            : $assignments->first();

        if (!$assignment) {
            $assignment = $assignments->first();
        }

        // Get students in the selected class
        $students = Student::where('class_id', $assignment->schoolClass->class_id)
            ->with('user')
            ->get()
            ->map(function ($student) use ($assignment) {
                // Get current grade/mark for this student in this subject (Term 1, Exam)
                $grade = Grade::where('student_id', $student->student_id)
                    ->where('class_subject_id', $assignment->class_subject_id)
                    ->where('assessment_type', 'EXAM')
                    ->where('term', 'Term 1')
                    ->where('academic_year', now()->year)
                    ->first();

                // Get current attendance for this student
                $attendance = Attendance::where('student_id', $student->student_id)
                    ->where('class_subject_id', $assignment->class_subject_id)
                    ->where('date', now()->toDateString())
                    ->first();

                $statusMap = [
                    'Present' => 'P',
                    'Absent'  => 'A',
                    'Late'    => 'L',
                ];

                return [
                    'id' => $student->student_id,
                    'name' => $student->full_name,
                    'mark' => $grade?->score ?? 0,
                    'attendance' => $statusMap[$attendance?->status] ?? 'P',
                ];
            });

        return view('teacher.marks', compact(
            'assignments',
            'assignment',
            'students'
        ));
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
                        'term' => 'Term 1',
                        'academic_year' => now()->year,
                    ],
                    [
                        'score' => $mark,
                        'max_score' => 100.00,
                        'recorded_by' => $teacher->teacher_id,
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
}