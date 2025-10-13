<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Inventory;

class TestBranchInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get staff user (ID 2)
        $user = User::find(2);
        
        if (!$user) {
            $this->command->info('Staff user not found');
            return;
        }

        $this->command->info("Setting up inventory for {$user->name} (Branch ID: {$user->branch_id})");

        // Get some items
        $items = Item::take(5)->get();

        foreach ($items as $item) {
            $stock = rand(10, 100); // Random stock between 10 and 100
            
            Inventory::updateOrCreate(
                [
                    'item_id' => $item->id, 
                    'branch_id' => $user->branch_id
                ],
                [
                    'current_stock' => $stock, 
                    'low_stock_alert' => 10
                ]
            );

            $this->command->info("Created inventory for {$item->item_name} with stock: {$stock}");
        }

        $this->command->info('Branch inventory setup complete!');
    }
}