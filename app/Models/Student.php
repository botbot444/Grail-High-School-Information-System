<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    protected $primaryKey = 'student_id';

    public const STATUS_ENROLLED = 'Enrolled';

    public const STATUS_GRADUATED = 'Graduated';

    public const STATUS_TRANSFERRED = 'Transferred';

    public const STATUS_WITHDRAWN = 'Withdrawn';

    public const STATUSES = [
        self::STATUS_ENROLLED    => 'Enrolled',
        self::STATUS_GRADUATED   => 'Graduated',
        self::STATUS_TRANSFERRED => 'Transferred',
        self::STATUS_WITHDRAWN   => 'Withdrawn',
    ];

    protected $fillable = [
        'user_id',
        'parent_user_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'student_number',
        'class_id',
        'guardian_name',
        'guardian_phone',
        'enrolment_date',
        'status',
        'graduated_on',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'enrolment_date'  => 'date',
        'graduated_on'    => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Student's own user / login account */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Parent's user / login account */
    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    /** Current class enrolment */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'class_id');
    }

    /** Parent/guardian login account (for notifications) */
    public function guardian()
    {
        return $this->belongsTo(User::class, 'parent_user_id', 'id');
    }

    /** All grade records for this student */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id', 'student_id');
    }

    /** All attendance records for this student */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id', 'student_id');
    }

    /** All fee records for this student */
    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'student_id', 'student_id');
    }

    /** Assignment submissions made by this student */
    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id', 'student_id');
    }

    /** Termly report cards (Phase 11) */
    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class, 'student_id', 'student_id');
    }

    /** Promotion history (Phase 6) */
    public function promotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'student_id', 'student_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Filter to students in a specific class */
    public function scopeInClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /** Currently enrolled — excludes graduates, transfers and withdrawals. */
    public function scopeEnrolled($query)
    {
        return $query->where('status', self::STATUS_ENROLLED);
    }

    public function scopeGraduated($query)
    {
        return $query->where('status', self::STATUS_GRADUATED);
    }

    public function isEnrolled(): bool
    {
        return $this->status === self::STATUS_ENROLLED;
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
