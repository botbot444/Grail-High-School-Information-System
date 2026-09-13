<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow 'Overdue' as a fee status.
     *
     * MySQL stores this column as a native ENUM, so the value list has to be
     * redeclared. SQLite renders enums as a varchar + CHECK constraint, which
     * would reject the new value, so the column is widened to a plain string
     * there — the Fee state machine is the real source of truth either way.
     */
    public function up(): void
    {
        if ($this->isMySql()) {
            DB::statement("ALTER TABLE fees MODIFY COLUMN status ENUM('Pending','Partially Paid','Cleared','Overdue') DEFAULT 'Pending'");

            return;
        }

        Schema::table('fees', function (Blueprint $table) {
            $table->string('status', 20)->default('Pending')->change();
        });
    }

    public function down(): void
    {
        if ($this->isMySql()) {
            DB::statement("ALTER TABLE fees MODIFY COLUMN status ENUM('Pending','Partially Paid','Cleared') DEFAULT 'Pending'");

            return;
        }

        Schema::table('fees', function (Blueprint $table) {
            $table->string('status', 20)->default('Pending')->change();
        });
    }

    private function isMySql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
