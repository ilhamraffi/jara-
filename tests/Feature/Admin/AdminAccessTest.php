<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $regularUser;

    protected string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $jwtService = app(JwtService::class);
        $this->userToken = $jwtService->generateToken($this->regularUser);
    }

    public function test_regular_user_cannot_list_users(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->userToken)
            ->getJson('/admin/users');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => 'Unauthorized. Admin access required.',
                'code' => 'ADMIN_ONLY',
            ]);
    }

    public function test_regular_user_cannot_create_user(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->userToken)
            ->postJson('/admin/users', [
                'name' => 'Should Fail',
                'email' => 'fail@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => 'Unauthorized. Admin access required.',
                'code' => 'ADMIN_ONLY',
            ]);
    }

    public function test_regular_user_cannot_deactivate_user(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->userToken)
            ->deleteJson('/admin/users/1');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => 'Unauthorized. Admin access required.',
                'code' => 'ADMIN_ONLY',
            ]);
    }

    public function test_unauthenticated_request_cannot_access_admin_endpoints(): void
    {
        $response = $this->getJson('/admin/users');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'UNAUTHORIZED',
            ]);
    }
}
