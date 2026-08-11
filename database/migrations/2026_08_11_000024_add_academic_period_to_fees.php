<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('academic_year');
            $table->unsignedBigInteger('term_id')->nullable()->after('term');

            $table->foreign('academic_year_id')
                  ->references('year_id')->on('academic_years')
                  ->nullOnDelete();

            $table->foreign('term_id')
                  ->references('term_id')->on('terms')
                  ->nullOnDelete();

            $table->index(['academic_year_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['term_id']);
            $table->dropIndex(['academic_year_id', 'term_id']);
            $table->dropColumn(['academic_year_id', 'term_id']);
        });
    }
};
