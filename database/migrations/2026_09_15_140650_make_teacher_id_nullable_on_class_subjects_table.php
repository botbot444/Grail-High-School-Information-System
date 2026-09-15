<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The admin "Create/Edit Class" screen lets an admin declare which subjects a
 * class offers before anyone has been assigned to teach them — the same
 * "decide later" pattern already used for the class's own homeroom teacher on
 * that same form. But `class_subjects.teacher_id` was NOT NULL, so saving a
 * class with subjects checked (and no teacher yet assigned to each) crashed
 * with a DB error instead of just recording the subject.
 *
 * Raw SQL rather than Schema::table(...)->change() — this project doesn't
 * have doctrine/dbal installed, which Laravel's column-modify helper needs.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE class_subjects MODIFY teacher_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Any existing NULLs would violate the restored NOT NULL constraint;
        // there's nothing safe to backfill them with, so this direction is
        // only expected to run against a database with no such rows.
        DB::statement('ALTER TABLE class_subjects MODIFY teacher_id BIGINT UNSIGNED NOT NULL');
    }
};
