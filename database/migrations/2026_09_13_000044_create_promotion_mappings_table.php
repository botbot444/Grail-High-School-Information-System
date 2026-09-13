<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 — reusable "where does this class go next" defaults, e.g. 10A → 11A.
 *
 * A null to_class_id means the source class graduates: it is the final grade
 * level and there is nowhere above it to promote into.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_mappings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_class_id')
                  ->constrained('school_classes', 'class_id')
                  ->cascadeOnDelete();

            $table->foreignId('to_class_id')
                  ->nullable()
                  ->constrained('school_classes', 'class_id')
                  ->nullOnDelete();

            // True when this class's students leave the school rather than move up.
            $table->boolean('graduates')->default(false);

            $table->timestamps();

            $table->unique('from_class_id', 'promotion_mappings_from_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_mappings');
    }
};
