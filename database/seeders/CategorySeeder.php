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
                'description' => 'Start your meal with our delicious appetizers and starters',
                'status' => 1
            ],
            [
                'name' => 'Main Courses',
                'description' => 'Our signature main dishes and entrees',
                'status' => 1
            ],
            [
                'name' => 'Desserts',
                'description' => 'Sweet treats and desserts to end your meal perfectly',
                'status' => 1
            ],
            [
                'name' => 'Beverages',
                'description' => 'Hot and cold drinks, juices, and specialty beverages',
                'status' => 1
            ],
            [
                'name' => 'Soups',
                'description' => 'Warm and comforting soups for every season',
                'status' => 1
            ],
            [
                'name' => 'Salads',
                'description' => 'Fresh and healthy salad options',
                'status' => 1
            ],
            [
                'name' => 'Specials',
                'description' => 'Chef\'s special dishes and seasonal offerings',
                'status' => 1
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
