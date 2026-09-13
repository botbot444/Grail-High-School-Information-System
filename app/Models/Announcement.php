<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Announcement extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const AUDIENCE_ALL = 'all';

    public const AUDIENCE_CLASS = 'class';

    public const AUDIENCE_GRADE_LEVEL = 'grade_level';

    public const AUDIENCES = [
        self::AUDIENCE_ALL         => 'Everyone at the school',
        self::AUDIENCE_CLASS       => 'Specific classes',
        self::AUDIENCE_GRADE_LEVEL => 'Whole grade levels',
    ];

    protected $primaryKey = 'announcement_id';

    protected $fillable = [
        'title',
        'body',
        'audience',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
        // Set by withExists() in AnnouncementService::feedFor(). SQLite returns
        // an integer for it, so cast explicitly rather than relying on loose
        // comparison in the views.
        'is_read'      => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function targets(): HasMany
    {
        return $this->hasMany(AnnouncementTarget::class, 'announcement_id', 'announcement_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class, 'announcement_id', 'announcement_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Published and not yet expired. Drafts and lapsed notices are excluded. */
    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * Announcements a given user should see.
     *
     * Resolution differs by role: a student is matched on their own class, a
     * parent on every class their children sit in. Both also see school-wide
     * notices. Everything is OR-ed into one query rather than fetched per
     * audience type, so ordering and paging stay correct.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $classIds = static::classIdsFor($user);
        $gradeLevelIds = $classIds->isEmpty()
            ? collect()
            : SchoolClass::whereIn('class_id', $classIds)
                ->whereNotNull('grade_level_id')
                ->pluck('grade_level_id')
                ->unique();

        return $query->live()->where(function (Builder $q) use ($classIds, $gradeLevelIds) {
            $q->where('audience', self::AUDIENCE_ALL);

            if ($classIds->isNotEmpty()) {
                $q->orWhere(fn (Builder $inner) => $inner
                    ->where('audience', self::AUDIENCE_CLASS)
                    ->whereHas('targets', fn ($t) => $t
                        ->where('targetable_type', SchoolClass::class)
                        ->whereIn('targetable_id', $classIds)));
            }

            if ($gradeLevelIds->isNotEmpty()) {
                $q->orWhere(fn (Builder $inner) => $inner
                    ->where('audience', self::AUDIENCE_GRADE_LEVEL)
                    ->whereHas('targets', fn ($t) => $t
                        ->where('targetable_type', GradeLevel::class)
                        ->whereIn('targetable_id', $gradeLevelIds)));
            }
        });
    }

    /**
     * The classes that place a user in an audience.
     *
     * Students: their own class. Parents: every class their children are in.
     * Anyone else (admin, teacher) gets none — they see school-wide notices
     * only, which is correct: a targeted notice is aimed at families.
     */
    public static function classIdsFor(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        if ($user->hasRole('student')) {
            return Student::where('user_id', $user->id)
                ->whereNotNull('class_id')
                ->pluck('class_id')
                ->unique();
        }

        if ($user->hasRole('parent')) {
            return Student::where('parent_user_id', $user->id)
                ->whereNotNull('class_id')
                ->pluck('class_id')
                ->unique();
        }

        return collect();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lessThanOrEqualTo(now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Draft · Scheduled · Live · Expired — what the admin list shows. */
    public function getStateAttribute(): string
    {
        if ($this->published_at === null) {
            return 'Draft';
        }

        if ($this->published_at->isFuture()) {
            return 'Scheduled';
        }

        return $this->isExpired() ? 'Expired' : 'Live';
    }

    public function getAudienceLabelAttribute(): string
    {
        return self::AUDIENCES[$this->audience] ?? $this->audience;
    }

    /** Human summary of the targets, e.g. "10A, 10B" or "Grade 10". */
    public function getTargetSummaryAttribute(): string
    {
        if ($this->audience === self::AUDIENCE_ALL) {
            return 'Whole school';
        }

        $names = $this->targets
            ->map(fn (AnnouncementTarget $target) => $target->displayName())
            ->filter()
            ->values();

        return $names->isEmpty() ? 'No targets set' : $names->implode(', ');
    }

    public function isReadBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->reads->contains(fn (AnnouncementRead $read) => $read->user_id === $user->id);
    }
}
