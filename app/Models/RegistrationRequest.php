<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationRequest extends Model
{
    use Auditable;

    protected $primaryKey = 'registration_request_id';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'parent_first_name',
        'parent_last_name',
        'parent_email',
        'parent_password',
        'parent_phone',
        'parent_address',
        'parent_occupation',
        'parent_national_id',
        'child_first_name',
        'child_last_name',
        'child_date_of_birth',
        'child_gender',
        'child_email',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'created_parent_user_id',
        'created_student_id',
    ];

    protected $hidden = [
        'parent_password',
    ];

    protected $casts = [
        'child_date_of_birth' => 'date',
        'reviewed_at'         => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function createdParentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_parent_user_id', 'id');
    }

    public function createdStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'created_student_id', 'student_id');
    }

    // ── State ─────────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getParentFullNameAttribute(): string
    {
        return "{$this->parent_first_name} {$this->parent_last_name}";
    }

    public function getChildFullNameAttribute(): string
    {
        return "{$this->child_first_name} {$this->child_last_name}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default                => ucfirst($this->status),
        };
    }
}
