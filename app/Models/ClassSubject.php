<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents the assignment of a Subject to a SchoolClass, taught by a Teacher.
 * Used as a first-class model (not just a pivot) because Grades and Attendance
 * both reference it directly via class_subject_id.
 */
class ClassSubject extends Model
{
    protected $primaryKey = 'class_subject_id';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'class_subject_id', 'class_subject_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_subject_id', 'class_subject_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** e.g. "10A – Mathematics" */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->schoolClass->class_name} – {$this->subject->subject_name}";
    }
}
