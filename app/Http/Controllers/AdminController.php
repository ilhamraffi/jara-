<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * List all registered users (FR-A4).
     */
    public function index(): JsonResponse
    {
        $users = User::select(['id', 'name', 'email', 'role', 'is_active', 'created_at'])->get();

        return response()->json([
            'success' => true,
            'data' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'created_at' => $user->created_at?->toISOString() ?? $user->created_at,
            ]),
        ], 200);
    }

    /**
     * Admin creates a new user account (FR-A2).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:admin,user',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'code' => 'VALIDATION_ERROR',
            ], 400);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Email already registered',
                'code' => 'EMAIL_EXISTS',
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->input('role', 'user'),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'created_at' => $user->created_at?->toISOString() ?? $user->created_at,
            ],
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Admin deletes/deactivates a user account (FR-A3).
     */
    public function destroy(int|string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'error' => 'User not found',
                'code' => 'USER_NOT_FOUND',
            ], 404);
        }

        $user->is_active = false;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User deactivated successfully',
        ], 200);
    }
}
