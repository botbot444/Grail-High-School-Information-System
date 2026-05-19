<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id('class_id');
            $table->string('class_name');
            $table->string('grade_level'); // e.g. "Grade 10", "Form 3"
            // teacher_id (class teacher / homeroom) added after teachers table via foreign key
            $table->unsignedBigInteger('teacher_id')->constrained('teachers', 'teacher_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
