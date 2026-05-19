<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Fee;

class FeeSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $year     = now()->year;
        $term     = 'Term 1';
        $count    = 0;

        foreach ($students as $student) {
            $exists = Fee::where([
                'student_id'    => $student->student_id,
                'term'          => $term,
                'academic_year' => $year,
            ])->exists();

            if ($exists) {
                continue;
            }

            $amountDue = 2500.00; // Standard tuition in ZMW (adjust as needed)

            // Distribute fee statuses realistically:
            // 50% Cleared, 30% Partially Paid, 20% Pending
            $roll = rand(1, 100);

            if ($roll <= 50) {
                // Cleared
                Fee::create([
                    'student_id'   => $student->student_id,
                    'description'  => "Tuition – {$term} {$year}",
                    'amount_due'   => $amountDue,
                    'amount_paid'  => $amountDue,
                    'balance'      => 0.00,
                    'due_date'     => now()->startOfYear()->addDays(30)->format('d-m-Y'),
                    'status'       => 'Cleared',
                    'term'         => $term,
                    'academic_year'=> $year,
                    'last_updated' => now()->subDays(rand(5, 60)),
                ]);
            } elseif ($roll <= 80) {
                // Partially Paid
                $paid = round($amountDue * fake()->randomFloat(2, 0.2, 0.8), 2);
                Fee::create([
                    'student_id'   => $student->student_id,
                    'description'  => "Tuition – {$term} {$year}",
                    'amount_due'   => $amountDue,
                    'amount_paid'  => $paid,
                    'balance'      => round($amountDue - $paid, 2),
                    'due_date'     => now()->startOfYear()->addDays(30)->format('d-m-Y'),
                    'status'       => 'Partially Paid',
                    'term'         => $term,
                    'academic_year'=> $year,
                    'last_updated' => now()->subDays(rand(1, 30)),
                ]);
            } else {
                // Pending / Overdue
                Fee::create([
                    'student_id'   => $student->student_id,
                    'description'  => "Tuition – {$term} {$year}",
                    'amount_due'   => $amountDue,
                    'amount_paid'  => 0.00,
                    'balance'      => $amountDue,
                    'due_date'     => now()->startOfYear()->addDays(30)->format('d-m-Y'),
                    'status'       => 'Pending',
                    'term'         => $term,
                    'academic_year'=> $year,
                    'last_updated' => null,
                ]);
            }
            $count++;
        }

        $this->command->info("✔ {$count} fee records seeded.");
    }
}
