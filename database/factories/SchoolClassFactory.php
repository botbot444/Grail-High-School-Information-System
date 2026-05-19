<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    /** Zambian secondary school grade levels */
    private const GRADE_LEVELS = [
        'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12',
    ];

    /** Class name suffixes */
    private const STREAMS = ['A', 'B', 'C', 'D'];

    public function definition(): array
    {
        $gradeLevel = fake()->randomElement(self::GRADE_LEVELS);
        $gradeNumber = explode(' ', $gradeLevel)[1]; // "10" from "Grade 10"
        $stream = fake()->randomElement(self::STREAMS);

        return [
            'class_name'  => "{$gradeNumber}{$stream}",      // e.g. "10A"
            'grade_level' => $gradeLevel,                     // e.g. "Grade 10"
            'teacher_id'  => Teacher::factory(),
        ];
    }
}
