<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\Item;

class CentralInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = Item::take(10)->get(); // Take first 10 items

        foreach ($items as $item) {
            // Create central inventory for each item (branch_id = null)
            Inventory::updateOrCreate(
                [
                    'item_id' => $item->id,
                    'branch_id' => null, // Central inventory
                ],
                [
                    'current_stock' => rand(500, 1000), // Higher stock for central inventory
                    'low_stock_alert' => rand(50, 100),
                ]
            );
        }
    }
}