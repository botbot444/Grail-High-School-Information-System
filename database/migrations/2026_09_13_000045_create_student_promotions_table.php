<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 — one row per student moved, grouped into a batch.
 *
 * This is what makes a promotion run reviewable and reversible: it records the
 * class a student came from, so a mistaken run can put everyone back. It also
 * carries the year context that permanent (non-year-scoped) classes cannot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();

            // Groups every row written by one run of the promotion screen.
            $table->string('batch_ref', 32);

            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->cascadeOnDelete();

            $table->foreignId('from_class_id')
                  ->nullable()
                  ->constrained('school_classes', 'class_id')
                  ->nullOnDelete();

            $table->foreignId('to_class_id')
                  ->nullable()
                  ->constrained('school_classes', 'class_id')
                  ->nullOnDelete();

            // promoted | retained | graduated
            $table->string('outcome', 20);

            // The status the student held before the run, so a rollback restores it.
            $table->string('previous_status', 20)->default('Enrolled');

            // The year being moved into.
            $table->foreignId('academic_year_id')
                  ->nullable()
                  ->constrained('academic_years', 'year_id')
                  ->nullOnDelete();

            $table->foreignId('promoted_by')
                  ->nullable()
                  ->constrained('users', 'id')
                  ->nullOnDelete();

            $table->timestamp('rolled_back_at')->nullable();

            $table->timestamps();

            $table->index(['batch_ref', 'outcome'], 'student_promotions_batch_index');
            $table->index('student_id', 'student_promotions_student_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
