<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Student;
use App\Models\ClassSubject;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::with('schoolClass.classSubjects')->get();
        $count    = 0;

        // Generate attendance for the last 4 school weeks (Mon–Fri only)
        $dates = [];
        $date  = Carbon::now()->startOfWeek()->subWeeks(4);
        while ($date->lte(Carbon::now())) {
            if ($date->isWeekday()) {
                $dates[] = $date->format('d-m-Y');
            }
            $date->addDay();
        }

        foreach ($students as $student) {
            $classSubjects = $student->schoolClass?->classSubjects ?? collect();

            foreach ($classSubjects as $cs) {
                // Only record attendance for the first subject per day
                // (in a real system each period would have its own record)
                foreach ($dates as $dateStr) {
                    // Skip if already exists (idempotent)
                    $exists = Attendance::where([
                        'student_id'       => $student->student_id,
                        'class_subject_id' => $cs->class_subject_id,
                        'date'             => $dateStr,
                    ])->exists();

                    if ($exists) {
                        continue;
                    }

                    // Weighted random: ~85% Present, ~10% Absent, ~5% Late
                    $roll = fake()->numberBetween(1, 100);
                    $status = match (true) {
                        $roll <= 85 => 'Present',
                        $roll <= 95 => 'Absent',
                        default     => 'Late',
                    };

                    Attendance::create([
                        'student_id'       => $student->student_id,
                        'class_subject_id' => $cs->class_subject_id,
                        'date'             => $dateStr,
                        'status'           => $status,
                        'recorded_by'      => $cs->teacher_id,
                    ]);
                    $count++;
                }
            }
        }

        $this->command->info("✔ {$count} attendance records seeded.");
    }
}
