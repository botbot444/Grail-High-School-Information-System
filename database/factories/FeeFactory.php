<?php

namespace Database\Factories;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeeFactory extends Factory
{
    protected $model = Fee::class;

    public function definition(): array
    {
        $amountDue  = fake()->randomElement([1500.00, 2000.00, 2500.00, 3000.00]);
        $amountPaid = 0.00;
        $term       = fake()->randomElement(['Term 1', 'Term 2', 'Term 3']);

        return [
            'student_id'   => Student::factory(),
            'description'  => "Tuition Fee – {$term} " . now()->year,
            'amount_due'   => $amountDue,
            'amount_paid'  => $amountPaid,
            'balance'      => $amountDue,           // balance = due - paid
            'due_date'     => fake()->dateTimeBetween('now', '+60 days')->format('d-m-Y'),
            'status'       => 'Pending',
            'term'         => $term,
            'academic_year'=> now()->year,
            'last_updated' => null,
        ];
    }

    // ── States ────────────────────────────────────────────────────────────────

    public function pending(): static
    {
        return $this->state(fn (array $attr) => [
            'amount_paid' => 0.00,
            'balance'     => $attr['amount_due'],
            'status'      => 'Pending',
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(function (array $attr) {
            $paid = round($attr['amount_due'] * fake()->randomFloat(2, 0.1, 0.89), 2);
            return [
                'amount_paid' => $paid,
                'balance'     => round($attr['amount_due'] - $paid, 2),
                'status'      => 'Partially Paid',
                'last_updated'=> now(),
            ];
        });
    }

    public function cleared(): static
    {
        return $this->state(fn (array $attr) => [
            'amount_paid' => $attr['amount_due'],
            'balance'     => 0.00,
            'status'      => 'Cleared',
            'last_updated'=> now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => fake()->dateTimeBetween('-90 days', '-1 day')->format('Y-m-d'),
            'status'   => 'Pending',
        ]);
    }
}
