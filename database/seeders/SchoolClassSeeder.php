<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Teacher;

class SchoolClassSeeder extends Seeder
{
    /**
     * Seed a realistic set of classes across Grades 8–12 (two streams each).
     * Teachers are assigned round-robin so every class has a homeroom teacher.
     */
    private const CLASSES = [
        ['class_name' => '8A',  'grade_level' => 'Grade 8'],
        ['class_name' => '8B',  'grade_level' => 'Grade 8'],
        ['class_name' => '9A',  'grade_level' => 'Grade 9'],
        ['class_name' => '9B',  'grade_level' => 'Grade 9'],
        ['class_name' => '10A', 'grade_level' => 'Grade 10'],
        ['class_name' => '10B', 'grade_level' => 'Grade 10'],
        ['class_name' => '11A', 'grade_level' => 'Grade 11'],
        ['class_name' => '11B', 'grade_level' => 'Grade 11'],
        ['class_name' => '12A', 'grade_level' => 'Grade 12'],
        ['class_name' => '12B', 'grade_level' => 'Grade 12'],
    ];

    public function run(): void
    {
        $teachers = Teacher::all();

        foreach (self::CLASSES as $index => $classData) {
            SchoolClass::firstOrCreate(
                ['class_name' => $classData['class_name']],
                [
                    'grade_level' => $classData['grade_level'],
                    'teacher_id'  => $teachers[$index % $teachers->count()]->teacher_id,
                ]
            );
        }

        $this->command->info('✔ ' . count(self::CLASSES) . ' classes seeded.');
    }
}
