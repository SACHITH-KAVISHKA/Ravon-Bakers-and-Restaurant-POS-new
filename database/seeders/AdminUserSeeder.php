<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@revon.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create staff user
        User::updateOrCreate(
            ['email' => 'staff@revon.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );

        // Create supervisor user
        User::updateOrCreate(
            ['email' => 'supervisor@revon.com'],
            [
                'name' => 'Supervisor User',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
            ]
        );
    }
}
