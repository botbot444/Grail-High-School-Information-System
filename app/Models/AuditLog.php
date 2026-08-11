<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values'    => 'array',
        'new_values'    => 'array',
    ];

    public $timestamps = ['created_at'];
    const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                     ->when($to,   fn ($q) => $q->whereDate('created_at', '<=', $to));
    }

    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        return $query->when($userId, fn ($q) => $q->where('user_id', $userId));
    }

    public function scopeForModelType(Builder $query, ?string $model): Builder
    {
        return $query->when($model, fn ($q) => $q->where('auditable_type', $model));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('reason', 'like', $like)
              ->orWhere('auditable_type', 'like', $like);
        });
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            default   => 'gray',
        };
    }
}
