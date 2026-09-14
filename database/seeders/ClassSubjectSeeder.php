<?php

namespace Database\Seeders;

use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class ClassSubjectSeeder extends Seeder
{
    /**
     * Puts a teacher in front of every subject in every class.
     *
     * These rows are what the teacher portal actually reads — marks,
     * attendance, assignments, report cards and class performance all resolve
     * through class_subjects — so a class with an unassigned subject is a
     * class whose report cards can never be finalized.
     *
     * The teacher is no longer round-robin from a pool of random staff: each
     * subject has its own teacher, and that teacher takes the subject in every
     * class. Mathematics in 9B is taught by the Mathematics teacher, and it
     * stays that way across re-seeds.
     */
    public function run(): void
    {
        $classes = SchoolClass::all();
        $core    = SubjectSeeder::coreSubjects();

        $subjects = Subject::whereIn('subject_name', $core)->get()->keyBy('subject_name');

        $assigned = 0;
        $unassigned = [];

        foreach ($classes as $class) {
            foreach ($core as $subjectName) {
                $subject = $subjects->get($subjectName);
                $teacher = TeacherSeeder::forSubject($subjectName);

                if (! $subject || ! $teacher) {
                    $unassigned[] = "{$class->class_name} · {$subjectName}";
                    continue;
                }

                // updateOrCreate rather than firstOrCreate: re-seeding an
                // existing database should correct a wrong teacher, not skip
                // the row because the class/subject pair already exists.
                ClassSubject::updateOrCreate(
                    [
                        'class_id'   => $class->class_id,
                        'subject_id' => $subject->subject_id,
                    ],
                    [
                        'teacher_id' => $teacher->teacher_id,
                    ]
                );

                $assigned++;
            }
        }

        $this->command->info("✔ {$assigned} class-subject assignments seeded, every one with its subject's teacher.");

        if (! empty($unassigned)) {
            $this->command->warn('✖ Left unassigned (missing subject or teacher): ' . implode(', ', $unassigned));
        }
    }
}
