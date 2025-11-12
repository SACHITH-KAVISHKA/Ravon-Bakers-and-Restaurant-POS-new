<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Main Branch',
                'display_name' => 'Main Branch - Kaduwela',
                'address' => '282/A 2, Kaduwela',
                'telephone' => '076 200 6007',
                'status' => 1
            ],
            [
                'name' => 'Downtown Branch',
                'display_name' => 'Downtown Branch',
                'address' => '123 Main Street, Colombo 01',
                'telephone' => '011 234 5678',
                'status' => 1
            ],
            [
                'name' => 'Mall Branch',
                'display_name' => 'Mall Branch - One Galle Face',
                'address' => 'One Galle Face Mall, Level 3, Colombo 02',
                'telephone' => '011 456 7890',
                'status' => 1
            ],
            [
                'name' => 'Airport Branch',
                'display_name' => 'Airport Branch - BIA',
                'address' => 'Bandaranaike International Airport, Katunayake',
                'telephone' => '031 222 3344',
                'status' => 1
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['name' => $branch['name']],
                $branch
            );
        }
    }
}
