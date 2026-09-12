<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link each class to a structured grade level.
     * The legacy `grade_level` string column is intentionally kept so any
     * already-migrated data / templates keep working during the transition.
     */
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('grade_level_id')
                  ->nullable()
                  ->after('grade_level')
                  ->constrained('grade_levels', 'grade_level_id')
                  ->nullOnDelete();
            $table->index('grade_level_id');
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['grade_level_id']);
            $table->dropIndex(['grade_level_id']);
            $table->dropColumn('grade_level_id');
        });
    }
};