<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Bakery Items
            ['item_name' => 'Bacon Egg Pastry', 'item_code' => 'BEP001', 'category' => 'Bakery Items', 'price' => 170.00],
            ['item_name' => 'Butter Croissants', 'item_code' => 'BC001', 'category' => 'Bakery Items', 'price' => 200.00],
            ['item_name' => 'Cauliflower Gratin', 'item_code' => 'CG001', 'category' => 'Bakery Items', 'price' => 180.00],
            ['item_name' => 'Chicken Roll', 'item_code' => 'CR001', 'category' => 'Bakery Items', 'price' => 170.00],
            ['item_name' => 'Cheese Toast', 'item_code' => 'CT001', 'category' => 'Bakery Items', 'price' => 130.00],
            
            // Main Courses  
            ['item_name' => 'Cheese Chicken Croissant', 'item_code' => 'CCC001', 'category' => 'Main Courses', 'price' => 230.00],
            ['item_name' => 'Cheese Onion Pastry', 'item_code' => 'COP001', 'category' => 'Main Courses', 'price' => 160.00],
            ['item_name' => 'Cheese Croissants', 'item_code' => 'CCR001', 'category' => 'Main Courses', 'price' => 230.00],
            ['item_name' => 'Cheese Pizza', 'item_code' => 'CP001', 'category' => 'Main Courses', 'price' => 180.00],
            ['item_name' => 'Chicken Fried Rice', 'item_code' => 'CFR001', 'category' => 'Rice & Noodles', 'price' => 320.00],
            ['item_name' => 'Vegetable Fried Rice', 'item_code' => 'VFR001', 'category' => 'Rice & Noodles', 'price' => 280.00],
            ['item_name' => 'Chicken Noodles', 'item_code' => 'CN001', 'category' => 'Rice & Noodles', 'price' => 300.00],
            
            // Chicken Items
            ['item_name' => 'Chicken Cheese Cone', 'item_code' => 'CCC002', 'category' => 'Main Courses', 'price' => 260.00],
            ['item_name' => 'Chicken Cornish', 'item_code' => 'CCO001', 'category' => 'Main Courses', 'price' => 190.00],
            ['item_name' => 'Chicken Cutlet', 'item_code' => 'CCU001', 'category' => 'Main Courses', 'price' => 140.00],
            ['item_name' => 'Chicken Devilled Pizza', 'item_code' => 'CDP001', 'category' => 'Main Courses', 'price' => 300.00],
            ['item_name' => 'Chicken Ham Egg Pastry', 'item_code' => 'CHEP001', 'category' => 'Main Courses', 'price' => 200.00],
            ['item_name' => 'Chicken Ham Pastry', 'item_code' => 'CHP001', 'category' => 'Main Courses', 'price' => 180.00],
            ['item_name' => 'Chicken Pastry', 'item_code' => 'CP002', 'category' => 'Main Courses', 'price' => 170.00],
            ['item_name' => 'Chicken Patty', 'item_code' => 'CPT001', 'category' => 'Main Courses', 'price' => 140.00],
            ['item_name' => 'Chicken Pie', 'item_code' => 'CPI001', 'category' => 'Main Courses', 'price' => 180.00],
            ['item_name' => 'Chicken Pizza', 'item_code' => 'CPZ001', 'category' => 'Main Courses', 'price' => 180.00],
            ['item_name' => 'Chicken Roti', 'item_code' => 'CRO001', 'category' => 'Main Courses', 'price' => 150.00],
            ['item_name' => 'Chicken Samosa', 'item_code' => 'CS001', 'category' => 'Appetizers', 'price' => 180.00],
            ['item_name' => 'Chicken Sausage And Omelette Puff', 'item_code' => 'CSAOP001', 'category' => 'Main Courses', 'price' => 200.00],
            ['item_name' => 'Chicken Sherwarma', 'item_code' => 'CSH001', 'category' => 'Main Courses', 'price' => 280.00],
            ['item_name' => 'Chicken Vol-Au-vent', 'item_code' => 'CVAV001', 'category' => 'Main Courses', 'price' => 190.00],
            ['item_name' => 'Crispy Chicken Burger', 'item_code' => 'CCB001', 'category' => 'Main Courses', 'price' => 460.00],
            
            // Seafood
            ['item_name' => 'Fish & Chips', 'item_code' => 'FC001', 'category' => 'Seafood', 'price' => 380.00],
            ['item_name' => 'Grilled Fish', 'item_code' => 'GF001', 'category' => 'Seafood', 'price' => 420.00],
            ['item_name' => 'Prawns Curry', 'item_code' => 'PC001', 'category' => 'Seafood', 'price' => 450.00],
            
            // Sweet Items & Desserts
            ['item_name' => 'Chocolate Croissants', 'item_code' => 'CHOC001', 'category' => 'Desserts', 'price' => 200.00],
            ['item_name' => 'Danish Pastry', 'item_code' => 'DP001', 'category' => 'Desserts', 'price' => 250.00],
            ['item_name' => 'Chocolate Cake Slice', 'item_code' => 'CCS001', 'category' => 'Desserts', 'price' => 180.00],
            ['item_name' => 'Ice Cream Sundae', 'item_code' => 'ICS001', 'category' => 'Desserts', 'price' => 220.00],
            
            // Egg Items
            ['item_name' => 'Egg Potato Pastry', 'item_code' => 'EPP001', 'category' => 'Appetizers', 'price' => 150.00],
            ['item_name' => 'Egg Boat', 'item_code' => 'EB001', 'category' => 'Appetizers', 'price' => 180.00],
            ['item_name' => 'Egg Pastry', 'item_code' => 'EP001', 'category' => 'Appetizers', 'price' => 160.00],
            ['item_name' => 'Egg Roll', 'item_code' => 'ER001', 'category' => 'Appetizers', 'price' => 140.00],
            
            // Beverages
            ['item_name' => 'Fresh Orange Juice', 'item_code' => 'FOJ001', 'category' => 'Beverages', 'price' => 120.00],
            ['item_name' => 'Coffee (Hot)', 'item_code' => 'COF001', 'category' => 'Beverages', 'price' => 80.00],
            ['item_name' => 'Tea (Hot)', 'item_code' => 'TEA001', 'category' => 'Beverages', 'price' => 60.00],
            ['item_name' => 'Iced Coffee', 'item_code' => 'ICF001', 'category' => 'Beverages', 'price' => 100.00],
            ['item_name' => 'Soft Drinks', 'item_code' => 'SOD001', 'category' => 'Beverages', 'price' => 80.00],
            
            // Soups
            ['item_name' => 'Chicken Corn Soup', 'item_code' => 'CCS002', 'category' => 'Soups', 'price' => 150.00],
            ['item_name' => 'Vegetable Soup', 'item_code' => 'VS001', 'category' => 'Soups', 'price' => 120.00],
            ['item_name' => 'Mushroom Soup', 'item_code' => 'MS001', 'category' => 'Soups', 'price' => 140.00],
            
            // Salads
            ['item_name' => 'Caesar Salad', 'item_code' => 'CAS001', 'category' => 'Salads', 'price' => 180.00],
            ['item_name' => 'Garden Salad', 'item_code' => 'GS001', 'category' => 'Salads', 'price' => 160.00],
            ['item_name' => 'Fruit Salad', 'item_code' => 'FS001', 'category' => 'Salads', 'price' => 140.00],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(
                ['item_code' => $item['item_code']],
                $item + [
                    'description' => 'Delicious ' . $item['item_name'] . ' from Ravon Bakery & Restaurant',
                    'is_active' => true,
                ]
            );
        }
    }
}
