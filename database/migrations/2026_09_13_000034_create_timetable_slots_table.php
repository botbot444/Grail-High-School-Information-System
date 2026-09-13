<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')
                ->constrained('school_classes', 'class_id')
                ->restrictOnDelete();
            $table->foreignId('subject_id')
                ->nullable()
                ->constrained('subjects', 'subject_id')
                ->restrictOnDelete();
            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('teachers', 'teacher_id')
                ->restrictOnDelete();
            $table->foreignId('period_id')
                ->constrained('periods')
                ->restrictOnDelete();
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            $table->foreignId('term_id')
                ->constrained('terms', 'term_id')
                ->restrictOnDelete();
            $table->timestamps();

            $table->unique(
                ['school_class_id', 'day_of_week', 'period_id', 'term_id'],
                'timetable_slot_class_day_period_term_unique'
            );
            $table->index(
                ['teacher_id', 'term_id', 'day_of_week', 'period_id'],
                'timetable_slot_teacher_conflict_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
    }
};