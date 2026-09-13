<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use Auditable, HasFactory;

    protected $primaryKey = 'grade_id';

    protected $fillable = [
        'student_id',
        'class_subject_id',
        'assessment_type',
        'score',
        'max_score',
        'term',
        'academic_year',
        'academic_year_id',
        'term_id',
        'recorded_by',
        'marks',
    ];

    protected $casts = [
        'score'         => 'decimal:2',
        'max_score'     => 'decimal:2',
        'academic_year' => 'integer',
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

    // ── Calendar Relationships ────────────────────────────────────────────────

    /** Structured academic year (Phase 3 calendar). */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'year_id');
    }

    /** Structured term (Phase 3 calendar). */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id', 'term_id');
    }

    // ── Attribute Mapping ─────────────────────────────────────────────────────

    public function getMarksAttribute()
    {
        return $this->score;
    }

    public function setMarksAttribute($value)
    {
        $this->score = $value;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Grades belonging to a term.
     *
     * Matches on term_id where it is populated, and otherwise falls back to the
     * legacy (term name + academic year) pair — rows created before Phase 3's
     * calendar landed, and anything the seeders write, carry only those.
     */
    public function scopeInTerm($query, Term $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('term_id', $term->term_id);

            if ($term->academicYear?->label) {
                $q->orWhere(fn ($legacy) => $legacy
                    ->whereNull('term_id')
                    ->where('term', $term->name)
                    ->where('academic_year', (int) $term->academicYear->label));
            }
        });
    }

    // ── Business Logic ────────────────────────────────────────────────────────

    /**
     * Validates that score does not exceed max_score.
     * Called explicitly by GradeController before saving.
     * Also enforced by Laravel validation rules in the controller.
     */
    public function validateScore(): bool
    {
        return $this->score >= 0 && $this->score <= $this->max_score;
    }

    /**
     * Calculate percentage score.
     */
    public function getPercentageAttribute(): float
    {
        if ($this->max_score == 0) {
            return 0;
        }
        return round(($this->score / $this->max_score) * 100, 2);
    }

    /**
     * Derive letter grade from percentage.
     */
    public function getLetterGradeAttribute(): string
    {
        $pct = $this->percentage;

        return match (true) {
            $pct >= 90 => 'A+',
            $pct >= 80 => 'A',
            $pct >= 75 => 'B+',
            $pct >= 70 => 'B',
            $pct >= 65 => 'C+',
            $pct >= 60 => 'C',
            $pct >= 50 => 'D',
            default    => 'F',
        };
    }

    /**
     * Remark for report card.
     */
    public function getRemarkAttribute(): string
    {
        return match ($this->letter_grade) {
            'A+', 'A' => 'Excellent',
            'B+', 'B' => 'Good',
            'C+', 'C' => 'Satisfactory',
            'D'       => 'Pass',
            default   => 'Fail',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForTerm($query, string $term, int $year)
    {
        return $query->where('term', $term)->where('academic_year', $year);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeExams($query)
    {
        return $query->where('assessment_type', 'EXAM');
    }

    public function scopeCa($query)
    {
        return $query->where('assessment_type', 'CA');
    }
}
