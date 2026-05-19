<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentProfile extends Model
{
    use SoftDeletes;

    /**
     * Named ParentProfile to avoid collision with PHP's reserved word 'Parent'.
     * Table is still 'parents'.
     */
    protected $table = 'parents';

    protected $primaryKey = 'parent_id';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'occupation',
        'national_id',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Linked auth / login account */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Students linked to this parent via parent_user_id on the students table.
     * A parent may have more than one child enrolled.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_user_id', 'user_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
