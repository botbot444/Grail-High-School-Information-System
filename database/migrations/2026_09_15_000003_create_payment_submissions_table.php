<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A parent-submitted claim of payment made outside the system (bank deposit,
 * mobile money, etc.) with proof attached. Nothing here touches a fee's
 * balance — that only happens when an admin approves it, at which point a
 * real Payment row is created via Fee::applyPayment(). This table is the
 * review queue sitting in front of that ledger, not the ledger itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id('submission_id');

            $table->foreignId('fee_id')
                  ->constrained('fees', 'fee_id')
                  ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50);
            $table->string('reference_number')->nullable();
            $table->timestamp('payment_date');

            $table->string('proof_path');
            $table->string('proof_original_filename')->nullable();
            $table->text('notes')->nullable();

            $table->string('status', 20)->default('pending');

            $table->foreignId('submitted_by')
                  ->constrained('users', 'id')
                  ->cascadeOnDelete();

            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users', 'id')
                  ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Set only once approved — the Payment row it produced.
            $table->foreignId('payment_id')
                  ->nullable()
                  ->constrained('payments', 'payment_id')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['fee_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
    }
};
