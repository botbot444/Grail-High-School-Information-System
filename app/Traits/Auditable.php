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
            // Always capture any human-supplied audit reason and remove it
            // from the model attributes before the DB write. This lets callers
            // set $model->audit_reason for the audit log without causing the
            // ORM to attempt to persist a non-existent `audit_reason` column.
            self::$auditTempReasons[spl_object_id($model)] = $model->audit_reason ?? null;
            if (array_key_exists('audit_reason', $model->getAttributes())) {
                $model->offsetUnset('audit_reason');
            }

            if ($model->exists && $model->isDirty()) {
                // getDirty(), not getChanges(). getChanges() is only populated
                // AFTER a save completes, so inside `saving` it is always empty
                // — which meant every update recorded what the row used to be
                // and nothing about what it became, leaving the log viewer's
                // field-by-field diff with nothing to compare against.
                $model->writeAudit('updated', $model->getOriginal(), $model->getDirty());
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
    /**
     * Attributes that must never be written to the audit table, whatever the
     * model. An audit log is read on screen by administrators, so a password
     * hash or a session token recorded "for completeness" turns an
     * accountability feature into a credential store. A model can extend this
     * list with its own $auditExclude property.
     *
     * @var array<int, string>
     */
    protected array $auditNeverStore = ['password', 'remember_token'];

    /**
     * Temporary storage for per-model audit reasons during the saving lifecycle.
     * We store reasons here keyed by the model object's id to avoid adding
     * them to the model attributes (which would make Eloquent try to persist
     * them as columns).
     *
     * @var array<int,string|null>
     */
    protected static array $auditTempReasons = [];

    /** Strip excluded attributes from a values array before it is stored. */
    protected function redactForAudit(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $excluded = array_merge(
            $this->auditNeverStore,
            property_exists($this, 'auditExclude') ? $this->auditExclude : []
        );

        return array_diff_key($values, array_flip($excluded));
    }

    protected function writeAudit(
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): void {
        $oldValues = $this->redactForAudit($oldValues);
        $newValues = $this->redactForAudit($newValues);

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
            // Prefer an intentionally-set audit reason. We capture it from
            // the temporary storage populated in the saving hook so it is
            // available here without having been persisted to the model's
            // table.
            'reason'         => $this->audit_reason ?? (self::$auditTempReasons[spl_object_id($this)] ?? null),
            'ip_address'     => $ip,
            'user_agent'     => $ua,
        ]);
        // Clear the temporary storage entry for this model.
        $oid = spl_object_id($this);
        if (array_key_exists($oid, self::$auditTempReasons)) {
            unset(self::$auditTempReasons[$oid]);
        }
    }
}
