<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One class or grade level an announcement is aimed at.
 * An announcement has as many of these as it names.
 */
class AnnouncementTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'targetable_type',
        'targetable_id',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id', 'announcement_id');
    }

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }

    /** "10A" or "Grade 10", whichever kind of target this is. */
    public function displayName(): ?string
    {
        $target = $this->targetable;

        return match (true) {
            $target instanceof SchoolClass => $target->class_name,
            $target instanceof GradeLevel  => $target->name,
            default                        => null,
        };
    }
}
