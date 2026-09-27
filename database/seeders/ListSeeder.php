<?php

namespace Database\Seeders;

use App\Models\ProjectList;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $john = User::where('email', 'john@example.com')->first();
        $jane = User::where('email', 'jane@example.com')->first();

        if ($john) {
            ProjectList::updateOrCreate(
                ['name' => 'Website Redesign Project', 'owner_id' => $john->id],
                ['description' => 'Revamping company landing page and customer portal.']
            );

            ProjectList::updateOrCreate(
                ['name' => 'Mobile App MVP', 'owner_id' => $john->id],
                ['description' => 'Initial Flutter MVP build for Android & iOS.']
            );
        }

        if ($jane) {
            ProjectList::updateOrCreate(
                ['name' => 'Brand & Design System', 'owner_id' => $jane->id],
                ['description' => 'Figma design tokens, typography, and UI guidelines.']
            );
        }
    }
}
