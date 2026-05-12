<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = ['student_id','class_subject_id','term','mark','type','teacher_comment'];

    // A grade cannot exist without a student.
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // Class-Subject-Teacher assignment.
    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    //grading scale. improve later
    public function getLetterAttribute(): string
    {
        if ($this->mark >= 75) return 'Distinction';
        if ($this->mark >= 60) return 'Merit';
        if ($this->mark >= 40) return 'Pass';
        return 'Fail';
    }
}