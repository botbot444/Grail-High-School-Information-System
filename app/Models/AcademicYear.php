<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $table = 'academic_years';
    protected $primaryKey = 'year_id';

    protected $fillable = [
        'label',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
        'is_current' => 'boolean',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class, 'academic_year_id', 'year_id');
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class, 'academic_year_id', 'year_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'academic_year_id', 'year_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'academic_year_id', 'year_id');
    }

    /** Scope: only the row flagged as current. */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * The academic year marked is_current, or null if none is.
     */
    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }

    /**
     * Flag this year as current and un-flag every other year.
     */
    public function setAsCurrent(): void
    {
        static::where('is_current', true)->update(['is_current' => false]);
        $this->is_current = true;
        $this->save();
    }
}