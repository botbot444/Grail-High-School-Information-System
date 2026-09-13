<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_level_id',
        'name',
        'start_time',
        'end_time',
        'order',
        'is_break',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'order' => 'integer',
        'is_break' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $period): void {
            $start = self::minutes($period->start_time);
            $end = self::minutes($period->end_time);

            if ($start >= $end) {
                throw ValidationException::withMessages([
                    'end_time' => 'The end time must be after the start time.',
                ]);
            }

            $overlaps = static::query()
                ->where('grade_level_id', $period->grade_level_id)
                ->when($period->exists, fn ($query) => $query->where('id', '!=', $period->getKey()))
                ->get(['start_time', 'end_time']);

            foreach ($overlaps as $existing) {
                $existingStart = self::minutes($existing->start_time);
                $existingEnd = self::minutes($existing->end_time);

                if ($start < $existingEnd && $end > $existingStart) {
                    throw ValidationException::withMessages([
                        'start_time' => 'This period overlaps another period in the same grade level.',
                    ]);
                }
            }
        });
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id', 'grade_level_id');
    }

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class);
    }

    private static function minutes(mixed $value): int
    {
        if ($value instanceof \DateTimeInterface) {
            return ((int) $value->format('H') * 60) + (int) $value->format('i');
        }

        [$hours, $minutes] = array_map('intval', explode(':', (string) $value));
        return ($hours * 60) + $minutes;
    }
}