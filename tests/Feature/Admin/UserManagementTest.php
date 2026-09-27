<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $jwtService = app(JwtService::class);
        $this->adminToken = $jwtService->generateToken($this->admin);
    }

    public function test_admin_can_list_all_users(): void
    {
        User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->getJson('/admin/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_create_new_user(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/admin/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'role' => 'user',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'name' => 'New User',
                    'email' => 'newuser@example.com',
                    'role' => 'user',
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_admin_cannot_create_user_with_duplicate_email(): void
    {
        User::create([
            'name' => 'Existing',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->postJson('/admin/users', [
                'name' => 'Duplicate',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Email already registered',
                'code' => 'EMAIL_EXISTS',
            ]);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $targetUser = User::create([
            'name' => 'Target User',
            'email' => 'target@example.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->deleteJson('/admin/users/'.$targetUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User deactivated successfully',
            ]);

        $this->assertFalse((bool) $targetUser->fresh()->is_active);
    }

    public function test_admin_deactivate_non_existent_user_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->deleteJson('/admin/users/9999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => 'User not found',
                'code' => 'USER_NOT_FOUND',
            ]);
    }
}
