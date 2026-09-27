<?php

namespace Tests\Unit\Models;

use App\Models\ListModel;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_belongs_to_owner(): void
    {
        $owner = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $owner->id]);

        $this->assertInstanceOf(User::class, $list->owner);
        $this->assertEquals($owner->id, $list->owner->id);
    }

    public function test_list_has_many_tasks(): void
    {
        $list = ListModel::factory()->create();
        Task::factory()->count(3)->create(['list_id' => $list->id]);

        $this->assertCount(3, $list->tasks);
    }

    public function test_list_has_many_members(): void
    {
        $list = ListModel::factory()->create();
        $list->members()->create(['user_id' => User::factory()->create()->id, 'role' => 'owner']);
        $list->members()->create(['user_id' => User::factory()->create()->id, 'role' => 'member']);

        $this->assertCount(2, $list->members);
    }

    public function test_is_member_returns_true_for_member(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create();
        $list->members()->create(['user_id' => $user->id, 'role' => 'member']);

        $this->assertTrue($list->isMember($user->id));
    }

    public function test_is_member_returns_false_for_non_member(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create();

        $this->assertFalse($list->isMember($user->id));
    }

    public function test_is_owner_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $list = ListModel::factory()->create(['owner_id' => $user->id]);

        $this->assertTrue($list->isOwner($user->id));
    }
}
