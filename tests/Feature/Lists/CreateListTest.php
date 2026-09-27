<?php

namespace Tests\Feature\Lists;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApi($user)
            ->postJson('/api/lists', [
                'name' => 'My New List',
                'description' => 'A test list',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'My New List',
                    'description' => 'A test list',
                ],
            ]);

        $this->assertDatabaseHas('lists', [
            'name' => 'My New List',
            'owner_id' => $user->id,
        ]);

        // Owner should be auto-added as member
        $this->assertDatabaseHas('list_members', [
            'list_id' => $response->json('data.id'),
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApi($user)
            ->postJson('/api/lists', [
                'description' => 'A test list',
            ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_create_list(): void
    {
        $response = $this->postJson('/api/lists', [
            'name' => 'My New List',
        ]);

        $response->assertStatus(401);
    }
}
