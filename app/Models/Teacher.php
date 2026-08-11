<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Teacher extends Model
{
    use SoftDeletes, Auditable;

    protected $primaryKey = 'teacher_id';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Linked auth user account */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Classes where this teacher is the homeroom teacher */
    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'teacher_id', 'teacher_id');
    }

    /** Class-subject assignments (i.e. subjects this teacher delivers) */
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'teacher_id', 'teacher_id');
    }

    /** Attendance records this teacher has recorded */
    public function recordedAttendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'recorded_by', 'teacher_id');
    }

    /** Grades this teacher has entered */
    public function recordedGrades(): HasMany
    {
        return $this->hasMany(Grade::class, 'recorded_by', 'teacher_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
