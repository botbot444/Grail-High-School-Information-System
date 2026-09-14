<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Role;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'role_id', 'email_verified_at', 'must_change_password', 'is_active'];

    /**
     * Default for brand-new instances, not just the DB column default.
     *
     * Model::create() builds its INSERT from the in-memory $attributes it
     * already has — it never re-fetches the row afterward — so a factory or
     * controller call that never mentions is_active would otherwise leave
     * the in-memory model with no is_active attribute at all (accessing it
     * returns null, which EnsureAccountIsActive reads as "deactivated").
     * Setting the default here means every new User starts active in PHP,
     * not only in the database.
     */
    protected $attributes = [
        'is_active' => true,
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_change_password' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->roleModel?->name ?? $this->attributes['role'] ?? null;
    }

    public function hasRole(string $roleName): bool
    {
        return strcasecmp($this->role_name ?? '', $roleName) === 0;
    }

    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

        public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    /** The parent profile record (table: parents, FK: user_id). */
    public function parent(): HasOne
    {
        return $this->hasOne(ParentProfile::class, 'user_id');
    }

    /** Alias kept for clarity in parent-facing code. */
    public function parentProfile(): HasOne
    {
        return $this->parent();
    }

    /** Students this user is the guardian of (parent_user_id = this user's id). */
    public function children(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_user_id', 'id');
    }



    // A teacher's assigned classes or subjects
    public function assignments(): HasManyThrough
    {
        return $this->hasManyThrough(
            ClassSubject::class,
            Teacher::class,
            'user_id',
            'teacher_id',
            'id',
            'teacher_id'
        );
    }
}
?>
