<?php

namespace Database\Seeders;

use App\Models\ListMember;
use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $john = User::where('email', 'john@example.com')->first();
        $jane = User::where('email', 'jane@example.com')->first();
        $bob = User::where('email', 'bob@example.com')->first();

        $list1 = ProjectList::where('name', 'Website Redesign Project')->first();
        $list2 = ProjectList::where('name', 'Mobile App MVP')->first();
        $list3 = ProjectList::where('name', 'Brand & Design System')->first();

        // List 1: John (owner), Jane (member), Bob (member)
        if ($list1 && $jane) {
            ListMember::updateOrCreate(
                ['list_id' => $list1->id, 'user_id' => $jane->id],
                ['role' => 'member']
            );
        }

        if ($list1 && $bob) {
            ListMember::updateOrCreate(
                ['list_id' => $list1->id, 'user_id' => $bob->id],
                ['role' => 'member']
            );
        }

        // List 2: John (owner), Jane (member)
        if ($list2 && $jane) {
            ListMember::updateOrCreate(
                ['list_id' => $list2->id, 'user_id' => $jane->id],
                ['role' => 'member']
            );
        }

        // List 3: Jane (owner), John (member)
        if ($list3 && $john) {
            ListMember::updateOrCreate(
                ['list_id' => $list3->id, 'user_id' => $john->id],
                ['role' => 'member']
            );
        }
    }
}
