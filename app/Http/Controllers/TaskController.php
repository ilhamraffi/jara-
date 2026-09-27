<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\ListModel;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, int $listId): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($listId);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isMember($user->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        $task = Task::create([
            'list_id' => $list->id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'deadline' => $request->deadline,
            'status' => 'pending',
        ]);

        return $this->successResponse($task, 'Task created successfully', 201);
    }

    public function index(Request $request, int $listId): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($listId);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isMember($user->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        $query = Task::where('list_id', $list->id);

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && in_array($request->priority, ['low', 'medium', 'high'])) {
            $query->where('priority', $request->priority);
        }

        // Filter by deadline (deadline <= this date)
        if ($request->has('deadline')) {
            $query->whereDate('deadline', '<=', $request->deadline);
        }

        $tasks = $query->get();

        return $this->successResponse($tasks);
    }

    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        $task = Task::find($id);

        if (! $task) {
            return $this->errorResponse('Task not found', 'TASK_NOT_FOUND', 404);
        }

        $list = $task->list;

        if (! $list->isMember($user->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        return $this->successResponse($task);
    }

    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $user = request()->user();
        $task = Task::find($id);

        if (! $task) {
            return $this->errorResponse('Task not found', 'TASK_NOT_FOUND', 404);
        }

        $list = $task->list;

        if (! $list->isMember($user->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        $task->update($request->only(['title', 'description', 'priority', 'deadline', 'status']));

        return $this->successResponse($task);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        $task = Task::find($id);

        if (! $task) {
            return $this->errorResponse('Task not found', 'TASK_NOT_FOUND', 404);
        }

        $list = $task->list;

        if (! $list->isMember($user->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        $task->delete();

        return $this->successResponse(null, 'Task deleted successfully');
    }
}
