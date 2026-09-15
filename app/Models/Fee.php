<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fee extends Model
{
    use Auditable, HasFactory;

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
        'academic_year_id',
        'term_id',
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

    public function feeItems(): HasMany
    {
        return $this->hasMany(FeeItem::class, 'fee_id', 'fee_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'fee_id', 'fee_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'year_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id', 'term_id');
    }

    // ── Auto-Total ──────────────────────────────────────────────────────────

    /**
     * Recalculate amount_due from fee items and recompute balance + status.
     */
    public function recalculateAmountDue(): void
    {
        $this->amount_due = $this->feeItems()->sum('amount');
        $this->balance    = round($this->amount_due - $this->amount_paid, 2);
        $this->status     = $this->computeStatus();
        $this->last_updated = Carbon::now();
    }

    /**
     * On every save, if the fee already exists in DB, keep amount_due in sync
     * with its line items so the header always reflects the current total.
     */
    protected static function booted(): void
    {
        static::saving(function ($fee) {
            if ($fee->exists && $fee->relationLoaded('feeItems')) {
                $fee->recalculateAmountDue();
            }
        });
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
     * PENDING → PARTIALLY PAID → CLEARED → OVERDUE
     */
    public function computeStatus(): string
    {
        if ($this->balance <= 0) {
            return 'Cleared';
        }

        if ($this->amount_paid > 0) {
            return 'Partially Paid';
        }

        if ($this->due_date && $this->due_date->isPast() && $this->balance > 0) {
            return 'Overdue';
        }

        return 'Pending';
    }

    /**
     * Compute the status and persist it so the Auditable trait captures the change.
     */
    public function updateStatus(): void
    {
        $this->status = $this->computeStatus();
        $this->last_updated = Carbon::now();
        $this->save();
    }

    /**
     * Apply a received payment: creates the Payment row for the full amount
     * received, applies whatever fits this fee's balance via
     * recordPayment(), and carries any overage to the student's account
     * credit (Student::grantCredit()) rather than dropping it.
     *
     * Shared by the bursar's direct entry (Admin\PaymentController) and
     * admin-approved parent proof-of-payment submissions
     * (Admin\PaymentSubmissionController), so both post through the exact
     * same ledger logic.
     */
    public function applyPayment(
        float $amount,
        string $method,
        ?string $reference,
        ?string $notes,
        $date,
        ?int $recordedBy
    ): Payment {
        $applied = min($amount, (float) $this->balance);
        $overage = round($amount - $applied, 2);

        $payment = $this->payments()->create([
            'amount'           => $amount,
            'payment_method'   => $method,
            'reference_number' => $reference,
            'notes'            => $notes,
            'payment_date'     => $date,
            'recorded_by'      => $recordedBy,
        ]);

        if ($applied > 0) {
            $this->recordPayment($applied);
        }

        if ($overage > 0) {
            $this->student->grantCredit($overage, [
                'source_payment_id' => $payment->payment_id,
                'recorded_by'       => $recordedBy,
                'notes'             => "Overpayment on fee #{$this->fee_id} ({$this->term} {$this->academic_year}).",
            ]);
        }

        return $payment;
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

    // ── Payment reference ─────────────────────────────────────────────────────

    /**
     * Characters used in the reference. Deliberately excludes the pairs people
     * mistype when copying off a phone screen or a deposit slip: I/1, O/0, S/5,
     * Z/2, B/8. 26 symbols, which is the checksum modulus.
     */
    private const REF_ALPHABET = 'ACDEFGHJKLMNPQRTUVWXY34679';

    /**
     * A stable per-fee reference the parent quotes when paying, e.g.
     * GRL-0042-0117-K. Deterministic, so the same fee always yields the same
     * code and a parent can pay in instalments against one reference.
     *
     * The trailing character is a check digit — a single mistyped digit, or two
     * transposed digits, will not resolve to a different valid fee.
     */
    public function getPaymentReferenceAttribute(): string
    {
        $body = sprintf('%04d-%04d', $this->student_id, $this->fee_id);

        return 'GRL-' . $body . '-' . self::checkCharacter($body);
    }

    /**
     * Resolve a reference a bursar has typed in. Returns null when the format is
     * wrong, the check character fails, or no such fee exists.
     */
    public static function findByPaymentReference(?string $reference): ?self
    {
        if (blank($reference)) {
            return null;
        }

        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $reference));

        // GRL + 4 student digits + 4 fee digits + 1 check character
        if (! preg_match('/^GRL(\d{4})(\d{4})([A-Z0-9])$/', $clean, $m)) {
            return null;
        }

        [, $studentPart, $feePart, $check] = $m;
        $body = $studentPart . '-' . $feePart;

        if ($check !== self::checkCharacter($body)) {
            return null;
        }

        return static::where('fee_id', (int) $feePart)
            ->where('student_id', (int) $studentPart)
            ->first();
    }

    /**
     * Weighted modulus check character. Position weights make transpositions
     * (the most common typing error) change the result, which a plain digit sum
     * would not.
     */
    private static function checkCharacter(string $body): string
    {
        $digits = preg_replace('/\D/', '', $body);
        $sum = 0;

        foreach (str_split($digits) as $i => $digit) {
            $sum += ((int) $digit) * ($i + 2);
        }

        return self::REF_ALPHABET[$sum % strlen(self::REF_ALPHABET)];
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
