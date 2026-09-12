<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_name' => fake()->unique()->randomElement([
                'Mathematics',
                'English Language',
                'Science',
                'Physics',
                'Chemistry',
                'Biology',
                'Geography',
                'History',
                'Civic Education',
                'Computer Studies',
                'Business Studies',
                'Religious Education',
                'Agricultural Science',
                'Home Economics',
                'Physical Education',
            ]),
        ];
    }
}