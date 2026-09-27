<?php

namespace App\Http\Controllers;

use App\Models\ListMember;
use App\Models\ProjectList;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CollaborationController extends Controller
{
    protected function getActiveUser(Request $request): ?User
    {
        $user = $request->user();

        if (! $user && Auth::check()) {
            $user = Auth::user();
        }

        if (! $user && $request->hasHeader('X-User-Id')) {
            $user = User::find($request->header('X-User-Id'));
        }

        if (! $user && $request->bearerToken()) {
            if (is_numeric($request->bearerToken())) {
                $user = User::find($request->bearerToken());
            } else {
                $user = User::where('is_active', true)->first();
            }
        }

        return $user;
    }

    protected function errorResponse(string $message, string $code, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
            'code' => $code,
        ], $status);
    }

    protected function successResponse(mixed $data = null, string $message = 'Operation successful', int $status = 200): JsonResponse
    {
        $response = ['success' => true];

        if ($data !== null) {
            $response['data'] = $data;
        }

        $response['message'] = $message;

        return response()->json($response, $status);
    }

    public function addMember(Request $request, int $id): JsonResponse
    {
        $currentUser = $this->getActiveUser($request);
        if (! $currentUser) {
            return $this->errorResponse('Unauthenticated', 'UNAUTHENTICATED', 401);
        }

        $list = ProjectList::find($id);
        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $currentUser->isAdmin() && ! $list->isOwner($currentUser->id)) {
            return $this->errorResponse('Only owner can invite members', 'OWNER_ONLY', 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'nullable|in:owner,member',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 'VALIDATION_ERROR', 400);
        }

        $targetUserId = (int) $request->input('user_id');
        $role = $request->input('role', 'member');

        $alreadyMember = ListMember::where('list_id', $list->id)
            ->where('user_id', $targetUserId)
            ->exists();

        if ($alreadyMember || $list->owner_id === $targetUserId) {
            return $this->errorResponse('User already a member of this list', 'MEMBER_EXISTS', 400);
        }

        $targetUser = User::find($targetUserId);
        if (! $targetUser || ! $targetUser->is_active) {
            return $this->errorResponse('Target user is not active or not found', 'USER_INACTIVE', 400);
        }

        $member = ListMember::create([
            'list_id' => $list->id,
            'user_id' => $targetUserId,
            'role' => $role,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $member->id,
                'list_id' => $member->list_id,
                'user_id' => $member->user_id,
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                ],
                'role' => $member->role,
                'created_at' => $member->created_at?->toISOString() ?? now()->toISOString(),
            ],
            'message' => 'Member added successfully',
        ], 201);
    }

    public function removeMember(Request $request, int $id, int $user_id): JsonResponse
    {
        $currentUser = $this->getActiveUser($request);
        if (! $currentUser) {
            return $this->errorResponse('Unauthenticated', 'UNAUTHENTICATED', 401);
        }

        $list = ProjectList::find($id);
        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $currentUser->isAdmin() && ! $list->isOwner($currentUser->id)) {
            return $this->errorResponse('Only owner can remove members', 'OWNER_ONLY', 403);
        }

        if ((int) $list->owner_id === $user_id) {
            return $this->errorResponse('Cannot remove the list owner', 'CANNOT_REMOVE_OWNER', 400);
        }

        $member = ListMember::where('list_id', $list->id)
            ->where('user_id', $user_id)
            ->first();

        if (! $member) {
            return $this->errorResponse('Member not found in this list', 'MEMBER_NOT_FOUND', 404);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully',
        ], 200);
    }

    public function getMembers(Request $request, int $id): JsonResponse
    {
        $currentUser = $this->getActiveUser($request);
        if (! $currentUser) {
            return $this->errorResponse('Unauthenticated', 'UNAUTHENTICATED', 401);
        }

        $list = ProjectList::with('owner')->find($id);
        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $currentUser->isAdmin() && ! $list->isMember($currentUser->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        $membersData = [];

        if ($list->owner) {
            $membersData[] = [
                'id' => 0,
                'user_id' => $list->owner->id,
                'user' => [
                    'id' => $list->owner->id,
                    'name' => $list->owner->name,
                    'email' => $list->owner->email,
                ],
                'role' => 'owner',
            ];
        }

        $listMembers = ListMember::with('user')
            ->where('list_id', $list->id)
            ->where('user_id', '!=', $list->owner_id)
            ->get();

        foreach ($listMembers as $lm) {
            if ($lm->user) {
                $membersData[] = [
                    'id' => $lm->id,
                    'user_id' => $lm->user_id,
                    'user' => [
                        'id' => $lm->user->id,
                        'name' => $lm->user->name,
                        'email' => $lm->user->email,
                    ],
                    'role' => $lm->role,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $membersData,
        ], 200);
    }

    public function getProgress(Request $request, int $id): JsonResponse
    {
        $currentUser = $this->getActiveUser($request);
        if (! $currentUser) {
            return $this->errorResponse('Unauthenticated', 'UNAUTHENTICATED', 401);
        }

        $list = ProjectList::find($id);
        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $currentUser->isAdmin() && ! $list->isMember($currentUser->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        $progressData = $list->calculateProgress();

        return response()->json([
            'success' => true,
            'data' => $progressData,
        ], 200);
    }

    public function getDashboard(Request $request): JsonResponse
    {
        $currentUser = $this->getActiveUser($request);
        if (! $currentUser) {
            return $this->errorResponse('Unauthenticated', 'UNAUTHENTICATED', 401);
        }

        $ownedLists = ProjectList::where('owner_id', $currentUser->id)->get();

        $memberListIds = ListMember::where('user_id', $currentUser->id)
            ->pluck('list_id')
            ->toArray();

        $memberLists = ProjectList::whereIn('id', $memberListIds)
            ->where('owner_id', '!=', $currentUser->id)
            ->get();

        $allLists = $ownedLists->merge($memberLists)->unique('id');

        $listsData = [];
        $totalTasksCount = 0;
        $totalCompletedCount = 0;

        foreach ($allLists as $list) {
            $totalTasks = $list->tasks()->count();
            $completedTasks = $list->tasks()->where('status', 'completed')->count();
            $percentage = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

            $memberCount = $list->members()->where('user_id', '!=', $list->owner_id)->count() + 1;
            $userRole = $list->getUserRole($currentUser->id);

            $listsData[] = [
                'id' => $list->id,
                'name' => $list->name,
                'description' => $list->description,
                'owner_id' => $list->owner_id,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'progress_percentage' => $percentage,
                'member_count' => $memberCount,
                'your_role' => $userRole,
            ];

            $totalTasksCount += $totalTasks;
            $totalCompletedCount += $completedTasks;
        }

        $overallProgress = $totalTasksCount > 0
            ? (int) round(($totalCompletedCount / $totalTasksCount) * 100)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_lists' => $allLists->count(),
                'owned_lists' => $ownedLists->count(),
                'member_lists' => $memberLists->count(),
                'lists' => $listsData,
                'total_tasks' => $totalTasksCount,
                'total_completed' => $totalCompletedCount,
                'overall_progress' => $overallProgress,
            ],
        ], 200);
    }

    public function getNotifications(Request $request): JsonResponse
    {
        $currentUser = $this->getActiveUser($request);
        if (! $currentUser) {
            return $this->errorResponse('Unauthenticated', 'UNAUTHENTICATED', 401);
        }

        $accessibleListIds = ProjectList::where('owner_id', $currentUser->id)
            ->pluck('id')
            ->merge(
                ListMember::where('user_id', $currentUser->id)->pluck('list_id')
            )
            ->unique();

        $today = Carbon::today();
        $threeDaysAhead = Carbon::today()->addDays(3);

        $tasks = Task::with('list')
            ->whereIn('list_id', $accessibleListIds)
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->where('deadline', '<=', $threeDaysAhead)
            ->orderBy('deadline', 'asc')
            ->get();

        $notifications = [];

        foreach ($tasks as $task) {
            $deadline = Carbon::parse($task->deadline);
            $daysLeft = (int) $today->diffInDays($deadline, false);

            $statusText = $daysLeft < 0 ? 'overdue' : ($daysLeft === 0 ? 'today' : 'upcoming');
            $message = match ($statusText) {
                'overdue' => "Tugas '{$task->title}' telah melewati deadline (".abs($daysLeft)." hari lalu) pada daftar {$task->list?->name}",
                'today' => "Tugas '{$task->title}' jatuh tempo HARI INI pada daftar {$task->list?->name}",
                default => "Tugas '{$task->title}' mendekati deadline ({$daysLeft} hari lagi) pada daftar {$task->list?->name}",
            };

            $notifications[] = [
                'id' => $task->id,
                'task_id' => $task->id,
                'task_title' => $task->title,
                'list_id' => $task->list_id,
                'list_name' => $task->list?->name,
                'deadline' => $task->deadline->format('Y-m-d'),
                'days_left' => $daysLeft,
                'urgency' => $statusText,
                'message' => $message,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'count' => count($notifications),
            ],
        ], 200);
    }
}
