<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Fee;
use App\Models\AcademicYear;
use App\Models\Term;

class FeeSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $year     = now()->year;
        $term     = 'Term 1';
        $count    = 0;

        $academicYear = AcademicYear::where('label', (string) $year)->first();
        $termModel   = $academicYear ? Term::where('academic_year_id', $academicYear->year_id)->where('name', $term)->first() : null;

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

            $status      = $roll <= 50 ? 'Cleared' : ($roll <= 80 ? 'Partially Paid' : 'Pending');
            $amountPaid  = match ($status) {
                'Cleared'        => $amountDue,
                'Partially Paid' => round($amountDue * fake()->randomFloat(2, 0.2, 0.8), 2),
                default          => 0.00,
            };
            $balance = round($amountDue - $amountPaid, 2);

            $fee = Fee::create([
                'student_id'       => $student->student_id,
                'description'      => "Tuition – {$term} {$year}",
                'amount_due'       => $amountDue,
                'amount_paid'      => $amountPaid,
                'balance'          => $balance,
                'due_date'         => now()->startOfYear()->addDays(30)->format('d-m-Y'),
                'status'           => $status,
                'term'             => $term,
                'academic_year'    => $year,
                'academic_year_id' => $academicYear?->year_id,
                'term_id'          => $termModel?->term_id,
                'last_updated'     => in_array($status, ['Cleared', 'Partially Paid'])
                    ? now()->subDays(rand(1, 60))
                    : null,
            ]);

            // Attach a single line item so fee_items stays in sync with fees.
            \App\Models\FeeItem::create([
                'fee_id'    => $fee->fee_id,
                'item_name' => 'General Fees',
                'category'  => 'Other',
                'amount'    => $amountDue,
            ]);

            $count++;
        }

        $this->command->info("✔ {$count} fee records seeded.");
    }
}
