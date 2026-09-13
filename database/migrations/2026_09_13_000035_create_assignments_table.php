<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assignments are authored by a teacher against a class_subject — the same
 * anchor grades and attendance already use — so a student's assignment list is
 * simply "everything set for the subjects my class takes".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id('assignment_id');

            $table->foreignId('class_subject_id')
                  ->constrained('class_subjects', 'class_subject_id')
                  ->cascadeOnDelete();

            $table->foreignId('term_id')
                  ->nullable()
                  ->constrained('terms', 'term_id')
                  ->nullOnDelete();

            $table->string('title');
            $table->text('instructions')->nullable();

            // Draft assignments are invisible to students until published.
            $table->string('status', 20)->default('Draft');
            $table->timestamp('published_at')->nullable();
            $table->dateTime('due_at');

            $table->decimal('max_score', 6, 2)->default(100.00);
            $table->boolean('allows_file_upload')->default(true);

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('teachers', 'teacher_id')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_subject_id', 'due_at'], 'assignments_class_subject_due_index');
            $table->index(['status', 'due_at'], 'assignments_status_due_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
