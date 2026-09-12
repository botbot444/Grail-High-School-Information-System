<?php

namespace App\Helpers;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Support\Carbon;

class CalendarHelper
{
    /**
     * The academic year currently flagged is_current.
     */
    public static function getCurrentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::current();
    }

    /**
     * The term whose start/end window contains today.
     */
    public static function getCurrentTerm(): ?Term
    {
        return Term::current();
    }

    /**
     * Count school days (Mon-Fri, excluding holidays) between two dates.
     * If an academic year is supplied, its holidays are subtracted; otherwise
     * only weekends are skipped.
     *
     * @param  string|\DateTimeInterface  $startDate
     * @param  string|\DateTimeInterface  $endDate
     * @param  int|null                   $academicYearId  (uses year_id)
     */
    public static function getSchoolDays($startDate, $endDate, ?int $academicYearId = null): int
    {
        $holidayDates = [];

        if ($academicYearId) {
            $holidayDates = AcademicYear::find($academicYearId)
                ?->holidays()
                ->whereBetween('date', [$startDate, $endDate])
                ->pluck('date')
                ->map(fn ($d) => $d->format('Y-m-d'))
                ->toArray() ?? [];
        }

        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        $days = 0;
        for ($day = $start; $day <= $end; $day->addDay()) {
            if ($day->dayOfWeek === 0 || $day->dayOfWeek === 6) {
                continue;
            }
            if (in_array($day->format('Y-m-d'), $holidayDates)) {
                continue;
            }
            $days++;
        }

        return $days;
    }
}