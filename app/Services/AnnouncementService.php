<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Phase 5 — audience resolution and read tracking.
 *
 * Kept out of the controllers because the same "who does this reach" question
 * is asked from three places: the admin preview before publishing, the admin
 * list afterwards, and the unread badge on each portal.
 */
class AnnouncementService
{
    /**
     * The students an announcement reaches, given an audience and its targets.
     *
     * Works on unsaved input too, which is what makes the "who will see this?"
     * preview possible before the admin commits.
     *
     * @param  array<int>  $classIds
     * @param  array<int>  $gradeLevelIds
     */
    public function audienceFor(string $audience, array $classIds = [], array $gradeLevelIds = []): EloquentCollection
    {
        $query = Student::query()->with('schoolClass');

        return match ($audience) {
            Announcement::AUDIENCE_ALL => $query->get(),

            Announcement::AUDIENCE_CLASS => $classIds === []
                ? new EloquentCollection()
                : $query->whereIn('class_id', $classIds)->get(),

            Announcement::AUDIENCE_GRADE_LEVEL => $gradeLevelIds === []
                ? new EloquentCollection()
                : $query->whereIn(
                    'class_id',
                    SchoolClass::whereIn('grade_level_id', $gradeLevelIds)->pluck('class_id')
                )->get(),

            default => new EloquentCollection(),
        };
    }

    /**
     * A one-line summary of reach: how many students, and how many parent
     * accounts will see it alongside them.
     *
     * @return array{students: int, parents: int, classes: Collection<int, string>}
     */
    public function reachSummary(string $audience, array $classIds = [], array $gradeLevelIds = []): array
    {
        $students = $this->audienceFor($audience, $classIds, $gradeLevelIds);

        return [
            'students' => $students->count(),
            'parents'  => $students->pluck('parent_user_id')->filter()->unique()->count(),
            'classes'  => $students
                ->pluck('schoolClass.class_name')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
        ];
    }

    /** Same summary, for an announcement that already exists. */
    public function reachFor(Announcement $announcement): array
    {
        $announcement->loadMissing('targets');

        return $this->reachSummary(
            $announcement->audience,
            $this->targetIds($announcement, SchoolClass::class),
            $this->targetIds($announcement, GradeLevel::class),
        );
    }

    /** @return array<int> */
    public function targetIds(Announcement $announcement, string $type): array
    {
        return $announcement->targets
            ->where('targetable_type', $type)
            ->pluck('targetable_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    // ── Read tracking ─────────────────────────────────────────────────────────

    /** Idempotent: marking an already-read announcement changes nothing. */
    public function markRead(Announcement $announcement, User $user): void
    {
        AnnouncementRead::firstOrCreate(
            [
                'announcement_id' => $announcement->announcement_id,
                'user_id'         => $user->id,
            ],
            ['read_at' => now()]
        );
    }

    /** Marks every announcement currently visible to the user as read. */
    public function markAllRead(User $user): int
    {
        $unread = Announcement::visibleTo($user)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
            ->get();

        foreach ($unread as $announcement) {
            $this->markRead($announcement, $user);
        }

        return $unread->count();
    }

    /** Unread count for the sidebar badge. */
    public function unreadCount(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return Announcement::visibleTo($user)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
            ->count();
    }

    /** The user's feed, newest first, with read state attached. */
    public function feedFor(User $user, int $limit = 50): Collection
    {
        return Announcement::visibleTo($user)
            ->with(['targets.targetable', 'author'])
            ->withExists(['reads as is_read' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('published_at')
            ->take($limit)
            ->get();
    }
}
