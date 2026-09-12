<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Holidays / non-school days for a given academic year.
     * Used by Term::getSchoolDaysAttribute() so attendance percentages
     * can be calculated against the true number of teaching days.
     */
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id('holiday_id');
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years', 'year_id')
                  ->cascadeOnDelete();
            $table->date('date');
            $table->string('description'); // e.g. "New Year's Day", "Labor Day"
            $table->timestamps();

            $table->index('academic_year_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};