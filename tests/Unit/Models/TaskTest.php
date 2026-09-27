<?php

namespace Tests\Unit\Models;

use App\Models\ListModel;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_belongs_to_list(): void
    {
        $list = ListModel::factory()->create();
        $task = Task::factory()->create(['list_id' => $list->id]);

        $this->assertInstanceOf(ListModel::class, $task->list);
        $this->assertEquals($list->id, $task->list->id);
    }

    public function test_is_completed_returns_true_for_completed_task(): void
    {
        $task = Task::factory()->create(['status' => 'completed']);

        $this->assertTrue($task->isCompleted());
    }

    public function test_is_completed_returns_false_for_pending_task(): void
    {
        $task = Task::factory()->create(['status' => 'pending']);

        $this->assertFalse($task->isCompleted());
    }
}
