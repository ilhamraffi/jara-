<?php

namespace App\Models;

<<<<<<< HEAD
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
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
>>>>>>> origin/feat/p1-auth-admin
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
<<<<<<< HEAD
            'email_verified_at' => 'datetime',
=======
>>>>>>> origin/feat/p1-auth-admin
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
<<<<<<< HEAD
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
     * Check if user has admin role
=======
     * Check if user is an admin.
>>>>>>> origin/feat/p1-auth-admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
<<<<<<< HEAD
=======

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
>>>>>>> origin/feat/p1-auth-admin
}
