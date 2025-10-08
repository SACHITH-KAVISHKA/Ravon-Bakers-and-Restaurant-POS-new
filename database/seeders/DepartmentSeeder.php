<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Kitchen',
                'description' => 'Kitchen department responsible for food preparation',
                'is_active' => true,
            ],
            [
                'name' => 'Bakery',
                'description' => 'Bakery department for bread and pastry production',
                'is_active' => true,
            ],
            [
                'name' => 'Storage',
                'description' => 'Main storage facility',
                'is_active' => true,
            ],
            [
                'name' => 'Front Service',
                'description' => 'Customer service and dining area',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
