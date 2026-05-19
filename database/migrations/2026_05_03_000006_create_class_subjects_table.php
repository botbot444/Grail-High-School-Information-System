<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Resolves the many-to-many between school_classes and subjects.
     * Also captures which teacher delivers that subject in that class.
     */
    public function up(): void
    {
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id('class_subject_id');
            $table->foreignId('class_id')
                  ->constrained('school_classes', 'class_id')
                  ->cascadeOnDelete();
            $table->foreignId('subject_id')
                  ->constrained('subjects', 'subject_id')
                  ->cascadeOnDelete();
            $table->foreignId('teacher_id')
                  ->constrained('teachers', 'teacher_id')
                  ->restrictOnDelete(); // prevent removing a teacher who owns active class-subjects
            $table->timestamps();

            // A subject can only be assigned once per class
            $table->unique(['class_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subjects');
    }
};
