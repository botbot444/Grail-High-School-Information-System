<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 11 — one report card per student per term.
 *
 * The plan proposed finalized_rank/finalized_at/finalized_by as columns on
 * `grades`, but rank is a single value per student per term while `grades` holds
 * a row per subject per assessment type — that shape would duplicate the rank
 * across every row and leave the overall class-teacher comment homeless. This
 * table is the amended shape (agreed 2026-09-13).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id('report_card_id');

            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->cascadeOnDelete();

            $table->foreignId('term_id')
                  ->constrained('terms', 'term_id')
                  ->cascadeOnDelete();

            // The class the student sat in when the term was finalized. Kept as a
            // snapshot so a later promotion does not rewrite historical rank.
            $table->foreignId('class_id')
                  ->nullable()
                  ->constrained('school_classes', 'class_id')
                  ->nullOnDelete();

            $table->decimal('term_average', 5, 2)->nullable();
            $table->unsignedSmallInteger('class_rank')->nullable();
            $table->unsignedSmallInteger('class_size')->nullable();

            $table->text('class_teacher_comment')->nullable();

            // Finalization. Null finalized_at means draft: no rank, not visible
            // to parents or students.
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')
                  ->nullable()
                  ->constrained('teachers', 'teacher_id')
                  ->nullOnDelete();

            $table->timestamps();

            $table->unique(['student_id', 'term_id'], 'report_cards_student_term_unique');
            $table->index(['class_id', 'term_id'], 'report_cards_class_term_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
