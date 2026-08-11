<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE fees ADD INDEX IF NOT EXISTS fees_student_id_due_date_index (student_id, due_date)');
        DB::statement('ALTER TABLE fees ADD INDEX IF NOT EXISTS fees_status_due_date_index (status, due_date)');

        $feeItemIndexExists = DB::selectOne("SHOW INDEX FROM fee_items WHERE Key_name = 'fee_items_fee_id_category_index'");
        if (!$feeItemIndexExists) {
            DB::statement('ALTER TABLE fee_items ADD INDEX fee_items_fee_id_category_index (fee_id, category)');
        }

        $paymentsDateIndexExists = DB::selectOne("SHOW INDEX FROM payments WHERE Key_name = 'payments_fee_id_payment_date_index'");
        if (!$paymentsDateIndexExists) {
            DB::statement('ALTER TABLE payments ADD INDEX payments_fee_id_payment_date_index (fee_id, payment_date)');
        }

        $paymentsMethodIndexExists = DB::selectOne("SHOW INDEX FROM payments WHERE Key_name = 'payments_method_date_index'");
        if (!$paymentsMethodIndexExists) {
            DB::statement('ALTER TABLE payments ADD INDEX payments_method_date_index (payment_method, payment_date)');
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE fees DROP INDEX IF EXISTS fees_student_id_due_date_index');
        DB::statement('ALTER TABLE fees DROP INDEX IF EXISTS fees_status_due_date_index');

        DB::statement('ALTER TABLE fee_items DROP INDEX IF EXISTS fee_items_fee_id_category_index');

        DB::statement('ALTER TABLE payments DROP INDEX IF EXISTS payments_fee_id_payment_date_index');
        DB::statement('ALTER TABLE payments DROP INDEX IF EXISTS payments_method_date_index');
    }
};
