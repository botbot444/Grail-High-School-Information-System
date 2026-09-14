<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Models\TimetableSlot;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    /**
     * Weekly timetable for every class.
     *
     * TimetableSlot refuses to save a teacher who is already booked for that
     * term, day and period, so the seeder has to schedule around that rather
     * than discover it. The previous version picked a subject by position and,
     * on a clash, grabbed a replacement teacher from the staff list without
     * re-checking — which threw the moment two classes in the same grade wanted
     * the same subject in the same slot. With one teacher per subject that is
     * no longer an occasional collision, it is every single slot.
     *
     * So: for each slot, walk the class's subjects from a rotating start and
     * take the first whose teacher is free. Streams within a grade share their
     * period rows, so the rotation is offset per class to keep 8A and 8B out of
     * each other's way, and offset per day so the week is not five identical
     * days. A slot with no free teacher is left empty — a free period, which is
     * honest — instead of failing the seed.
     */
    public function run(): void
    {
        $term = Term::current() ?? Term::orderBy('start_date')->first();

        if (! $term) {
            $this->command?->warn('✖ No term to hang a timetable on — skipped.');

            return;
        }

        $slots = 0;
        $free  = 0;

        foreach (GradeLevel::orderBy('order')->get() as $gradeLevel) {
            $periods = $this->periodsFor($gradeLevel->grade_level_id, $gradeLevel->order)
                ->where('is_break', false)
                ->values();

            if ($periods->isEmpty()) {
                continue;
            }

            $classes = SchoolClass::where('grade_level_id', $gradeLevel->grade_level_id)->get()->values();

            foreach ($classes as $classIndex => $class) {
                $subjects = $class->classSubjects()->with('subject')->get()->values();

                if ($subjects->isEmpty()) {
                    continue;
                }

                foreach (self::DAYS as $dayIndex => $day) {
                    foreach ($periods as $periodIndex => $period) {
                        $start = ($dayIndex * $periods->count()) + $periodIndex + $classIndex;
                        $assignment = $this->firstFreeSubject($subjects, $start, $term->term_id, $day, $period->id);

                        TimetableSlot::firstOrCreate(
                            [
                                'school_class_id' => $class->class_id,
                                'day_of_week'     => $day,
                                'period_id'       => $period->id,
                                'term_id'         => $term->term_id,
                            ],
                            [
                                'subject_id' => $assignment?->subject_id,
                                'teacher_id' => $assignment?->teacher_id,
                            ]
                        );

                        $assignment ? $slots++ : $free++;
                    }
                }
            }
        }

        $message = "✔ {$slots} timetable slots seeded.";

        if ($free > 0) {
            $message .= " {$free} left as free periods — every teacher for those subjects was already booked.";
        }

        $this->command?->info($message);
    }

    /**
     * The first subject, walking from $start, whose teacher is not already
     * booked for this term, day and period.
     */
    private function firstFreeSubject($subjects, int $start, int $termId, string $day, int $periodId)
    {
        $count = $subjects->count();

        for ($offset = 0; $offset < $count; $offset++) {
            $candidate = $subjects[($start + $offset) % $count];

            if (! $candidate->teacher_id) {
                continue;
            }

            $booked = TimetableSlot::where('term_id', $termId)
                ->where('day_of_week', $day)
                ->where('period_id', $periodId)
                ->where('teacher_id', $candidate->teacher_id)
                ->exists();

            if (! $booked) {
                return $candidate;
            }
        }

        return null;
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
                    'name'       => $definition['name'],
                    'start_time' => sprintf('%02d:%02d', intdiv($cursor, 60), $cursor % 60),
                    'end_time'   => sprintf('%02d:%02d', intdiv($cursor + $definition['minutes'], 60), ($cursor + $definition['minutes']) % 60),
                    'is_break'   => $definition['break'],
                ]
            );

            $cursor += $definition['minutes'] + 10;
        }

        return Period::where('grade_level_id', $gradeLevelId)->orderBy('order')->get();
    }
}
