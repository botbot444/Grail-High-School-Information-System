<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_subject_id')->constrained('class_subjects')->onDelete('cascade');
            $table->integer('term');
            $table->decimal('mark', 5, 2); // 000.00 format
            $table->string('type'); // "Test", "Exam", "Assignment"
            $table->string('comment')->nullable();
            $table->timestamps();

            // Composite index for rapid report generation
            $table->index(['student_id', 'term']);
            // Prevent duplicate grade
            $table->unique(['student_id', 'class_subject_id', 'term', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
