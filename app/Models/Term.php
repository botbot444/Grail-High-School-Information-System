<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Term extends Model
{
    protected $table = 'terms';
    protected $primaryKey = 'term_id';

    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'year_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'term_id', 'term_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'term_id', 'term_id');
    }

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class, 'term_id', 'term_id');
    }

    /** Scope: only the row flagged is_current. */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * The term whose start/end window contains today, or null.
     * (Used by attendance % calculations and quick term lookups.)
     */
    public static function current(): ?self
    {
        return static::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * Number of school days (Mon-Fri, excluding holidays) in this term.
     */
    public function getSchoolDaysAttribute(): int
    {
        $holidayDates = optional($this->academicYear)
            ? $this->academicYear->holidays()
                ->whereBetween('date', [$this->start_date, $this->end_date])
                ->pluck('date')
                ->map(fn ($d) => $d->format('Y-m-d'))
                ->toArray()
            : [];

        $schoolDays = 0;
        for ($day = Carbon::parse($this->start_date); $day <= $this->end_date; $day->addDay()) {
            // Skip weekends (0 = Sunday, 6 = Saturday).
            if ($day->dayOfWeek === 0 || $day->dayOfWeek === 6) {
                continue;
            }
            if (in_array($day->format('Y-m-d'), $holidayDates)) {
                continue;
            }
            $schoolDays++;
        }

        return $schoolDays;
    }
}
