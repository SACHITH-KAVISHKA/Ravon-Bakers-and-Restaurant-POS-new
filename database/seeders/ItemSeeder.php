<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Inventory;
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
            ['item_name' => 'Bacon Egg Pastry', 'item_code' => 'BEP001', 'category' => 'Bakery', 'price' => 170.00, 'stock_quantity' => 50],
            ['item_name' => 'Butter Croissants', 'item_code' => 'BC001', 'category' => 'Bakery', 'price' => 200.00, 'stock_quantity' => 30],
            ['item_name' => 'Cauliflower Gratin', 'item_code' => 'CG001', 'category' => 'Bakery', 'price' => 180.00, 'stock_quantity' => 25],
            ['item_name' => 'Chicken Roll', 'item_code' => 'CR001', 'category' => 'Bakery', 'price' => 170.00, 'stock_quantity' => 40],
            ['item_name' => 'Cheese Toast', 'item_code' => 'CT001', 'category' => 'Bakery', 'price' => 130.00, 'stock_quantity' => 35],
            
            // Savory Items  
            ['item_name' => 'Cheese Chicken Croissant', 'item_code' => 'CCC001', 'category' => 'Savory', 'price' => 230.00, 'stock_quantity' => 20],
            ['item_name' => 'Cheese Onion Pastry', 'item_code' => 'COP001', 'category' => 'Savory', 'price' => 160.00, 'stock_quantity' => 30],
            ['item_name' => 'Cheese Croissants', 'item_code' => 'CCR001', 'category' => 'Savory', 'price' => 230.00, 'stock_quantity' => 25],
            ['item_name' => 'Cheese Pizza', 'item_code' => 'CP001', 'category' => 'Savory', 'price' => 180.00, 'stock_quantity' => 15],
            
            // Chicken Items
            ['item_name' => 'Chicken Cheese Cone', 'item_code' => 'CCC002', 'category' => 'Chicken', 'price' => 260.00, 'stock_quantity' => 20],
            ['item_name' => 'Chicken Cornish', 'item_code' => 'CCO001', 'category' => 'Chicken', 'price' => 190.00, 'stock_quantity' => 25],
            ['item_name' => 'Chicken Cutlet', 'item_code' => 'CCU001', 'category' => 'Chicken', 'price' => 140.00, 'stock_quantity' => 30],
            ['item_name' => 'Chicken Devilled Pizza', 'item_code' => 'CDP001', 'category' => 'Chicken', 'price' => 300.00, 'stock_quantity' => 10],
            ['item_name' => 'Chicken Ham Egg Pastry', 'item_code' => 'CHEP001', 'category' => 'Chicken', 'price' => 200.00, 'stock_quantity' => 20],
            ['item_name' => 'Chicken Ham Pastry', 'item_code' => 'CHP001', 'category' => 'Chicken', 'price' => 180.00, 'stock_quantity' => 25],
            ['item_name' => 'Chicken Pastry', 'item_code' => 'CP002', 'category' => 'Chicken', 'price' => 170.00, 'stock_quantity' => 35],
            ['item_name' => 'Chicken Patty', 'item_code' => 'CPT001', 'category' => 'Chicken', 'price' => 140.00, 'stock_quantity' => 30],
            ['item_name' => 'Chicken Pie', 'item_code' => 'CPI001', 'category' => 'Chicken', 'price' => 180.00, 'stock_quantity' => 20],
            ['item_name' => 'Chicken Pizza', 'item_code' => 'CPZ001', 'category' => 'Chicken', 'price' => 180.00, 'stock_quantity' => 15],
            ['item_name' => 'Chicken Roti', 'item_code' => 'CRO001', 'category' => 'Chicken', 'price' => 150.00, 'stock_quantity' => 25],
            ['item_name' => 'Chicken Samosa', 'item_code' => 'CS001', 'category' => 'Chicken', 'price' => 180.00, 'stock_quantity' => 30],
            ['item_name' => 'Chicken Sausage And Omelette Puff', 'item_code' => 'CSAOP001', 'category' => 'Chicken', 'price' => 200.00, 'stock_quantity' => 15],
            ['item_name' => 'Chicken Sherwarma', 'item_code' => 'CSH001', 'category' => 'Chicken', 'price' => 280.00, 'stock_quantity' => 20],
            ['item_name' => 'Chicken Vol-Au-vent', 'item_code' => 'CVAV001', 'category' => 'Chicken', 'price' => 190.00, 'stock_quantity' => 20],
            
            // Sweet Items
            ['item_name' => 'Chocolate Croissants', 'item_code' => 'CHOC001', 'category' => 'Sweet', 'price' => 200.00, 'stock_quantity' => 25],
            ['item_name' => 'Crispy Chicken Burger', 'item_code' => 'CCB001', 'category' => 'Burgers', 'price' => 460.00, 'stock_quantity' => 15],
            ['item_name' => 'Danish Pastry', 'item_code' => 'DP001', 'category' => 'Sweet', 'price' => 250.00, 'stock_quantity' => 20],
            
            // Egg Items
            ['item_name' => 'Egg Potato Pastry', 'item_code' => 'EPP001', 'category' => 'Egg', 'price' => 150.00, 'stock_quantity' => 30],
            ['item_name' => 'Egg Boat', 'item_code' => 'EB001', 'category' => 'Egg', 'price' => 180.00, 'stock_quantity' => 25],
            ['item_name' => 'Egg Pastry', 'item_code' => 'EP001', 'category' => 'Egg', 'price' => 160.00, 'stock_quantity' => 35],
            ['item_name' => 'Egg Roll', 'item_code' => 'ER001', 'category' => 'Egg', 'price' => 140.00, 'stock_quantity' => 40],
        ];

        foreach ($items as $item) {
            $createdItem = Item::create($item + [
                'description' => 'Delicious ' . $item['item_name'] . ' from Revon Bakery',
                'is_active' => true,
            ]);

            // Create inventory record for each item
            Inventory::create([
                'item_id' => $createdItem->id,
                'current_stock' => $item['stock_quantity'],
                'low_stock_alert' => 5,
            ]);
        }
    }
}
