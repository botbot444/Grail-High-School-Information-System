<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('fee_id')
                  ->constrained('fees', 'fee_id')
                  ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('payment_date');

            $table->foreignId('recorded_by')
                  ->nullable()
                  ->constrained('users', 'id')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['fee_id', 'payment_date']);
            $table->index(['payment_method', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
