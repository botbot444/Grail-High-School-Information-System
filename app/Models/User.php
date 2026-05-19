<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use App\Models\ClassSubject;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password',];

    protected $hidden = ['password','remember_token',];
    //Returns key value arr(hash map)
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    // A teacher's assigned classes or subjects
    public function assignments(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'teacher_id');
    }
}
?>
