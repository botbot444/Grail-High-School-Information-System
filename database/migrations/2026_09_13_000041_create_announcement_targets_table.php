<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 — who a targeted announcement is aimed at.
 *
 * The plan proposed targetable_type / targetable_id as columns on the
 * announcements row itself, but the same phase requires that one announcement
 * can name several classes or several grade levels (and its exit checklist
 * tests exactly that). A single column pair holds one target, so targets get
 * their own table — amended 2026-09-13.
 *
 * targetable is polymorphic: SchoolClass or GradeLevel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_targets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('announcement_id')
                  ->constrained('announcements', 'announcement_id')
                  ->cascadeOnDelete();

            $table->string('targetable_type');
            $table->unsignedBigInteger('targetable_id');

            $table->timestamps();

            $table->unique(
                ['announcement_id', 'targetable_type', 'targetable_id'],
                'announcement_targets_unique'
            );
            $table->index(['targetable_type', 'targetable_id'], 'announcement_targets_morph_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_targets');
    }
};
