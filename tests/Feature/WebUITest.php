<?php

namespace Tests\Feature;

use App\Models\ListMember;
use App\Models\ProjectList;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebUITest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $member;

    protected User $stranger;

    protected ProjectList $list;

    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Owner', 'email' => 'owner@example.com']);
        $this->member = User::factory()->create(['name' => 'Member', 'email' => 'member@example.com']);
        $this->stranger = User::factory()->create(['name' => 'Stranger', 'email' => 'stranger@example.com']);

        $this->list = ProjectList::create([
            'owner_id' => $this->owner->id,
            'name' => 'Demo Project',
            'description' => 'A sample project for UI test',
        ]);

        ListMember::create([
            'list_id' => $this->list->id,
            'user_id' => $this->member->id,
            'role' => 'member',
        ]);

        $this->task = Task::create([
            'list_id' => $this->list->id,
            'title' => 'Sample UI Task',
            'status' => 'pending',
            'priority' => 'high',
        ]);
    }

    public function test_can_render_dashboard_view(): void
    {
        $response = $this->actingAs($this->owner)->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Dashboard Ringkasan Progres')
            ->assertSee('Demo Project');
    }

    public function test_can_render_list_detail_for_owner_and_member(): void
    {
        // Owner access
        $ownerResp = $this->actingAs($this->owner)->get("/lists/{$this->list->id}");
        $ownerResp->assertStatus(200)
            ->assertSee('Demo Project')
            ->assertSee('Role Anda: OWNER')
            ->assertSee('Undang Member (FR-C1)');

        // Member access
        $memberResp = $this->actingAs($this->member)->get("/lists/{$this->list->id}");
        $memberResp->assertStatus(200)
            ->assertSee('Demo Project')
            ->assertSee('Role Anda: MEMBER');
    }

    public function test_stranger_cannot_access_list_detail(): void
    {
        $response = $this->actingAs($this->stranger)->get("/lists/{$this->list->id}");
        $response->assertStatus(403);
    }

    public function test_member_can_toggle_task(): void
    {
        $response = $this->actingAs($this->member)
            ->post("/tasks/{$this->task->id}/toggle");

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_render_login_view(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200)->assertSee('Masuk ke JARA');
    }

    public function test_can_render_profile_view(): void
    {
        $response = $this->actingAs($this->owner)->get('/profile');
        $response->assertStatus(200)->assertSee('Profil Saya');
    }

    public function test_can_switch_user(): void
    {
        $response = $this->get("/switch-user/{$this->member->id}");
        $response->assertRedirect();
        $this->assertEquals($this->member->id, auth()->id());
    }
}
