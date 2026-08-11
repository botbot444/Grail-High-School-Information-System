<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * amount_due already exists in 000010_create_fees_table, but we keep this
     * as a guard for environments where that migration may not have run.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('fees', 'amount_due')) {
            Schema::table('fees', function (Blueprint $table) {
                $table->decimal('amount_due', 10, 2)->default(0.00)->after('description');
            });
        }
    }

    public function down(): void
    {
        // Do NOT drop the column — it's part of the original schema.
    }
};
