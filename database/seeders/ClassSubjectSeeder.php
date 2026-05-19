<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\Teacher;

class ClassSubjectSeeder extends Seeder
{
    /**
     * Core subjects taught in every class.
     * Each subject is assigned a teacher round-robin from the teacher pool.
     */
    private const CORE_SUBJECTS = [
        'English Language',
        'Mathematics',
        'Integrated Science',
        'Geography',
        'History',
        'Civic Education',
        'Computer Studies',
    ];

    public function run(): void
    {
        $classes  = SchoolClass::all();
        $teachers = Teacher::all();
        $count    = 0;
        $tIdx     = 0;

        foreach ($classes as $class) {
            foreach (self::CORE_SUBJECTS as $subjectName) {
                $subject = Subject::where('subject_name', $subjectName)->first();

                if (! $subject) {
                    continue;
                }

                ClassSubject::firstOrCreate(
                    [
                        'class_id'   => $class->class_id,
                        'subject_id' => $subject->subject_id,
                    ],
                    [
                        'teacher_id' => $teachers[$tIdx % $teachers->count()]->teacher_id,
                    ]
                );

                $tIdx++;
                $count++;
            }
        }

        $this->command->info("✔ {$count} class-subject assignments seeded.");
    }
}
