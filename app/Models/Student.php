<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'student_id';

    protected $fillable = [
        'user_id',
        'parent_user_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'student_number',
        'class_id',
        'guardian_name',
        'guardian_phone',
        'enrolment_date',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'enrolment_date'  => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Student's own user / login account */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Parent's user / login account */
    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    /** Current class enrolment */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'class_id');
    }

    /** All grade records for this student */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id', 'student_id');
    }

    /** All attendance records for this student */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id', 'student_id');
    }

    /** All fee records for this student */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'student_id', 'student_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Filter to students in a specific class */
    public function scopeInClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
