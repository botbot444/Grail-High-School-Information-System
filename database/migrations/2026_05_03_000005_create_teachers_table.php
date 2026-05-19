<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Teachers are linked to the users table (role = 'teacher').
     * This table holds the teacher profile / HR data.
     */
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id('teacher_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamps();
            $table->softDeletes(); // preserves records when teacher leaves
        });

        // Now that teachers table exists, add FK on school_classes
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreign('teacher_id')
                  ->references('teacher_id')
                  ->on('teachers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });
        Schema::dropIfExists('teachers');
    }
};
