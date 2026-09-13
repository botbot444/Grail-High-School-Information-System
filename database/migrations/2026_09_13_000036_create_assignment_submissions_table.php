<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One submission per student per assignment. Re-submitting overwrites the same
 * row rather than creating a second one, which keeps "has this student handed
 * in?" a single-row question.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id('submission_id');

            $table->foreignId('assignment_id')
                  ->constrained('assignments', 'assignment_id')
                  ->cascadeOnDelete();

            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->cascadeOnDelete();

            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();

            $table->timestamp('submitted_at')->nullable();

            // Grading — written by the teacher, read-only to the student.
            $table->decimal('score', 6, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')
                  ->nullable()
                  ->constrained('teachers', 'teacher_id')
                  ->nullOnDelete();
            $table->timestamp('graded_at')->nullable();

            $table->timestamps();

            $table->unique(['assignment_id', 'student_id'], 'assignment_submissions_unique');
            $table->index(['student_id', 'submitted_at'], 'assignment_submissions_student_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
