<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name','subject_code','category'];

    // Get all classes where this subject is taught.
    public function classAssignments(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }
}