<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $primaryKey = 'subject_id';

    protected $fillable = [
        'subject_name',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Classes that offer this subject */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'class_subjects',
            'subject_id',
            'class_id',
            'subject_id',
            'class_id'
        )->withPivot('teacher_id', 'class_subject_id')->withTimestamps();
    }

    /** ClassSubject pivot records for this subject */
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'subject_id', 'subject_id');
    }

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class, 'subject_id', 'subject_id');
    }

    /** Teachers assigned to this subject independently of a class */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            Teacher::class,
            'teacher_subjects',
            'subject_id',
            'teacher_id',
            'subject_id',
            'teacher_id'
        )->withTimestamps();
    }
}
