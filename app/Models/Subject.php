<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
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
}
