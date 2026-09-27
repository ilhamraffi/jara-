<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'owner_id',
        'name',
        'description',
    ];

    /**
     * Relationship to Owner (User)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relationship to ListMember records
     */
    public function members(): HasMany
    {
        return $this->hasMany(ListMember::class, 'list_id');
    }

    /**
     * Many-to-many relationship with users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_members', 'list_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Relationship to Tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'list_id');
    }

    /**
     * Check if a given user is the owner of this list
     */
    public function isOwner(int $userId): bool
    {
        if ((int) $this->owner_id === (int) $userId) {
            return true;
        }

        return $this->members()
            ->where('user_id', $userId)
            ->where('role', 'owner')
            ->exists();
    }

    /**
     * Check if a given user is a member or owner of this list
     */
    public function isMember(int $userId): bool
    {
        if ((int) $this->owner_id === (int) $userId) {
            return true;
        }

        return $this->members()
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get user role in this list ('owner', 'member', or null)
     */
    public function getUserRole(int $userId): ?string
    {
        if ((int) $this->owner_id === (int) $userId) {
            return 'owner';
        }

        $member = $this->members()->where('user_id', $userId)->first();

        return $member ? $member->role : null;
    }

    /**
     * Calculate progress metrics for this list (FR-C4)
     */
    public function calculateProgress(): array
    {
        $total = $this->tasks()->count();
        $completed = $this->tasks()->where('status', 'completed')->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $tasksByStatus = [
            'pending' => $this->tasks()->where('status', 'pending')->count(),
            'in_progress' => $this->tasks()->where('status', 'in_progress')->count(),
            'completed' => $completed,
        ];

        $tasksByPriority = [
            'low' => $this->tasks()->where('priority', 'low')->count(),
            'medium' => $this->tasks()->where('priority', 'medium')->count(),
            'high' => $this->tasks()->where('priority', 'high')->count(),
        ];

        // Upcoming deadlines: non-completed tasks with deadline ordered by deadline ASC
        $upcomingTasks = $this->tasks()
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->orderBy('deadline', 'asc')
            ->limit(10)
            ->get();

        $today = Carbon::today();
        $upcomingDeadlines = [];

        foreach ($upcomingTasks as $task) {
            $deadlineDate = Carbon::parse($task->deadline);
            $daysLeft = (int) $today->diffInDays($deadlineDate, false);

            $upcomingDeadlines[] = [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority,
                'deadline' => $task->deadline->format('Y-m-d'),
                'days_left' => $daysLeft,
            ];
        }

        return [
            'list_id' => $this->id,
            'name' => $this->name,
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'progress_percentage' => $percentage,
            'tasks_by_status' => $tasksByStatus,
            'tasks_by_priority' => $tasksByPriority,
            'upcoming_deadlines' => $upcomingDeadlines,
        ];
    }
}
