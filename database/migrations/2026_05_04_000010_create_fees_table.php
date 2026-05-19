<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id('fee_id');
            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->cascadeOnDelete();

            $table->string('description')->nullable(); // e.g. "Term 1 Tuition 2024"
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2);         // recomputed on every payment
            $table->date('due_date');

            // Status is always computed by the model, never set manually
            $table->enum('status', ['Pending', 'Partially Paid', 'Cleared'])
                  ->default('Pending');

            $table->string('term');
            $table->unsignedSmallInteger('academic_year');

            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            // One fee record per student / term / year (e.g. tuition)
            $table->unique(['student_id', 'term', 'academic_year'], 'unique_fee_per_term');

            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
