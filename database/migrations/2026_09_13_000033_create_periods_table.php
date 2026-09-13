<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_id')
                ->constrained('grade_levels', 'grade_level_id')
                ->restrictOnDelete();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('order');
            $table->boolean('is_break')->default(false);
            $table->timestamps();

            $table->unique(['grade_level_id', 'order']);
            $table->index(['grade_level_id', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};