<?php

namespace Tests\Feature\Tasks;

use App\Models\ListModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_create_task(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);
        $list->members()->create(['user_id' => $owner->id, 'role' => 'owner']);
        $list->members()->create(['user_id' => $member->id, 'role' => 'member']);

        $response = $this->actingAsApi($member)
            ->postJson("/api/lists/{$list->id}/tasks", [
                'title' => 'New Task',
                'description' => 'Task description',
                'priority' => 'high',
                'deadline' => '2026-10-15',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'New Task',
                    'priority' => 'high',
                    'status' => 'pending',
                ],
            ]);
    }

    public function test_non_member_cannot_create_task(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);
        $list->members()->create(['user_id' => $owner->id, 'role' => 'owner']);

        $response = $this->actingAsApi($stranger)
            ->postJson("/api/lists/{$list->id}/tasks", [
                'title' => 'New Task',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ACCESS_DENIED',
            ]);
    }

    public function test_title_is_required(): void
    {
        $owner = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);
        $list->members()->create(['user_id' => $owner->id, 'role' => 'owner']);

        $response = $this->actingAsApi($owner)
            ->postJson("/api/lists/{$list->id}/tasks", [
                'priority' => 'high',
            ]);

        $response->assertStatus(422);
    }
}
