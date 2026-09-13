<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo announcements covering all three audience types, plus one expired and
 * one draft, so every state on the admin list and every branch of the
 * visibility scope has something to show.
 */
class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@grail.school')->first();
        $classes = SchoolClass::orderBy('class_name')->get();
        $levels = GradeLevel::orderBy('order')->get();

        if ($classes->isEmpty() || $levels->isEmpty()) {
            $this->command->warn('  Skipped announcements: no classes or grade levels to target.');

            return;
        }

        $made = 0;

        // ── School-wide, live ────────────────────────────────────────────────
        $made += $this->make($admin, [
            'title'        => 'Term opens Monday — reporting times',
            'body'         => "The new term begins on Monday. Gates open at 06:45 and assembly starts at 07:15 sharp.\n\nPlease make sure your child arrives in full uniform with all required books. The bookshop will be open on Saturday morning for anyone still needing supplies.",
            'audience'     => Announcement::AUDIENCE_ALL,
            'published_at' => now()->subDays(2),
        ]);

        $made += $this->make($admin, [
            'title'        => 'Fee deadline reminder',
            'body'         => "A reminder that term fees fall due at the end of this month. Payment details and your reference number are on the Fees page of the parent portal.\n\nIf you need to arrange a payment plan, please speak to the bursar's office before the deadline rather than after it.",
            'audience'     => Announcement::AUDIENCE_ALL,
            'published_at' => now()->subDay(),
            'expires_at'   => now()->addWeeks(3),
        ]);

        // ── Targeted at specific classes ─────────────────────────────────────
        $targetClasses = $classes->take(2);

        if ($targetClasses->isNotEmpty()) {
            $made += $this->make($admin, [
                'title'        => 'Science practical — bring lab coats',
                'body'         => "Practical sessions start this week. Every learner needs a lab coat and closed shoes; anyone without them will not be allowed into the laboratory for safety reasons.\n\nSpare coats are available from the science department on loan.",
                'audience'     => Announcement::AUDIENCE_CLASS,
                'published_at' => now()->subHours(6),
            ], $targetClasses->map(fn (SchoolClass $c) => [SchoolClass::class, $c->class_id])->all());
        }

        // ── Targeted at a whole grade level ──────────────────────────────────
        $level = $levels->firstWhere('order', 12) ?? $levels->last();

        $made += $this->make($admin, [
            'title'        => "{$level->name} parents' evening",
            'body'         => "A parents' evening for {$level->name} will be held next Thursday from 16:00 in the main hall.\n\nSubject teachers will be available to discuss progress ahead of the examinations. No appointment is needed.",
            'audience'     => Announcement::AUDIENCE_GRADE_LEVEL,
            'published_at' => now()->subHours(3),
        ], [[GradeLevel::class, $level->grade_level_id]]);

        // ── Expired, to prove it drops off the feeds ─────────────────────────
        $made += $this->make($admin, [
            'title'        => 'Sports day — last term',
            'body'         => 'Sports day took place last term. This notice has expired and should not appear on any family feed.',
            'audience'     => Announcement::AUDIENCE_ALL,
            'published_at' => now()->subMonths(2),
            'expires_at'   => now()->subMonth(),
        ]);

        // ── Draft, to prove it stays invisible ───────────────────────────────
        $made += $this->make($admin, [
            'title'        => 'Uniform supplier change (draft)',
            'body'         => 'Still being finalised with the supplier. Not yet published, so no family should see this.',
            'audience'     => Announcement::AUDIENCE_ALL,
            'published_at' => null,
        ]);

        $this->command->info("✔ {$made} announcements seeded (live, targeted, expired and draft).");
    }

    /**
     * @param  array<int, array{0: string, 1: int}>  $targets
     */
    private function make(?User $author, array $attributes, array $targets = []): int
    {
        if (Announcement::where('title', $attributes['title'])->exists()) {
            return 0;
        }

        $announcement = Announcement::create($attributes + ['created_by' => $author?->id]);

        foreach ($targets as [$type, $id]) {
            AnnouncementTarget::create([
                'announcement_id' => $announcement->announcement_id,
                'targetable_type' => $type,
                'targetable_id'   => $id,
            ]);
        }

        return 1;
    }
}
