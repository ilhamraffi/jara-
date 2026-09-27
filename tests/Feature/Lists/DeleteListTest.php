<?php

namespace Tests\Feature\Lists;

use App\Models\ListModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteListTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_list(): void
    {
        $owner = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAsApi($owner)
            ->deleteJson("/api/lists/{$list->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'List deleted successfully',
            ]);

        $this->assertDatabaseMissing('lists', ['id' => $list->id]);
    }

    public function test_non_owner_cannot_delete_list(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAsApi($otherUser)
            ->deleteJson("/api/lists/{$list->id}");

        $response->assertStatus(403);
    }
}
