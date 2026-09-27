<?php

namespace Tests\Feature\Tasks;

use App\Models\ListModel;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_status(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $user->id]);
        $list->members()->create(['user_id' => $user->id, 'role' => 'owner']);

        Task::factory()->create(['list_id' => $list->id, 'status' => 'pending']);
        Task::factory()->create(['list_id' => $list->id, 'status' => 'completed']);
        Task::factory()->create(['list_id' => $list->id, 'status' => 'in_progress']);

        $response = $this->actingAsApi($user)
            ->getJson("/api/lists/{$list->id}/tasks?status=pending");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filter_by_priority(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $user->id]);
        $list->members()->create(['user_id' => $user->id, 'role' => 'owner']);

        Task::factory()->create(['list_id' => $list->id, 'priority' => 'high']);
        Task::factory()->create(['list_id' => $list->id, 'priority' => 'low']);
        Task::factory()->create(['list_id' => $list->id, 'priority' => 'medium']);

        $response = $this->actingAsApi($user)
            ->getJson("/api/lists/{$list->id}/tasks?priority=high");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filter_by_combined_params(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $user->id]);
        $list->members()->create(['user_id' => $user->id, 'role' => 'owner']);

        Task::factory()->create([
            'list_id' => $list->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);
        Task::factory()->create([
            'list_id' => $list->id,
            'status' => 'completed',
            'priority' => 'high',
        ]);
        Task::factory()->create([
            'list_id' => $list->id,
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response = $this->actingAsApi($user)
            ->getJson("/api/lists/{$list->id}/tasks?status=pending&priority=high");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_filter_by_deadline(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $user->id]);
        $list->members()->create(['user_id' => $user->id, 'role' => 'owner']);

        $filterDate = now()->addDays(10)->toDateString();

        Task::factory()->create([
            'list_id' => $list->id,
            'deadline' => now()->addDays(5)->toDateString(),
        ]);
        Task::factory()->create([
            'list_id' => $list->id,
            'deadline' => now()->addDays(10)->toDateString(),
        ]);
        Task::factory()->create([
            'list_id' => $list->id,
            'deadline' => now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAsApi($user)
            ->getJson("/api/lists/{$list->id}/tasks?deadline={$filterDate}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
