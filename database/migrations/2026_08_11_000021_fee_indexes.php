<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supplementary fee-related indexes.
     *
     * Written with the schema builder rather than raw SQL so the migration
     * runs on SQLite (local dev) as well as MySQL.
     */
    private array $indexes = [
        ['fees',      ['student_id', 'due_date'],        'fees_student_id_due_date_index'],
        ['fees',      ['status', 'due_date'],            'fees_status_due_date_index'],
        ['fee_items', ['fee_id', 'category'],            'fee_items_fee_id_category_index'],
        ['payments',  ['fee_id', 'payment_date'],        'payments_fee_id_payment_date_index'],
        ['payments',  ['payment_method', 'payment_date'], 'payments_method_date_index'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as [$table, $columns, $name]) {
            if (! Schema::hasTable($table) || Schema::hasIndex($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                $blueprint->index($columns, $name);
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes) as [$table, $columns, $name]) {
            if (! Schema::hasTable($table) || ! Schema::hasIndex($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($name) {
                $blueprint->dropIndex($name);
            });
        }
    }
};
