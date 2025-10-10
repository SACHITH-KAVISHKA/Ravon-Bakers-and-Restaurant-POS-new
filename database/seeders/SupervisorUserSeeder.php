<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SupervisorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update supervisor user without branch assignment (manages central inventory)
        User::updateOrCreate(
            ['email' => 'supervisor@ravon.com'],
            [
                'name' => 'Supervisor User',
                'email' => 'supervisor@ravon.com',
                'password' => Hash::make('supervisor123'),
                'role' => 'supervisor',
                'branch_id' => null, // No branch assignment - manages central inventory
            ]
        );
    }
}
