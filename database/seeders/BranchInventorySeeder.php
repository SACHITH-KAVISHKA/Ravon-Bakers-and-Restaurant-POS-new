<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Branch;

class BranchInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $items = Item::take(10)->get(); // Take first 10 items

        foreach ($branches as $branch) {
            foreach ($items as $item) {
                // Create inventory for each branch-item combination
                Inventory::updateOrCreate(
                    [
                        'item_id' => $item->id,
                        'branch_id' => $branch->id,
                    ],
                    [
                        'current_stock' => rand(50, 200),
                        'low_stock_alert' => rand(10, 20),
                    ]
                );
            }
        }
    }
}