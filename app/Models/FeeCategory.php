<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function feeItems(): HasMany
    {
        return $this->hasMany(FeeItem::class, 'category', 'slug');
    }

    /**
     * Only categories that are actually referenced by at least one fee item.
     */
    public function scopeInUse($query)
    {
        return $query->whereHas('feeItems');
    }

    /**
     * Auto-generate the slug from the name on boot.
     */
    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (blank($category->slug)) {
                $category->slug = str($category->name)->slug()->__toString();
            }
        });
    }
}
