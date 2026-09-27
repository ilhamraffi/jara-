<?php

namespace Tests\Feature\Lists;

use App\Models\ListModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateListTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_list(): void
    {
        $owner = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAsApi($owner)
            ->putJson("/api/lists/{$list->id}", [
                'name' => 'Updated List Name',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated List Name',
                    'description' => 'Updated description',
                ],
            ]);
    }

    public function test_non_owner_cannot_update_list(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAsApi($otherUser)
            ->putJson("/api/lists/{$list->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'OWNER_ONLY',
            ]);
    }
}
