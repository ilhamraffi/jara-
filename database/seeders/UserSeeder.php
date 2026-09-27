<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
<<<<<<< HEAD
use Illuminate\Support\Facades\Hash;
=======
>>>>>>> origin/feat/p1-auth-admin

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
=======
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Jara',
                'password' => 'password123',
>>>>>>> origin/feat/p1-auth-admin
                'role' => 'admin',
                'is_active' => true,
            ]
        );

<<<<<<< HEAD
        User::updateOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password123'),
=======
        // Standard User 1
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'John Doe',
                'password' => 'password123',
>>>>>>> origin/feat/p1-auth-admin
                'role' => 'user',
                'is_active' => true,
            ]
        );

<<<<<<< HEAD
        User::updateOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make('password123'),
=======
        // Standard User 2
        User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Doe',
                'password' => 'password123',
>>>>>>> origin/feat/p1-auth-admin
                'role' => 'user',
                'is_active' => true,
            ]
        );

<<<<<<< HEAD
        User::updateOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Johnson',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
=======
        // Deactivated User
        User::firstOrCreate(
            ['email' => 'inactive@example.com'],
            [
                'name' => 'Inactive User',
                'password' => 'password123',
                'role' => 'user',
                'is_active' => false,
>>>>>>> origin/feat/p1-auth-admin
            ]
        );
    }
}
