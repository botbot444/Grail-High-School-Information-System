<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For every existing fee create one line item so no data is lost.
        // Uses a raw INSERT to keep memory usage flat even with many rows.
        DB::table('fee_items')->insertUsing(
            ['fee_id', 'item_name', 'category', 'amount', 'created_at', 'updated_at'],
            DB::table('fees')
                ->select(
                    'fee_id',
                    DB::raw("'General Fees' as item_name"),
                    DB::raw("'Other' as category"),
                    'amount_due as amount',
                    DB::raw('NOW()'),
                    DB::raw('NOW()')
                )
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('fee_items')
                          ->whereColumn('fee_items.fee_id', 'fees.fee_id');
                })
        );
    }

    public function down(): void
    {
        // Backfill is idempotent; rollback does not remove data.
    }
};
