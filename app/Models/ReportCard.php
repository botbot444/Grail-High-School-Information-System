<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends Model
{
    use Auditable, HasFactory;

    protected $primaryKey = 'report_card_id';

    protected $fillable = [
        'student_id',
        'term_id',
        'class_id',
        'term_average',
        'class_rank',
        'class_size',
        'class_teacher_comment',
        'finalized_at',
        'finalized_by',
        'audit_reason',
    ];

    protected $casts = [
        'term_average' => 'decimal:2',
        'class_rank'   => 'integer',
        'class_size'   => 'integer',
        'finalized_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id', 'term_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'class_id');
    }

    public function finalizedByTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'finalized_by', 'teacher_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Only finalized cards are visible to parents and students. */
    public function scopeFinalized(Builder $query): Builder
    {
        return $query->whereNotNull('finalized_at');
    }

    public function scopeForTerm(Builder $query, int $termId): Builder
    {
        return $query->where('term_id', $termId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }

    /** "3rd of 28", or null while still a draft. */
    public function getRankLabelAttribute(): ?string
    {
        if ($this->class_rank === null) {
            return null;
        }

        $suffix = match (true) {
            in_array($this->class_rank % 100, [11, 12, 13], true) => 'th',
            $this->class_rank % 10 === 1 => 'st',
            $this->class_rank % 10 === 2 => 'nd',
            $this->class_rank % 10 === 3 => 'rd',
            default => 'th',
        };

        return $this->class_size
            ? "{$this->class_rank}{$suffix} of {$this->class_size}"
            : "{$this->class_rank}{$suffix}";
    }
}
