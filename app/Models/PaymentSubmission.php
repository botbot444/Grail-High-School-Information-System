<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSubmission extends Model
{
    use Auditable;

    protected $primaryKey = 'submission_id';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'fee_id',
        'amount',
        'payment_method',
        'reference_number',
        'payment_date',
        'proof_path',
        'proof_original_filename',
        'notes',
        'status',
        'submitted_by',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'payment_id',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'fee_id', 'fee_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by', 'id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
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

    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque'        => 'Cheque',
            'mobile_money'  => 'Mobile Money',
            'card'          => 'Card',
            default         => ucfirst($this->payment_method),
        };
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

    /**
     * Public URL to the uploaded proof file (image or PDF).
     *
     * Deliberately not Storage::disk('public')->url() — that builds from the
     * 'public' disk's configured 'url' (env('APP_URL').'/storage'), a fixed
     * string baked in at config load. When APP_URL doesn't match the host/port
     * actually being browsed (e.g. APP_URL=http://localhost while serving on
     * 127.0.0.1:8000), every proof link breaks. asset() instead falls back to
     * the current request's own root when no root URL is forced, so the link
     * works regardless of which host/port the app is being browsed on.
     */
    public function getProofUrlAttribute(): string
    {
        return asset('storage/' . $this->proof_path);
    }

    public function getProofIsImageAttribute(): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', $this->proof_path);
    }
}
