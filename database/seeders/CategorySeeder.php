<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Appetizers',
                'status' => 1
            ],
            [
                'name' => 'Main Courses',
                'status' => 1
            ],
            [
                'name' => 'Desserts',
                'status' => 1
            ],
            [
                'name' => 'Beverages',
                'status' => 1
            ],
            [
                'name' => 'Soups',
                'status' => 1
            ],
            [
                'name' => 'Salads',
                'status' => 1
            ],
            [
                'name' => 'Specials',
                'status' => 1
            ],
            [
                'name' => 'Bakery Items',
                'status' => 1
            ],
            [
                'name' => 'Rice & Noodles',
                'status' => 1
            ],
            [
                'name' => 'Seafood',
                'status' => 1
            ]
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
