<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\Term;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::with('schoolClass.classSubjects')->get();

        // Anchor the seeded marks to a real term from the Phase 3 calendar so
        // report cards and term-scoped reports can find them by term_id, not
        // just by the legacy term-name string.
        $termModel = Term::with('academicYear')->orderBy('start_date')->first();
        $year      = (int) ($termModel?->academicYear?->label ?? now()->year);
        $term      = $termModel?->name ?? 'Term 1';
        $count     = 0;

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
                        'term_id'          => $termModel?->term_id,
                        'academic_year_id' => $termModel?->academicYear?->year_id,
                        'recorded_by'      => $cs->teacher_id,
                    ]);
                    $count++;
                }
            }
        }

        $this->command->info("✔ {$count} grade records seeded ({$term}, {$year}).");
    }
}
