<?php

namespace Tests\Feature\Tasks;

use App\Models\ListModel;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_delete_task(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $user->id]);
        $list->members()->create(['user_id' => $user->id, 'role' => 'owner']);
        $task = Task::factory()->create(['list_id' => $list->id]);

        $response = $this->actingAsApi($user)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_non_member_cannot_delete_task(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);
        $list->members()->create(['user_id' => $owner->id, 'role' => 'owner']);
        $task = Task::factory()->create(['list_id' => $list->id]);

        $response = $this->actingAsApi($stranger)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }
}
