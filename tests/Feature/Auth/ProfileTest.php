<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_name(): void
    {
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/profile', [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'Updated Name',
                    'email' => 'user@example.com',
                ],
            ]);

        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    public function test_user_can_change_password_with_valid_old_password(): void
    {
        $user = User::create([
            'name' => 'User Name',
            'email' => 'user@example.com',
            'password' => 'oldpass123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/profile/password', [
                'old_password' => 'oldpass123',
                'new_password' => 'newpass456',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        $this->assertTrue(Hash::check('newpass456', $user->fresh()->password));
    }

    public function test_change_password_fails_with_invalid_old_password(): void
    {
        $user = User::create([
            'name' => 'User Name',
            'email' => 'user@example.com',
            'password' => 'oldpass123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/profile/password', [
                'old_password' => 'wrongoldpass',
                'new_password' => 'newpass456',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Old password is incorrect',
                'code' => 'INVALID_PASSWORD',
            ]);
    }
}
