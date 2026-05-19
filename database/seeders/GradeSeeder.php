<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::with('schoolClass.classSubjects')->get();
        $year     = now()->year;
        $term     = 'Term 1';
        $count    = 0;

        foreach ($students as $student) {
            $classSubjects = $student->schoolClass?->classSubjects ?? collect();

            foreach ($classSubjects as $cs) {
                foreach (['CA', 'EXAM'] as $type) {
                    $maxScore = $type === 'CA' ? 50.00 : 100.00;

                    $exists = Grade::where([
                        'student_id'       => $student->student_id,
                        'class_subject_id' => $cs->class_subject_id,
                        'assessment_type'  => $type,
                        'term'             => $term,
                        'academic_year'    => $year,
                    ])->exists();

                    if ($exists) {
                        continue;
                    }

                    Grade::create([
                        'student_id'       => $student->student_id,
                        'class_subject_id' => $cs->class_subject_id,
                        'assessment_type'  => $type,
                        'score'            => fake()->randomFloat(2, 0, $maxScore),
                        'max_score'        => $maxScore,
                        'term'             => $term,
                        'academic_year'    => $year,
                        'recorded_by'      => $cs->teacher_id,
                    ]);
                    $count++;
                }
            }
        }

        $this->command->info("✔ {$count} grade records seeded (Term 1, {$year}).");
    }
}
