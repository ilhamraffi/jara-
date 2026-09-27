<?php

namespace Database\Seeders;

use App\Models\ListModel;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListSeeder extends Seeder
{
    public function run(): void
    {
        $john = User::where('email', 'john@example.com')->first();
        $jane = User::where('email', 'jane@example.com')->first();
        $bob = User::where('email', 'bob@example.com')->first();
        $alice = User::where('email', 'alice@example.com')->first();

        // List 1: Project Alpha (owned by John)
        $list1 = ListModel::create([
            'owner_id' => $john->id,
            'name' => 'Project Alpha',
            'description' => 'Main project for Q4 2026',
        ]);

        $list1->members()->create(['user_id' => $john->id, 'role' => 'owner']);
        $list1->members()->create(['user_id' => $jane->id, 'role' => 'member']);
        $list1->members()->create(['user_id' => $bob->id, 'role' => 'member']);

        // List 2: Personal Tasks (owned by Jane)
        $list2 = ListModel::create([
            'owner_id' => $jane->id,
            'name' => 'Personal Tasks',
            'description' => 'Daily personal tasks',
        ]);

        $list2->members()->create(['user_id' => $jane->id, 'role' => 'owner']);

        // List 3: Team Collaboration (owned by Bob)
        $list3 = ListModel::create([
            'owner_id' => $bob->id,
            'name' => 'Team Collaboration',
            'description' => 'Cross-team collaboration project',
        ]);

        $list3->members()->create(['user_id' => $bob->id, 'role' => 'owner']);
        $list3->members()->create(['user_id' => $alice->id, 'role' => 'member']);
    }
}
