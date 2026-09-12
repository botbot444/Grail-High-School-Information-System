<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attach the structured academic_year_id + term_id foreign keys to
     * grades. The legacy `academic_year` (int) and `term` (string) columns are
     * kept for backward compatibility with existing reports/templates.
     */
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                  ->nullable()
                  ->after('academic_year')
                  ->constrained('academic_years', 'year_id')
                  ->nullOnDelete();
            $table->foreignId('term_id')
                  ->nullable()
                  ->after('term')
                  ->constrained('terms', 'term_id')
                  ->nullOnDelete();
            $table->index('academic_year_id');
            $table->index('term_id');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['term_id']);
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['term_id']);
            $table->dropColumn(['academic_year_id', 'term_id']);
        });
    }
};