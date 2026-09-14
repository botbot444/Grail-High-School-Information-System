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

    /**
     * Phase 12 — field-by-field diff for the log viewer.
     *
     * The raw old/new JSON is unreadable in a table, and most of it is noise:
     * timestamps and unchanged columns that Eloquent includes anyway. This
     * returns only fields whose value actually moved.
     *
     * @return array<int, array{field: string, from: ?string, to: ?string}>
     */
    public function getChangeSummaryAttribute(): array
    {
        $ignored = ['created_at', 'updated_at', 'deleted_at', 'remember_token', 'password', 'last_updated'];

        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        $fields = collect(array_keys($old + $new))
            ->reject(fn ($field) => in_array($field, $ignored, true))
            ->values();

        $changes = [];

        foreach ($fields as $field) {
            $before = $old[$field] ?? null;
            $after  = $new[$field] ?? null;

            // On an update Eloquent hands us every attribute, not just the dirty
            // ones — skip anything that did not actually move.
            if ($this->action === 'updated' && $this->stringify($before) === $this->stringify($after)) {
                continue;
            }

            $changes[] = [
                'field' => ucfirst(str_replace('_', ' ', $field)),
                'from'  => $this->stringify($before),
                'to'    => $this->stringify($after),
            ];
        }

        return $changes;
    }

    /** Render a stored value for display, keeping it short enough for a table cell. */
    private function stringify(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        $text = (string) $value;

        return mb_strlen($text) > 60 ? mb_substr($text, 0, 57) . '…' : $text;
    }

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
