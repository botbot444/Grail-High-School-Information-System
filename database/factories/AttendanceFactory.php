<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'student_id'       => Student::factory(),
            'class_subject_id' => ClassSubject::factory(),
            'date'             => fake()->dateTimeBetween('-90 days', 'now')->format('d-m-Y'),
            // Weighted toward Present to reflect realistic attendance
            'status'           => fake()->randomElement([
                'Present', 'Present', 'Present', 'Present',
                'Absent', 'Late',
            ]),
            'recorded_by'      => Teacher::factory(),
        ];
    }

    public function present(): static
    {
        return $this->state(fn () => ['status' => 'Present']);
    }

    public function absent(): static
    {
        return $this->state(fn () => ['status' => 'Absent']);
    }

    public function late(): static
    {
        return $this->state(fn () => ['status' => 'Late']);
    }
}
