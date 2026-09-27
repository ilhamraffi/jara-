<?php

namespace App\Http\Controllers;

use App\Models\ListModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollaborationController extends Controller
{
    public function store(Request $request, int $listId): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($listId);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isOwner($user->id)) {
            return $this->errorResponse('Only owner can invite members', 'OWNER_ONLY', 403);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'sometimes|in:owner,member',
        ]);

        if ($list->isMember($request->user_id)) {
            return $this->errorResponse('User already a member of this list', 'MEMBER_EXISTS', 400);
        }

        $member = $list->members()->create([
            'user_id' => $request->user_id,
            'role' => $request->role ?? 'member',
        ]);

        $member->load('user');

        return $this->successResponse($member, 'Member added successfully', 201);
    }

    public function destroy(int $listId, int $userId): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($listId);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isOwner($user->id)) {
            return $this->errorResponse('Only owner can remove members', 'OWNER_ONLY', 403);
        }

        $member = $list->members()->where('user_id', $userId)->first();

        if (! $member) {
            return $this->errorResponse('Member not found', 'MEMBER_NOT_FOUND', 404);
        }

        $member->delete();

        return $this->successResponse(null, 'Member removed successfully');
    }

    public function index(int $listId): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($listId);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isMember($user->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        $members = $list->members()->with('user')->get();

        return $this->successResponse($members);
    }

    public function progress(int $listId): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($listId);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isMember($user->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        $totalTasks = $list->tasks()->count();
        $completedTasks = $list->tasks()->where('status', 'completed')->count();
        $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $tasksByStatus = [
            'pending' => $list->tasks()->where('status', 'pending')->count(),
            'in_progress' => $list->tasks()->where('status', 'in_progress')->count(),
            'completed' => $list->tasks()->where('status', 'completed')->count(),
        ];

        $tasksByPriority = [
            'low' => $list->tasks()->where('priority', 'low')->count(),
            'medium' => $list->tasks()->where('priority', 'medium')->count(),
            'high' => $list->tasks()->where('priority', 'high')->count(),
        ];

        $upcomingDeadlines = $list->tasks()
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->where('deadline', '>=', now()->toDateString())
            ->orderBy('deadline')
            ->limit(5)
            ->get()
            ->map(function ($task) {
                $daysLeft = now()->diffInDays($task->deadline, false);

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'deadline' => $task->deadline->toDateString(),
                    'days_left' => $daysLeft,
                ];
            });

        return $this->successResponse([
            'list_id' => $list->id,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'progress_percentage' => $progressPercentage,
            'tasks_by_status' => $tasksByStatus,
            'tasks_by_priority' => $tasksByPriority,
            'upcoming_deadlines' => $upcomingDeadlines,
        ]);
    }

    public function dashboard(): JsonResponse
    {
        $user = request()->user();

        $lists = ListModel::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['tasks', 'members'])
            ->get();

        $ownedLists = $lists->where('owner_id', $user->id)->count();
        $memberLists = $lists->where('owner_id', '!=', $user->id)->count();

        $totalTasks = 0;
        $totalCompleted = 0;

        $listsData = $lists->map(function ($list) use ($user, &$totalTasks, &$totalCompleted) {
            $listTotalTasks = $list->tasks->count();
            $listCompletedTasks = $list->tasks->where('status', 'completed')->count();
            $listProgressPercentage = $listTotalTasks > 0 ? round(($listCompletedTasks / $listTotalTasks) * 100) : 0;

            $totalTasks += $listTotalTasks;
            $totalCompleted += $listCompletedTasks;

            $myRole = $list->members->where('user_id', $user->id)->first()->role ?? 'member';

            return [
                'id' => $list->id,
                'name' => $list->name,
                'owner_id' => $list->owner_id,
                'total_tasks' => $listTotalTasks,
                'completed_tasks' => $listCompletedTasks,
                'progress_percentage' => $listProgressPercentage,
                'member_count' => $list->members->count(),
                'your_role' => $myRole,
            ];
        });

        $overallProgress = $totalTasks > 0 ? round(($totalCompleted / $totalTasks) * 100) : 0;

        return $this->successResponse([
            'total_lists' => $lists->count(),
            'owned_lists' => $ownedLists,
            'member_lists' => $memberLists,
            'lists' => $listsData,
            'total_tasks' => $totalTasks,
            'total_completed' => $totalCompleted,
            'overall_progress' => $overallProgress,
        ]);
    }
}
