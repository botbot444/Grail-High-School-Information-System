<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 — school announcements.
 *
 * `audience` decides how the notice is aimed: school-wide, at specific classes,
 * or at whole grade levels. The actual targets live in announcement_targets,
 * because one notice can name several classes or several grade levels — see the
 * note on that migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id('announcement_id');

            $table->string('title');
            $table->text('body');

            // all | class | grade_level. A plain string rather than a native enum
            // so the column behaves identically on SQLite and MySQL.
            $table->string('audience', 20)->default('all');

            // Null published_at means draft: authored but not yet visible.
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users', 'id')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['audience', 'published_at'], 'announcements_audience_published_index');
            $table->index('expires_at', 'announcements_expires_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
