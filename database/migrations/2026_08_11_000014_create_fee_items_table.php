<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_items', function (Blueprint $table) {
            $table->id('fee_item_id');
            $table->foreignId('fee_id')
                  ->constrained('fees', 'fee_id')
                  ->cascadeOnDelete();

            $table->string('item_name');
            $table->string('category');
            $table->decimal('amount', 10, 2);

            $table->timestamps();

            $table->index(['fee_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_items');
    }
};
