<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backs Student::grantCredit() / applyAvailableCredit() — the running
 * account credit for a student who has overpaid a fee (a parent's bank or
 * mobile money deposit doesn't have to land on the exact balance owed).
 * See the fee_credits table for the ledger of how each credit was granted
 * and applied; this column is just the running total, same pattern as
 * Fee::amount_paid / Fee::balance.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Guarded the same way as add_is_active_to_users_table — safe to
        // re-run if a migrate already added this on another machine.
        if (! Schema::hasColumn('students', 'credit_balance')) {
            Schema::table('students', function (Blueprint $table) {
                $table->decimal('credit_balance', 10, 2)->default(0.00)->after('guardian_phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'credit_balance')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('credit_balance');
            });
        }
    }
};
