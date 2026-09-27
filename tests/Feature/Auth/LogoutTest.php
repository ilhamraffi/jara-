<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout_with_valid_token(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful',
            ]);

        // Token should now be invalidated
        $profileResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/profile', ['name' => 'Should Fail']);

        $profileResponse->assertStatus(401);
    }

    public function test_logout_fails_without_token(): void
    {
        $response = $this->postJson('/auth/logout');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'UNAUTHORIZED',
            ]);
    }
}
