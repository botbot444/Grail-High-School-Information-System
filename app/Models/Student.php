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
        'credit_balance',
    ];

    protected $casts = [
        'date_of_birth'   => 'date',
        'enrolment_date'  => 'date',
        'graduated_on'    => 'date',
        'credit_balance'  => 'decimal:2',
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

    /** Ledger of overpayment credits granted / applied / refunded. */
    public function feeCredits(): HasMany
    {
        return $this->hasMany(FeeCredit::class, 'student_id', 'student_id');
    }

    // ── Fee credit (overpayment carry-forward) ──────────────────────────────

    /**
     * Grant this student a fee credit from an overpayment — a parent paid
     * more than a fee's balance, so the excess didn't just vanish from the
     * ledger. Immediately tries to apply it to whatever the student
     * currently owes (oldest due date first); whatever's left over stays as
     * a credit balance and is applied automatically the next time a fee is
     * created for this student, e.g. next term's tuition.
     *
     * @param  array{source_payment_id?: int, recorded_by?: int, notes?: string}  $attrs
     */
    public function grantCredit(float $amount, array $attrs = []): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->credit_balance = round((float) $this->credit_balance + $amount, 2);
        $this->save();

        FeeCredit::create(array_merge([
            'student_id' => $this->student_id,
            'amount'     => $amount,
            'type'       => 'overpayment',
        ], $attrs));

        $this->applyAvailableCredit($attrs['recorded_by'] ?? null);
    }

    /**
     * Apply whatever credit this student has toward their outstanding fees,
     * oldest due date first, until the credit or the debt runs out. Safe to
     * call any time — right after a credit is granted, or right after a new
     * fee is created — since it's a no-op when there's no credit or nothing
     * owed. Each application is recorded as a real Payment (method
     * 'credit'), not just a silent balance nudge, so the parent and the
     * bursar both see where the money came from, same as any other
     * payment method.
     */
    public function applyAvailableCredit(?int $recordedBy = null): void
    {
        if ((float) $this->credit_balance <= 0) {
            return;
        }

        $fees = $this->fees()->where('balance', '>', 0)->orderBy('due_date')->get();

        foreach ($fees as $fee) {
            if ((float) $this->credit_balance <= 0) {
                break;
            }

            $applied = round(min((float) $this->credit_balance, (float) $fee->balance), 2);

            if ($applied <= 0) {
                continue;
            }

            $fee->payments()->create([
                'amount'           => $applied,
                'payment_method'   => 'credit',
                'reference_number' => null,
                'notes'            => 'Applied from account credit (overpayment carried forward).',
                'payment_date'     => now(),
                'recorded_by'      => $recordedBy,
            ]);

            $fee->recordPayment($applied);

            FeeCredit::create([
                'student_id'     => $this->student_id,
                'amount'         => -$applied,
                'type'           => 'applied',
                'applied_fee_id' => $fee->fee_id,
                'recorded_by'    => $recordedBy,
            ]);

            $this->credit_balance = round((float) $this->credit_balance - $applied, 2);
            $this->save();
        }
    }

    /**
     * Manual, logged exception to carry-forward: for a student leaving the
     * school (withdrawn/graduated) with credit still on the books and no
     * future fee to apply it to. This doesn't move any money itself — the
     * school still has to actually pay the parent back outside the system
     * (bank transfer, cash) — it only records that it happened, the same
     * way the rest of Grail's payment flow is a reconciliation record, not
     * a payment gateway.
     */
    public function refundCredit(float $amount, string $notes, ?int $recordedBy = null): void
    {
        if ($amount <= 0 || $amount > (float) $this->credit_balance) {
            throw new \InvalidArgumentException(
                'Refund amount must be > 0 and no more than the available credit.'
            );
        }

        FeeCredit::create([
            'student_id'  => $this->student_id,
            'amount'      => -$amount,
            'type'        => 'refunded',
            'notes'       => $notes,
            'recorded_by' => $recordedBy,
        ]);

        $this->credit_balance = round((float) $this->credit_balance - $amount, 2);
        $this->save();
    }

    /**
     * Next student number for the current year, e.g. "2026/0017" — the same
     * `{year}/{seq}` shape StudentSeeder has always used. Admin used to type
     * this in by hand; callers should retry Student::create() on a unique-
     * constraint violation using a freshly generated number rather than
     * relying on this alone to be race-proof — cheap, not bulletproof, which
     * is all a human-facing ID like this needs.
     */
    public static function nextStudentNumber(): string
    {
        $prefix = now()->year . '/';

        $last = static::withTrashed()
            ->where('student_number', 'like', $prefix . '%')
            ->orderByDesc('student_number')
            ->value('student_number');

        $next = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a Student with an auto-generated student number, retrying with a
     * fresh one on a rare collision instead of failing outright. Wraps
     * Student::create() — every other attribute is passed through as-is.
     */
    public static function createWithGeneratedNumber(array $attributes): self
    {
        for ($attempt = 1; ; $attempt++) {
            $attributes['student_number'] = static::nextStudentNumber();

            try {
                return static::create($attributes);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($attempt >= 3 || ! str_contains($e->getMessage(), 'student_number')) {
                    throw $e;
                }
            }
        }
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

    /**
     * Average percentage across this student's EXAM grades (all subjects,
     * all terms currently loaded on `grades`). Shared by the admin and
     * teacher student-profile views so both show the same number.
     */
    public function averageExamPercentage(): ?float
    {
        $scores = $this->grades
            ->where('assessment_type', 'EXAM')
            ->map(fn (Grade $grade) => $grade->percentage)
            ->filter(fn ($score) => $score !== null);

        return $scores->isNotEmpty() ? round($scores->avg(), 1) : null;
    }

    /** Letter grade for {@see averageExamPercentage()}, or null if ungraded. */
    public function averageExamLetterGrade(): ?string
    {
        $pct = $this->averageExamPercentage();

        if ($pct === null) {
            return null;
        }

        return match (true) {
            $pct >= 90 => 'A+', $pct >= 80 => 'A', $pct >= 75 => 'B+',
            $pct >= 70 => 'B', $pct >= 65 => 'C+', $pct >= 60 => 'C',
            $pct >= 50 => 'D', default => 'F',
        };
    }

    /**
     * Overall attendance rate (Present + Late) / total recorded sessions,
     * from whatever `attendance` records are currently loaded. Null when
     * there are no records yet, so views can show "—" instead of "0%".
     */
    public function attendanceRate(): ?float
    {
        $total = $this->attendance->count();

        if ($total === 0) {
            return null;
        }

        $attended = $this->attendance->whereIn('status', ['Present', 'Late'])->count();

        return round(($attended / $total) * 100, 1);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
