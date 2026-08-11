<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: change enum to include Overdue
        DB::statement("ALTER TABLE fees MODIFY COLUMN status ENUM('Pending','Partially Paid','Cleared','Overdue') DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE fees MODIFY COLUMN status ENUM('Pending','Partially Paid','Cleared') DEFAULT 'Pending'");
    }
};
