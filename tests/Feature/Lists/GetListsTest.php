<?php

namespace Tests\Feature\Lists;

use App\Models\ListModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_their_lists(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $user->id]);
        $list->members()->create(['user_id' => $user->id, 'role' => 'owner']);

        $response = $this->actingAsApi($user)
            ->getJson('/api/lists');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'name' => $list->name,
            ]);
    }

    public function test_member_can_see_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);
        $list->members()->create(['user_id' => $owner->id, 'role' => 'owner']);
        $list->members()->create(['user_id' => $member->id, 'role' => 'member']);

        $response = $this->actingAsApi($member)
            ->getJson("/api/lists/{$list->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_non_member_cannot_see_list(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);
        $list->members()->create(['user_id' => $owner->id, 'role' => 'owner']);

        $response = $this->actingAsApi($stranger)
            ->getJson("/api/lists/{$list->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ACCESS_DENIED',
            ]);
    }
}
