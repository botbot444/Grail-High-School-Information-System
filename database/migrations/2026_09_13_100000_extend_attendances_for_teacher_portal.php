<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('status');
        });

        // Extend the status enum to include Excused (MySQL-specific).
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Present','Absent','Late','Excused') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Present','Absent','Late') NOT NULL");

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
