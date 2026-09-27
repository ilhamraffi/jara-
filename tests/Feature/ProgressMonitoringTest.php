<?php

namespace Tests\Feature;

use App\Models\ListMember;
use App\Models\ProjectList;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected ProjectList $list;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Test User']);
        $this->otherUser = User::factory()->create(['name' => 'Other User']);

        $this->list = ProjectList::create([
            'owner_id' => $this->user->id,
            'name' => 'Sprint 1',
            'description' => 'First development sprint',
        ]);

        // Create tasks with different statuses and priorities
        Task::create([
            'list_id' => $this->list->id,
            'title' => 'Completed Task',
            'priority' => 'high',
            'deadline' => Carbon::today()->addDays(2),
            'status' => 'completed',
        ]);

        Task::create([
            'list_id' => $this->list->id,
            'title' => 'In Progress Task',
            'priority' => 'medium',
            'deadline' => Carbon::today()->addDays(1),
            'status' => 'in_progress',
        ]);

        Task::create([
            'list_id' => $this->list->id,
            'title' => 'Pending Task',
            'priority' => 'low',
            'deadline' => Carbon::today()->addDays(5),
            'status' => 'pending',
        ]);

        Task::create([
            'list_id' => $this->list->id,
            'title' => 'Overdue Task',
            'priority' => 'high',
            'deadline' => Carbon::yesterday(),
            'status' => 'pending',
        ]);
    }

    /**
     * FR-C4: Owner/member dapat memantau progres penyelesaian tugas dalam daftar
     */
    public function test_can_get_list_progress_summary(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/lists/{$this->list->id}/progress");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'list_id' => $this->list->id,
                    'total_tasks' => 4,
                    'completed_tasks' => 1,
                    'progress_percentage' => 25,
                    'tasks_by_status' => [
                        'pending' => 2,
                        'in_progress' => 1,
                        'completed' => 1,
                    ],
                    'tasks_by_priority' => [
                        'low' => 1,
                        'medium' => 1,
                        'high' => 2,
                    ],
                ],
            ]);
    }

    /**
     * FR-C5: Sistem menampilkan dashboard ringkasan progres per daftar
     */
    public function test_can_get_dashboard_summary(): void
    {
        // Add another list where user is an invited member
        $memberList = ProjectList::create([
            'owner_id' => $this->otherUser->id,
            'name' => 'Collab Sprint',
        ]);

        ListMember::create([
            'list_id' => $memberList->id,
            'user_id' => $this->user->id,
            'role' => 'member',
        ]);

        Task::create([
            'list_id' => $memberList->id,
            'title' => 'Member Task Completed',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_lists' => 2,
                    'owned_lists' => 1,
                    'member_lists' => 1,
                    'total_tasks' => 5,
                    'total_completed' => 2,
                    'overall_progress' => 40,
                ],
            ]);
    }

    /**
     * FR-C6: Notifikasi tugas yang mendekati deadline atau overdue
     */
    public function test_can_get_deadline_notifications(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/notifications');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'notifications' => [
                        '*' => [
                            'task_id',
                            'task_title',
                            'list_id',
                            'deadline',
                            'days_left',
                            'urgency',
                            'message',
                        ],
                    ],
                    'count',
                ],
            ]);
    }
}
