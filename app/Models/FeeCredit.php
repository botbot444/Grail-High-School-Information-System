<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

/**
 * One row per fee-credit ledger event — see the migration for the full
 * picture of what 'overpayment' / 'applied' / 'refunded' mean. Rows are
 * never updated after they're written (each new event is its own row), but
 * Auditable is still attached for consistency with the other money models
 * (Fee, Payment) — a deletion of a ledger row is exactly the kind of thing
 * that should never happen silently.
 */
class FeeCredit extends Model
{
    use Auditable;

    protected $primaryKey = 'credit_id';

    protected $fillable = [
        'student_id',
        'amount',
        'type',
        'source_payment_id',
        'applied_fee_id',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function sourcePayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'source_payment_id', 'payment_id');
    }

    public function appliedFee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'applied_fee_id', 'fee_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by', 'id');
    }
}
