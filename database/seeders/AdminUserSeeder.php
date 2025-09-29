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
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@revon.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create staff user
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@revon.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
    }
}
