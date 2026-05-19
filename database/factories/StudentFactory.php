<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    private static int $counter = 1;

    public function definition(): array
    {
        $year = now()->year;
        $seq  = str_pad(self::$counter++, 4, '0', STR_PAD_LEFT);

        return [
            'user_id'        => null, // set explicitly or via UserFactory::student() state
            'parent_user_id' => null,
            'first_name'     => fake()->firstName(),
            'last_name'      => fake()->lastName(),
            'date_of_birth'  => fake()->dateTimeBetween('-20 years', '-13 years')->format('d-m-Y'),
            'gender'         => fake()->randomElement(['Male', 'Female']),
            'student_number' => "{$year}/{$seq}",
            'class_id'       => SchoolClass::factory(),
            'guardian_name'  => fake()->name(),
            'guardian_phone' => fake()->phoneNumber(),
            'enrolment_date' => fake()->dateTimeBetween('-4 years', 'now')->format('d-m-Y'),
        ];
    }

    /** State: student has a linked user account */
    public function withUserAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->student(),
        ]);
    }

    /** State: student has a linked parent account */
    public function withParentAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_user_id' => User::factory()->parent(),
        ]);
    }
}
