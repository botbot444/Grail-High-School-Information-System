<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backs the Phase 12 EnsureAccountIsActive middleware, which reads
 * `is_active` to log a user out on their next request once an admin
 * deactivates them. Defaults to true so every existing account stays
 * active until someone explicitly deactivates it.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Guarded: the column is already present on some machines (an
        // earlier migrate run appears to have added it without the run
        // getting recorded), so this stays safe to re-run either way.
        if (! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('must_change_password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
