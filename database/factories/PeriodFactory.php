<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodFactory extends Factory
{
    protected $model = Period::class;

    public function definition(): array
    {
        $order = fake()->numberBetween(1, 6);
        $startMinutes = 7 * 60 + (($order - 1) * 60);

        return [
            'grade_level_id' => GradeLevel::query()->inRandomOrder()->value('grade_level_id')
                ?? GradeLevel::query()->create(['name' => 'Grade '.$order, 'order' => $order])->grade_level_id,
            'name' => 'Period '.$order,
            'start_time' => sprintf('%02d:%02d', intdiv($startMinutes, 60), $startMinutes % 60),
            'end_time' => sprintf('%02d:%02d', intdiv($startMinutes + 45, 60), ($startMinutes + 45) % 60),
            'order' => $order,
            'is_break' => false,
        ];
    }
}