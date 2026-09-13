<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{
    use SoftDeletes, Auditable, HasFactory;

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

    /** Subjects this teacher is qualified or assigned to teach */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_subjects',
            'teacher_id',
            'subject_id',
            'teacher_id',
            'subject_id'
        )->withTimestamps();
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

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class, 'teacher_id', 'teacher_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
