<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    //table name defined in the migration
    protected $table = 'attendance';

    protected $fillable = ['student_id','date','status','remarks'];

    //Attendance belongs to a specific student If the student is deleted, this record is cascaded.
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    //Scope: Helps quickly filter attendance for today's roll call. Usage: Attendance::today()->get();
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }
}