<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
<<<<<<< HEAD
        $this->call([
            UserSeeder::class,
            ListSeeder::class,
            TaskSeeder::class,
            MemberSeeder::class,
        ]);
=======
        $this->call(UserSeeder::class);

        if (class_exists(ListSeeder::class)) {
            $this->call(ListSeeder::class);
        }

        if (class_exists(TaskSeeder::class)) {
            $this->call(TaskSeeder::class);
        }

        if (class_exists(MemberSeeder::class)) {
            $this->call(MemberSeeder::class);
        }
>>>>>>> origin/feat/p1-auth-admin
    }
}
