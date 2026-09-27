<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(protected JwtService $jwtService) {}

    /**
     * User login endpoint (FR-A1).
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'code' => 'VALIDATION_ERROR',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password) || ! $user->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials',
                'code' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        $token = $this->jwtService->generateToken($user);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
            'message' => 'Login successful',
        ], 200);
    }

    /**
     * User logout endpoint.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('token');

        if (! $token) {
            $header = $request->header('Authorization');
            if ($header && preg_match('/Bearer\s+(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        if ($token) {
            $this->jwtService->invalidateToken($token);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ], 200);
    }

    /**
     * Update own profile (FR-A6).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'code' => 'VALIDATION_ERROR',
            ], 400);
        }

        /** @var User $user */
        $user = $request->user();
        $user->name = $request->name;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Change password (FR-A6).
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'code' => 'VALIDATION_ERROR',
            ], 400);
        }

        /** @var User $user */
        $user = $request->user();

        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Old password is incorrect',
                'code' => 'INVALID_PASSWORD',
            ], 400);
        }

        $user->password = $request->new_password;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ], 200);
    }
}
