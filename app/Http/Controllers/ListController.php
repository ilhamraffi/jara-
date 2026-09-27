<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListRequest;
use App\Http\Requests\UpdateListRequest;
use App\Models\ListModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function store(StoreListRequest $request): JsonResponse
    {
        $user = request()->user();

        $list = ListModel::create([
            'owner_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Auto-add owner as member with role 'owner'
        $list->members()->create([
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        return $this->successResponse($list, 'List created successfully', 201);
    }

    public function index(Request $request): JsonResponse
    {
        $user = request()->user();

        $lists = ListModel::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->withCount(['members', 'tasks'])
            ->withCount(['tasks as completed_count' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->get();

        return $this->successResponse($lists);
    }

    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($id);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isMember($user->id)) {
            return $this->errorResponse('You are not a member of this list', 'ACCESS_DENIED', 403);
        }

        return $this->successResponse($list);
    }

    public function update(UpdateListRequest $request, int $id): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($id);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isOwner($user->id)) {
            return $this->errorResponse('Only owner can update this list', 'OWNER_ONLY', 403);
        }

        $list->update($request->only(['name', 'description']));

        return $this->successResponse($list);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        $list = ListModel::find($id);

        if (! $list) {
            return $this->errorResponse('List not found', 'LIST_NOT_FOUND', 404);
        }

        if (! $list->isOwner($user->id)) {
            return $this->errorResponse('Only owner can delete this list', 'OWNER_ONLY', 403);
        }

        $list->delete();

        return $this->successResponse(null, 'List deleted successfully');
    }
}
