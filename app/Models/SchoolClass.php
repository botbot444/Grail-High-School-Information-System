<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'class_id';

    protected $fillable = [
        'class_name',
        'grade_level',
        'teacher_id',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Homeroom / class teacher */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    /** Students enrolled in this class */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id', 'class_id');
    }

    /** Subjects taught in this class (via pivot) */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'class_subjects',
            'class_id',
            'subject_id',
            'class_id',
            'subject_id'
        )->withPivot('teacher_id', 'class_subject_id')->withTimestamps();
    }

    /** ClassSubject pivot records for this class */
    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'class_id', 'class_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

        /** Full display name, e.g. "10A – Grade 10" */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->class_name} – {$this->grade_level}";
    }

    // ── Calendar Relationships ────────────────────────────────────────────────

    /**
     * Structured grade level for this class (Phase 3 calendar).
     * Backward compatible: grade_level_id is nullable and populated from the
     * legacy textual grade_level column during the data migration.
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id', 'grade_level_id');
    }

    /**
     * Resolved grade level name — prefers the structured relation when linked,
     * falling back to the legacy grade_level column for unmigrated classes.
     */
    public function getGradeLevelNameAttribute(): string
    {
        return $this->gradeLevel ? $this->gradeLevel->name : $this->grade_level;
    }
}
