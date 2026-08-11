<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class FeeItem extends Model
{
    use Auditable;

    protected $primaryKey = 'fee_item_id';

    protected $fillable = [
        'fee_id',
        'item_name',
        'category',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'fee_id', 'fee_id');
    }
}
