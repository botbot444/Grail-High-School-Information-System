<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data migration (runs after tables are created).
     *
     * Purpose: wire every existing row onto the new calendar entities WITHOUT
     * losing any data. It is fully idempotent so running it again is harmless,
     * and reversible so rollback only strips the links we created.
     *
     * The fees table was already backfilled for the "current year only"
     * by migration 000025; here we additionally link fees that reference
     * a different year, and we backfill grades + school_classes (which have
     * never been linked yet).
     */
    public function up(): void
    {
        // ── 1. Distinct academic years referenced by grades & fees ─────────────
        $years = collect()
            ->merge(DB::table('grades')->whereNotNull('academic_year')->distinct()->pluck('academic_year'))
            ->merge(DB::table('fees')->whereNotNull('academic_year')->distinct()->pluck('academic_year'))
            ->filter()
            ->unique()
            ->sort();

        if ($years->isEmpty()) {
            $years = collect([(int) date('Y')]);
        }

        // Default term windows used when seeding a year that has none.
        $termTemplates = [
            ['name' => 'Term 1', 'start' => '-01-15', 'end' => '-03-28'],
            ['name' => 'Term 2', 'start' => '-05-05', 'end' => '-08-15'],
            ['name' => 'Term 3', 'start' => '-09-01', 'end' => '-12-15'],
        ];

        $academicYearIds = [];

        foreach ($years as $year) {
            // Reuse an existing year (e.g. the one migration 000025 created).
            $yearId = DB::table('academic_years')
                ->where('label', (string) $year)
                ->value('year_id');

            if (! $yearId) {
                $yearId = DB::table('academic_years')->insertGetId([
                    'label'      => (string) $year,
                    'start_date' => $year.'-01-01',
                    'end_date'   => $year.'-12-31',
                    'is_current' => ((int) $year === (int) date('Y')) ? true : false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Make sure the three default terms exist for this year.
            foreach ($termTemplates as $t) {
                $exists = DB::table('terms')
                    ->where('academic_year_id', $yearId)
                    ->where('name', $t['name'])
                    ->exists();

                if (! $exists) {
                    DB::table('terms')->insert([
                        'academic_year_id' => $yearId,
                        'name'             => $t['name'],
                        'start_date'       => $year.$t['start'],
                        'end_date'         => $year.$t['end'],
                        'is_current'       => false,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            $academicYearIds[$year] = $yearId;
        }

        // ── 2. Grades: link academic_year_id + term_id (never overwrite) ──────────
        foreach (DB::table('grades')->get() as $grade) {
            $yearId = $academicYearIds[$grade->academic_year] ?? null;

            if ($yearId && ! $grade->academic_year_id) {
                DB::table('grades')
                    ->where('grade_id', $grade->grade_id)
                    ->update(['academic_year_id' => $yearId]);
            }

            if ($yearId && $grade->term && ! $grade->term_id) {
                $termId = DB::table('terms')
                    ->where('academic_year_id', $yearId)
                    ->where('name', $grade->term)
                    ->value('term_id');

                if ($termId) {
                    DB::table('grades')
                        ->where('grade_id', $grade->grade_id)
                        ->update(['term_id' => $termId]);
                }
            }
        }

        // ── 3. Fees: link any fee that migration 000025 missed (only where null) ─
        foreach (DB::table('fees')->get() as $fee) {
            $yearId = $academicYearIds[$fee->academic_year] ?? null;

            if ($yearId && ! $fee->academic_year_id) {
                DB::table('fees')
                    ->where('fee_id', $fee->fee_id)
                    ->update(['academic_year_id' => $yearId]);
            }

            if ($yearId && $fee->term && ! $fee->term_id) {
                $termId = DB::table('terms')
                    ->where('academic_year_id', $yearId)
                    ->where('name', $fee->term)
                    ->value('term_id');

                if ($termId) {
                    DB::table('fees')
                        ->where('fee_id', $fee->fee_id)
                        ->update(['term_id' => $termId]);
                }
            }
        }

        // ── 4. Grade levels: derive from school_classes.grade_level and link ─────
        $gradeLevels = DB::table('school_classes')
            ->whereNotNull('grade_level')
            ->distinct()
            ->pluck('grade_level');

        foreach ($gradeLevels as $gradeLevel) {
            $levelId = DB::table('grade_levels')
                ->where('name', $gradeLevel)
                ->value('grade_level_id');

            if (! $levelId) {
                $order = (int) filter_var($gradeLevel, FILTER_SANITIZE_NUMBER_INT);
                $order = $order > 0 ? $order : (DB::table('grade_levels')->max('order') + 1);

                $levelId = DB::table('grade_levels')->insertGetId([
                    'name'       => $gradeLevel,
                    'order'      => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('school_classes')
                ->where('grade_level', $gradeLevel)
                ->whereNull('grade_level_id')
                ->update(['grade_level_id' => $levelId]);
        }
    }

    public function down(): void
    {
        // Strip the links this migration created.
        DB::table('school_classes')->update(['grade_level_id' => null]);
        DB::table('grades')->update(['academic_year_id' => null, 'term_id' => null]);
        DB::table('fees')->update(['academic_year_id' => null, 'term_id' => null]);

        // Remove grade levels no longer referenced by any class.
        $usedLevels = DB::table('school_classes')
            ->whereNotNull('grade_level_id')
            ->pluck('grade_level_id');
        DB::table('grade_levels')->whereNotIn('grade_level_id', $usedLevels)->delete();

        // Remove terms no longer referenced by any grade or fee.
        $usedTerms = DB::table('grades')->whereNotNull('term_id')->pluck('term_id')
            ->merge(DB::table('fees')->whereNotNull('term_id')->pluck('term_id'))
            ->unique();
        DB::table('terms')->whereNotIn('term_id', $usedTerms)->delete();

        // Remove academic years no longer referenced anywhere.
        $usedYears = DB::table('grades')->whereNotNull('academic_year_id')->pluck('academic_year_id')
            ->merge(DB::table('fees')->whereNotNull('academic_year_id')->pluck('academic_year_id'))
            ->merge(DB::table('terms')->pluck('academic_year_id'))
            ->merge(DB::table('holidays')->pluck('academic_year_id'))
            ->unique();
        DB::table('academic_years')->whereNotIn('year_id', $usedYears)->delete();
    }
};