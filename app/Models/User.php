<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Lists owned by this user
     */
    public function ownedLists(): HasMany
    {
        return $this->hasMany(ProjectList::class, 'owner_id');
    }

    /**
     * List membership entries
     */
    public function listMembers(): HasMany
    {
        return $this->hasMany(ListMember::class, 'user_id');
    }

    /**
     * Lists where this user is a member
     */
    public function memberLists(): BelongsToMany
    {
        return $this->belongsToMany(ProjectList::class, 'list_members', 'user_id', 'list_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
