<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Grades 8–12, two streams each.
     *
     * The homeroom teacher is drawn from the core-subject teachers in order,
     * so every form teacher also teaches a subject inside their own class.
     * That matters for testing: finalizing a report card is restricted to the
     * form teacher, and a form teacher who taught nothing in their own class
     * could not enter a single mark towards the cards they then have to sign
     * off.
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
        $core = SubjectSeeder::coreSubjects();
        $fallback = Teacher::first();

        foreach (self::CLASSES as $index => $classData) {
            $homeroomSubject = $core[$index % count($core)];
            $homeroom = TeacherSeeder::forSubject($homeroomSubject) ?? $fallback;

            SchoolClass::updateOrCreate(
                ['class_name' => $classData['class_name']],
                [
                    'grade_level' => $classData['grade_level'],
                    'teacher_id'  => $homeroom?->teacher_id,
                ]
            );
        }

        $this->command->info('✔ ' . count(self::CLASSES) . ' classes seeded, each with a homeroom teacher.');
    }
}
