<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create a default academic year + term if none exist
        $yearId = DB::table('academic_years')->insertGetId([
            'label'      => (string) date('Y'),
            'start_date' => date('Y-01-01'),
            'end_date'   => date('Y-12-31'),
            'is_current' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $termId = DB::table('terms')->insertGetId([
            'academic_year_id' => $yearId,
            'name'             => 'Term 1',
            'start_date'       => date('Y-01-01'),
            'end_date'         => date('Y-03-31'),
            'is_current'       => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Backfill fees that have string term/int academic_year but no FK yet
        DB::table('fees')
            ->whereNull('academic_year_id')
            ->orWhereNull('term_id')
            ->update([
                'academic_year_id' => $yearId,
                'term_id'          => $termId,
            ]);
    }

    public function down(): void
    {
        // Intentionally left empty: backfill is additive.
    }
};
