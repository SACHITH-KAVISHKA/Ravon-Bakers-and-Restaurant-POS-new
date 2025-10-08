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
            ['name' => 'Main Branch', 'status' => 1],
            ['name' => 'Downtown Branch', 'status' => 1],
            ['name' => 'Mall Branch', 'status' => 1],
            ['name' => 'Airport Branch', 'status' => 1],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['name' => $branch['name']],
                $branch
            );
        }
    }
}
