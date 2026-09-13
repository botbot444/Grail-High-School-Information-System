<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One student's move within one promotion run. */
class StudentPromotion extends Model
{
    use HasFactory;

    public const PROMOTED = 'promoted';

    public const RETAINED = 'retained';

    public const GRADUATED = 'graduated';

    protected $fillable = [
        'batch_ref',
        'student_id',
        'from_class_id',
        'to_class_id',
        'outcome',
        'previous_status',
        'academic_year_id',
        'promoted_by',
        'rolled_back_at',
    ];

    protected $casts = ['rolled_back_at' => 'datetime'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id', 'class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id', 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'year_id');
    }

    public function promotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by', 'id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('rolled_back_at');
    }
}
