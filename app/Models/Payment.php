<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class Payment extends Model
{
    use Auditable;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'fee_id',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'payment_date',
        'recorded_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'fee_id', 'fee_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by', 'id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFormattedAmountAttribute(): string
    {
        return 'ZMW ' . number_format($this->amount, 2);
    }

    public function getIsCashAttribute(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'         => 'Cash',
            'bank_transfer'=> 'Bank Transfer',
            'cheque'       => 'Cheque',
            'mobile_money' => 'Mobile Money',
            'card'         => 'Card',
            default        => ucfirst($this->payment_method),
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        return $query->when($from, fn ($q) => $q->whereDate('payment_date', '>=', $from))
                     ->when($to,   fn ($q) => $q->whereDate('payment_date', '<=', $to));
    }

    public function scopeMethod($query, ?string $method)
    {
        return $query->when($method, fn ($q) => $q->where('payment_method', $method));
    }
}
