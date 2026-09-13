<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Where a class's students go at year-end: 10A → 11A, or out of the school. */
class PromotionMapping extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'from_class_id',
        'to_class_id',
        'graduates',
    ];

    protected $casts = ['graduates' => 'boolean'];

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id', 'class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id', 'class_id');
    }
}
