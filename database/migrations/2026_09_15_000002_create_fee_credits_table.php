<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ledger of every fee-credit event for a student: an overpayment granting
 * credit ('overpayment'), that credit being applied to a fee ('applied'),
 * or an admin manually recording a refund paid outside the system
 * ('refunded' — e.g. a withdrawing student with no future fee to carry the
 * credit into). Student::credit_balance is the running total this ledger
 * sums to; this table is the "why" — an audit trail for money the school
 * is holding on a family's behalf, in the same spirit as the Auditable
 * trait already used on Fee and Payment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_credits', function (Blueprint $table) {
            $table->id('credit_id');

            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->cascadeOnDelete();

            // Positive = credit granted (overpayment). Negative = credit
            // consumed (applied to a fee, or refunded outside the system).
            $table->decimal('amount', 10, 2);

            $table->enum('type', ['overpayment', 'applied', 'refunded']);

            // Set only on an 'overpayment' row: the payment that created it.
            $table->foreignId('source_payment_id')
                  ->nullable()
                  ->constrained('payments', 'payment_id')
                  ->nullOnDelete();

            // Set only on an 'applied' row: the fee the credit paid down.
            $table->foreignId('applied_fee_id')
                  ->nullable()
                  ->constrained('fees', 'fee_id')
                  ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')
                  ->nullable()
                  ->constrained('users', 'id')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_credits');
    }
};
