<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id('grade_id');
            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->cascadeOnDelete();
            $table->foreignId('class_subject_id')
                  ->constrained('class_subjects', 'class_subject_id')
                  ->cascadeOnDelete();

            // CA = Continuous Assessment, EXAM = end-of-term examination
            $table->enum('assessment_type', ['CA', 'EXAM']);

            $table->decimal('score', 6, 2);
            $table->decimal('max_score', 6, 2)->default(100.00);

            // e.g. "Term 1", "Term 2", "Term 3"
            $table->string('term');
            $table->unsignedSmallInteger('academic_year'); // e.g. 2024

            // Teacher who entered the grade (audit trail)
            $table->foreignId('recorded_by')
                  ->constrained('teachers', 'teacher_id')
                  ->restrictOnDelete();

            $table->timestamps();

            // Composite index for report card and performance summary queries
            $table->index(['student_id', 'class_subject_id', 'term', 'academic_year']);

            // One entry per student / subject / assessment type / term / year
            $table->unique([
                'student_id',
                'class_subject_id',
                'assessment_type',
                'term',
                'academic_year',
            ], 'unique_grade_entry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
