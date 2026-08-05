<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['name' => 'admin', 'description' => 'System administrator', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'teacher', 'description' => 'Teacher or staff member', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'parent', 'description' => 'Parent or guardian', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'student', 'description' => 'Student account', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
