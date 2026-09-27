<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListMember extends Model
{
    use HasFactory;

    protected $table = 'list_members';

    protected $fillable = [
        'list_id',
        'user_id',
        'role',
    ];

    /**
     * Relationship to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship to List/Project
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ProjectList::class, 'list_id');
    }
}
