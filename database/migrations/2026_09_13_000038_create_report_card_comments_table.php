<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 11 — per-subject teacher comments, keyed by student + term + subject.
 *
 * Kept out of `grades` because a subject has both a CA row and an EXAM row for
 * a term, so a column there would be ambiguous about which one carries the
 * comment (and would vanish for a CA-only subject).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_comments', function (Blueprint $table) {
            $table->id('comment_id');

            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->cascadeOnDelete();

            $table->foreignId('term_id')
                  ->constrained('terms', 'term_id')
                  ->cascadeOnDelete();

            $table->foreignId('class_subject_id')
                  ->constrained('class_subjects', 'class_subject_id')
                  ->cascadeOnDelete();

            $table->text('comment');

            // Author — the subject teacher who wrote it.
            $table->foreignId('teacher_id')
                  ->nullable()
                  ->constrained('teachers', 'teacher_id')
                  ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['student_id', 'term_id', 'class_subject_id'],
                'report_card_comments_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_comments');
    }
};
