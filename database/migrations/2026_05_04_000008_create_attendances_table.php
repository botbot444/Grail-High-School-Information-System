<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->foreignId('student_id')
                  ->constrained('students', 'student_id')
                  ->cascadeOnDelete();
            $table->foreignId('class_subject_id')
                  ->constrained('class_subjects', 'class_subject_id')
                  ->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['Present', 'Absent', 'Late']);

            // The teacher who recorded this entry (audit trail)
            $table->foreignId('recorded_by')
                  ->constrained('teachers', 'teacher_id')
                  ->restrictOnDelete();

            $table->timestamps();

            // Composite index for the most common queries (by student over a date range,
            // and by class-subject on a given date)
            $table->index(['student_id', 'date']);
            $table->index(['class_subject_id', 'date']);

            // A student can only have one attendance record per class-subject per day
            $table->unique(['student_id', 'class_subject_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
