<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 — read/unread state, one row per user per announcement.
 *
 * Absence of a row means unread, so nothing has to be written when an
 * announcement is published to a thousand people.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('announcement_id')
                  ->constrained('announcements', 'announcement_id')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users', 'id')
                  ->cascadeOnDelete();

            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id'], 'announcement_reads_unique');
            $table->index('user_id', 'announcement_reads_user_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
    }
};
