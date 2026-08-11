<?php

namespace App\Traits;

use App\Models\AuditLog;

/**
 * Auditable
 *
 * Drop this trait into any Eloquent model to automatically write an AuditLog
 * row on create, update and delete.
 *
 * Usage:
 *   $fee->audit_reason = 'Corrected exam fee';
 *   $fee->save();
 */
trait Auditable
{
    /**
     * Register the model events. Laravel calls this automatically because
     * Eloquent scans traits for a method named "boot{TraitName}".
     */
    public static function bootAuditable(): void
    {
        // UPDATE
        // The `saving` event fires BEFORE the row is written. For an *existing*
        // record it is the right moment to snapshot the current DB values
        // (getOriginal()) and the incoming dirty values (getChanges()).
        static::saving(function ($model) {
            if ($model->exists && $model->isDirty()) {
                $model->writeAudit('updated', $model->getOriginal(), $model->getChanges());
            }
        });

        // CREATE
        // During `saving` a brand-new model has no PK yet and getKey() is null.
        // `created` fires AFTER the insert, so auditable_id is reliably set.
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->getAttributes());
        });

        // DELETE
        static::deleted(function ($model) {
            // Capture the row *before* it vanishes.
            $model->writeAudit('deleted', $model->getOriginal(), null);
        });
    }

    /**
     * Persist a single audit log entry.
     */
    protected function writeAudit(
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): void {
        // Seeders / artisan commands have no HTTP request / auth user.
        if (app()->runningInConsole()) {
            $ip   = null;
            $ua   = null;
            $uid  = null;
        } else {
            $ip   = request()->ip();
            $ua   = request()->userAgent();
            $uid  = auth()->id();
        }

        AuditLog::create([
            'user_id'        => $uid,
            'auditable_type' => static::class,
            'auditable_id'   => $this->getKey(),
            'action'         => $action,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'reason'         => $this->audit_reason ?? null,
            'ip_address'     => $ip,
            'user_agent'     => $ua,
        ]);
    }
}
