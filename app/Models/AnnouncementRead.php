<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Marks one announcement as read by one user. No row means unread. */
class AnnouncementRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'user_id',
        'read_at',
    ];

    protected $casts = ['read_at' => 'datetime'];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id', 'announcement_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
