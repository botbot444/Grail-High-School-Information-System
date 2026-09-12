<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class GradeLevel extends Model
{
    protected $table = 'grade_levels';
    protected $primaryKey = 'grade_level_id';

    protected $fillable = [
        'name',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /** Classes that belong to this grade level. */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'grade_level_id', 'grade_level_id');
    }

    /** Every student enrolled in a class of this grade level. */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            SchoolClass::class,
            'grade_level_id',  // foreign key on school_classes...
            'class_id',        // foreign key on students
            'grade_level_id',  // local key on grade_levels
            'class_id'         // local key on school_classes
        );
    }
}