<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Jara',
                'password' => 'password123',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Standard User 1
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'John Doe',
                'password' => 'password123',
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // Standard User 2
        User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Doe',
                'password' => 'password123',
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // Deactivated User
        User::firstOrCreate(
            ['email' => 'inactive@example.com'],
            [
                'name' => 'Inactive User',
                'password' => 'password123',
                'role' => 'user',
                'is_active' => false,
            ]
        );
    }
}
