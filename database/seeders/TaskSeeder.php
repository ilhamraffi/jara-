<?php

namespace Database\Seeders;

use App\Models\ListModel;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $list1 = ListModel::where('name', 'Project Alpha')->first();
        $list2 = ListModel::where('name', 'Personal Tasks')->first();
        $list3 = ListModel::where('name', 'Team Collaboration')->first();

        // Tasks for Project Alpha
        Task::create([
            'list_id' => $list1->id,
            'title' => 'Design database schema',
            'description' => 'Create ERD and define table structures',
            'priority' => 'high',
            'deadline' => '2026-10-05',
            'status' => 'completed',
        ]);

        Task::create([
            'list_id' => $list1->id,
            'title' => 'Implement authentication',
            'description' => 'Setup JWT auth and user management',
            'priority' => 'high',
            'deadline' => '2026-10-10',
            'status' => 'in_progress',
        ]);

        Task::create([
            'list_id' => $list1->id,
            'title' => 'Build task CRUD',
            'description' => 'Create task management endpoints',
            'priority' => 'medium',
            'deadline' => '2026-10-15',
            'status' => 'pending',
        ]);

        Task::create([
            'list_id' => $list1->id,
            'title' => 'Write documentation',
            'description' => 'Document API endpoints',
            'priority' => 'low',
            'deadline' => '2026-10-20',
            'status' => 'pending',
        ]);

        // Tasks for Personal Tasks
        Task::create([
            'list_id' => $list2->id,
            'title' => 'Buy groceries',
            'description' => 'Weekly grocery shopping',
            'priority' => 'medium',
            'deadline' => '2026-10-01',
            'status' => 'completed',
        ]);

        Task::create([
            'list_id' => $list2->id,
            'title' => 'Schedule dentist appointment',
            'description' => 'Annual checkup',
            'priority' => 'high',
            'deadline' => '2026-10-08',
            'status' => 'pending',
        ]);

        // Tasks for Team Collaboration
        Task::create([
            'list_id' => $list3->id,
            'title' => 'Prepare presentation',
            'description' => 'Prepare slides for client meeting',
            'priority' => 'high',
            'deadline' => '2026-10-12',
            'status' => 'in_progress',
        ]);

        Task::create([
            'list_id' => $list3->id,
            'title' => 'Review budget',
            'description' => 'Review Q4 budget allocation',
            'priority' => 'medium',
            'deadline' => '2026-10-18',
            'status' => 'pending',
        ]);
    }
}
