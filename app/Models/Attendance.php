<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'student_id',
        'class_subject_id',
        'date',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class, 'class_subject_id', 'class_subject_id');
    }

    public function recordedByTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'recorded_by', 'teacher_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForClass($query, int $classSubjectId)
    {
        return $query->where('class_subject_id', $classSubjectId);
    }

    public function scopeForDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'Present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'Absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'Late');
    }
}
