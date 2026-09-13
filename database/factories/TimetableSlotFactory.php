<?php

namespace Database\Factories;

use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\TimetableSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimetableSlotFactory extends Factory
{
    protected $model = TimetableSlot::class;

    public function definition(): array
    {
        $period = Period::query()->inRandomOrder()->first();
        $class = $period
            ? SchoolClass::where('grade_level_id', $period->grade_level_id)->inRandomOrder()->first()
            : null;

        if (! $class) {
            $class = SchoolClass::query()->whereNotNull('grade_level_id')->inRandomOrder()->first();
            $period ??= $class
                ? Period::where('grade_level_id', $class->grade_level_id)->inRandomOrder()->first()
                : null;
        }

        if (! $class || ! $period) {
            throw new \RuntimeException('Create a grade-level class and period before using TimetableSlotFactory.');
        }

        return [
            'school_class_id' => $class->class_id,
            'subject_id' => Subject::query()->inRandomOrder()->value('subject_id'),
            'teacher_id' => Teacher::query()->inRandomOrder()->value('teacher_id'),
            'period_id' => $period->id,
            'day_of_week' => fake()->randomElement(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            'term_id' => Term::query()->inRandomOrder()->value('term_id'),
        ];
    }
}