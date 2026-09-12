<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Canonical list of grade levels (Grade 8, 9, 10, ...) that classes
     * belong to. Decouples the textual grade_level column from structured data
     * so reports, filtering and sequencing become reliable.
     */
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id('grade_level_id');
            $table->string('name')->unique(); // e.g. "Grade 10"
            $table->integer('order')->index(); // e.g. 10, 11, 12 for sorting
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};