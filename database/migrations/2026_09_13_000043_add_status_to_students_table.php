<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 — enrolment status.
 *
 * A graduating student is not deleted and not soft-deleted: their grades, fees
 * and report cards must stay readable for years (see the retention policy in
 * Phase 15). They stop being enrolled instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('status', 20)->default('Enrolled')->after('class_id');
            $table->date('graduated_on')->nullable()->after('status');

            $table->index('status', 'students_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_status_index');
            $table->dropColumn(['status', 'graduated_on']);
        });
    }
};
