<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    protected $table = 'holidays';
    protected $primaryKey = 'holiday_id';

    protected $fillable = [
        'academic_year_id',
        'date',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'year_id');
    }
}