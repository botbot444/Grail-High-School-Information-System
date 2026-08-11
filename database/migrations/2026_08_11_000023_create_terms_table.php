<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id('term_id');
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years', 'year_id')
                  ->cascadeOnDelete();

            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('academic_year_id');
            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
