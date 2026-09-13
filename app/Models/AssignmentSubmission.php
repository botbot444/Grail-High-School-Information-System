<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use Auditable, HasFactory;

    protected $primaryKey = 'submission_id';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'notes',
        'file_path',
        'original_filename',
        'submitted_at',
        'score',
        'feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at'    => 'datetime',
        'score'        => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id', 'assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function gradedByTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'graded_by', 'teacher_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isLate(): bool
    {
        return $this->submitted_at !== null
            && $this->assignment?->due_at !== null
            && $this->submitted_at->greaterThan($this->assignment->due_at);
    }

    public function getPercentageAttribute(): ?float
    {
        $max = (float) ($this->assignment?->max_score ?? 0);

        if ($this->score === null || $max <= 0) {
            return null;
        }

        return round(((float) $this->score / $max) * 100, 2);
    }
}
