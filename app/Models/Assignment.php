<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'Draft';

    public const STATUS_PUBLISHED = 'Published';

    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'class_subject_id',
        'term_id',
        'title',
        'instructions',
        'status',
        'published_at',
        'due_at',
        'max_score',
        'allows_file_upload',
        'created_by',
    ];

    protected $casts = [
        'published_at'       => 'datetime',
        'due_at'             => 'datetime',
        'max_score'          => 'decimal:2',
        'allows_file_upload' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class, 'class_subject_id', 'class_subject_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id', 'term_id');
    }

    public function createdByTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'created_by', 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'assignment_id', 'assignment_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /** Assignments set for any subject taught to the given class. */
    public function scopeForClass(Builder $query, int $classId): Builder
    {
        return $query->whereHas('classSubject', fn ($q) => $q->where('class_id', $classId));
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->whereHas('classSubject', fn ($q) => $q->where('teacher_id', $teacherId));
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->published()->where('due_at', '<', now());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isPastDue(): bool
    {
        return $this->due_at !== null && $this->due_at->isPast();
    }

    /**
     * This assignment's state for one student: Graded, Submitted, Overdue or Pending.
     * Pass an already-loaded submission to avoid a query per row in a list.
     */
    public function statusForStudent(?AssignmentSubmission $submission): string
    {
        if ($submission?->graded_at) {
            return 'Graded';
        }

        if ($submission?->submitted_at) {
            return 'Submitted';
        }

        return $this->isPastDue() ? 'Overdue' : 'Pending';
    }
}
