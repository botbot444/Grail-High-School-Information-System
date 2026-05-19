<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');

            // Linked to a users account (role = 'student') — nullable so admin
            // can pre-register a student before they have login credentials.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Parent user account (role = 'parent') — nullable; parent may not
            // have an account yet at registration time.
            $table->foreignId('parent_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('student_number')->unique(); // school-issued ID, e.g. "2024/001"

            // Class assignment — nullable during initial registration
            $table->foreignId('class_id')
                  ->nullable()
                  ->constrained('school_classes', 'class_id')
                  ->nullOnDelete();

            // Guardian contact — stored here for quick access without joining users
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();

            $table->date('enrolment_date');
            $table->timestamps();
            $table->softDeletes(); // historical data preserved per spec
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
