<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function run(): void
    {
        $term = Term::current() ?? Term::orderBy('start_date')->first();
        if (! $term) {
            return;
        }

        $teachers = Teacher::pluck('teacher_id')->values();
        $teacherCursor = 0;

        foreach (GradeLevel::orderBy('order')->get() as $gradeLevel) {
            $periods = $this->periodsFor($gradeLevel->grade_level_id, $gradeLevel->order);
            $classes = SchoolClass::where('grade_level_id', $gradeLevel->grade_level_id)->get();

            foreach ($classes as $class) {
                $subjects = $class->classSubjects()->with('subject')->get()->values();
                if ($subjects->isEmpty()) {
                    continue;
                }

                foreach (self::DAYS as $day) {
                    foreach ($periods->where('is_break', false)->values() as $periodIndex => $period) {
                        $assignment = $subjects->get($periodIndex % $subjects->count());
                        $teacherId = $assignment->teacher_id;
                        $conflict = TimetableSlot::where('term_id', $term->term_id)
                            ->where('day_of_week', $day)
                            ->where('period_id', $period->id)
                            ->where('teacher_id', $teacherId)
                            ->exists();

                        if ($conflict && $teachers->isNotEmpty()) {
                            $teacherId = $teachers[$teacherCursor++ % $teachers->count()];
                        }

                        TimetableSlot::firstOrCreate([
                            'school_class_id' => $class->class_id,
                            'day_of_week' => $day,
                            'period_id' => $period->id,
                            'term_id' => $term->term_id,
                        ], [
                            'subject_id' => $assignment->subject_id,
                            'teacher_id' => $teacherId,
                        ]);
                    }
                }
            }
        }

        $this->command?->info('Timetable periods and weekly slots seeded.');
    }

    private function periodsFor(int $gradeLevelId, int $gradeOrder)
    {
        $base = 7 * 60;
        $definitions = [
            ['name' => 'Period 1', 'minutes' => 45, 'break' => false],
            ['name' => 'Period 2', 'minutes' => 45, 'break' => false],
            ['name' => 'Break', 'minutes' => 20, 'break' => true],
            ['name' => 'Period 3', 'minutes' => 50, 'break' => false],
            ['name' => 'Period 4', 'minutes' => 50, 'break' => false],
        ];
        if ($gradeOrder >= 11) {
            $definitions[] = ['name' => 'Period 5', 'minutes' => 50, 'break' => false];
        }

        $cursor = $base;
        foreach ($definitions as $order => $definition) {
            Period::firstOrCreate(
                ['grade_level_id' => $gradeLevelId, 'order' => $order + 1],
                [
                    'name' => $definition['name'],
                    'start_time' => sprintf('%02d:%02d', intdiv($cursor, 60), $cursor % 60),
                    'end_time' => sprintf('%02d:%02d', intdiv($cursor + $definition['minutes'], 60), ($cursor + $definition['minutes']) % 60),
                    'is_break' => $definition['break'],
                ]
            );
            $cursor += $definition['minutes'] + 10;
        }

        return Period::where('grade_level_id', $gradeLevelId)->orderBy('order')->get();
    }
}