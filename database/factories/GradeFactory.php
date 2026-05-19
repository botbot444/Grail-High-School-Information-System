<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        $maxScore = fake()->randomElement([50.00, 60.00, 100.00]);
        $score    = fake()->randomFloat(2, 0, $maxScore);

        return [
            'student_id'       => Student::factory(),
            'class_subject_id' => ClassSubject::factory(),
            'assessment_type'  => fake()->randomElement(['CA', 'EXAM']),
            'score'            => $score,
            'max_score'        => $maxScore,
            'term'             => fake()->randomElement(['Term 1', 'Term 2', 'Term 3']),
            'academic_year'    => now()->year,
            'recorded_by'      => Teacher::factory(),
        ];
    }

    public function ca(): static
    {
        return $this->state(fn () => ['assessment_type' => 'CA']);
    }

    public function exam(): static
    {
        return $this->state(fn () => ['assessment_type' => 'EXAM']);
    }

    public function passing(): static
    {
        return $this->state(function (array $attributes) {
            $max = $attributes['max_score'];
            return ['score' => fake()->randomFloat(2, $max * 0.5, $max)];
        });
    }

    public function failing(): static
    {
        return $this->state(function (array $attributes) {
            $max = $attributes['max_score'];
            return ['score' => fake()->randomFloat(2, 0, $max * 0.49)];
        });
    }
}
