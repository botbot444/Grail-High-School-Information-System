<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Fee extends Model
{
    protected $primaryKey = 'fee_id';

    protected $fillable = [
        'student_id',
        'description',
        'amount_due',
        'amount_paid',
        'balance',
        'due_date',
        'status',
        'term',
        'academic_year',
        'last_updated',
    ];

    protected $casts = [
        'amount_due'   => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'balance'      => 'decimal:2',
        'due_date'     => 'date',
        'last_updated' => 'datetime',
        'academic_year'=> 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    // ── State Machine (FR-11) ─────────────────────────────────────────────────

    /**
     * Record a payment and transition to the correct status.
     * Status is always computed — never set manually.
     *
     * @param  float  $amount  Amount being paid now (must be > 0)
     * @throws \InvalidArgumentException
     */
    public function recordPayment(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $this->amount_paid  = round($this->amount_paid + $amount, 2);
        $this->balance      = round($this->amount_due - $this->amount_paid, 2);
        $this->last_updated = Carbon::now();

        $this->status = $this->computeStatus();
        $this->save();
    }

    /**
     * Reverse a payment (admin error correction).
     * Recalculates balance and status from scratch.
     *
     * @param  float  $amount  Amount to reverse (must be > 0 and ≤ amount_paid)
     */
    public function reversePayment(float $amount): void
    {
        if ($amount <= 0 || $amount > $this->amount_paid) {
            throw new \InvalidArgumentException(
                'Reversal amount must be > 0 and ≤ amount already paid.'
            );
        }

        $this->amount_paid  = round($this->amount_paid - $amount, 2);
        $this->balance      = round($this->amount_due - $this->amount_paid, 2);
        $this->last_updated = Carbon::now();

        $this->status = $this->computeStatus();
        $this->save();
    }

    /**
     * Derive the correct status from the current figures.
     * PENDING → PARTIALLY PAID → CLEARED
     */
    public function computeStatus(): string
    {
        if ($this->amount_paid <= 0) {
            return 'Pending';
        }

        if ($this->balance <= 0) {
            return 'Cleared';
        }

        return 'Partially Paid';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'Partially Paid');
    }

    public function scopeCleared($query)
    {
        return $query->where('status', 'Cleared');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::today())
                     ->whereIn('status', ['Pending', 'Partially Paid']);
    }

    public function scopeForTerm($query, string $term, int $year)
    {
        return $query->where('term', $term)->where('academic_year', $year);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date->isPast() && $this->status !== 'Cleared';
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->amount_due == 0) {
            return 100.0;
        }
        return round(($this->amount_paid / $this->amount_due) * 100, 1);
    }
}
