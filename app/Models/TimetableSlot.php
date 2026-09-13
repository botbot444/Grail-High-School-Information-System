<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class TimetableSlot extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'school_class_id',
        'subject_id',
        'teacher_id',
        'period_id',
        'day_of_week',
        'term_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $slot): void {
            $slot->loadMissing(['schoolClass.gradeLevel', 'period']);

            $classGradeLevelId = $slot->schoolClass?->grade_level_id;
            $periodGradeLevelId = $slot->period?->grade_level_id;

            if (! $classGradeLevelId || ! $periodGradeLevelId || (int) $classGradeLevelId !== (int) $periodGradeLevelId) {
                throw ValidationException::withMessages([
                    'period_id' => 'The selected period does not belong to the class grade level.',
                ]);
            }

            if ($slot->period->is_break && ($slot->subject_id || $slot->teacher_id)) {
                throw ValidationException::withMessages([
                    'subject_id' => 'Break periods cannot have a subject or teacher assigned.',
                ]);
            }

            if ($slot->teacher_id) {
                $conflict = static::query()
                    ->where('term_id', $slot->term_id)
                    ->where('day_of_week', $slot->day_of_week)
                    ->where('period_id', $slot->period_id)
                    ->where('teacher_id', $slot->teacher_id)
                    ->when($slot->exists, fn ($query) => $query->where('id', '!=', $slot->getKey()))
                    ->exists();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'teacher_id' => 'This teacher is already assigned during that period and day.',
                    ]);
                }
            }
        });
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id', 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id', 'term_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return sprintf(
            '%s · %s · %s',
            $this->schoolClass?->class_name ?? 'Class',
            $this->day_of_week,
            $this->subject?->subject_name ?? ($this->period?->name ?? 'Break')
        );
    }
}