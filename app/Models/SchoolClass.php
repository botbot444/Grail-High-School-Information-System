<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = ['grade_level', 'section'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    // Accessor: Returns "Grade 10A"
    public function getFullNameAttribute(): string
    {
        return "Grade {$this->grade_level}{$this->section}";
    }
}
?>