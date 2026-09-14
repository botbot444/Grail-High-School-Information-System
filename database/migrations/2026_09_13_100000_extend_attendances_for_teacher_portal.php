<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a remarks column and allows 'Excused' as an attendance status.
     *
     * MySQL stores status as a native ENUM, so the value list has to be
     * redeclared with MODIFY COLUMN. SQLite has no such statement — it renders
     * an enum as a varchar with a CHECK constraint, and a raw MODIFY is a
     * syntax error before it even reaches the column. Worse, the CHECK left in
     * place would reject every 'Excused' row the teacher attendance screen
     * tries to save, so this is not a cosmetic difference: the column has to be
     * widened to a plain string there.
     *
     * Attendance::STATUSES is the real source of truth for what is valid,
     * on either driver.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('attendances', 'remarks')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('status');
            });
        }

        if ($this->isMySql()) {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Present','Absent','Late','Excused') NOT NULL");

            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status', 20)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        if ($this->isMySql()) {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Present','Absent','Late') NOT NULL");
        } else {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('status', 20)->nullable(false)->change();
            });
        }

        if (Schema::hasColumn('attendances', 'remarks')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('remarks');
            });
        }
    }

    private function isMySql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
