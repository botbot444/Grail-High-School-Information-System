<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName  = fake()->lastName();

        return [
            'user_id'    => User::factory()->teacher(), // uses the teacher state on UserFactory
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => fake()->unique()->safeEmail(),
            'phone'      => fake()->phoneNumber(),
        ];
    }
}
