<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InventoryRequest;
use App\Models\InventoryRequestItem;
use App\Models\User;
use App\Models\Department;
use App\Models\Item;
use Carbon\Carbon;

class InventoryRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the supervisor user
        $supervisor = User::where('role', 'supervisor')->first();
        
        if (!$supervisor) {
            return; // No supervisor found
        }

        // Get departments and items
        $departments = Department::all();
        $items = Item::take(10)->get(); // Get first 10 items

        if ($departments->isEmpty() || $items->isEmpty()) {
            return; // No data to work with
        }

        // Create sample inventory requests
        $requests = [
            [
                'date_time' => Carbon::create(2025, 10, 8, 13, 50),
                'department_id' => $departments->where('name', 'Bakery')->first()->id ?? $departments->first()->id,
                'items' => [
                    ['item_id' => $items[0]->id, 'quantity' => 50],
                    ['item_id' => $items[1]->id, 'quantity' => 30],
                    ['item_id' => $items[2]->id, 'quantity' => 30],
                ],
                'notes' => 'Morning inventory restocking for bakery section'
            ],
            [
                'date_time' => Carbon::create(2025, 10, 8, 14, 00),
                'department_id' => $departments->where('name', 'Kitchen')->first()->id ?? $departments->skip(1)->first()->id,
                'items' => [
                    ['item_id' => $items[3]->id, 'quantity' => 100],
                ],
                'notes' => 'Urgent restock for lunch preparation'
            ],
            [
                'date_time' => Carbon::create(2025, 10, 7, 16, 30),
                'department_id' => $departments->where('name', 'Storage')->first()->id ?? $departments->skip(2)->first()->id,
                'items' => [
                    ['item_id' => $items[4]->id, 'quantity' => 25],
                    ['item_id' => $items[5]->id, 'quantity' => 40],
                    ['item_id' => $items[6]->id, 'quantity' => 35],
                    ['item_id' => $items[7]->id, 'quantity' => 20],
                ],
                'notes' => 'Weekly inventory update for storage department'
            ]
        ];

        foreach ($requests as $requestData) {
            // Create the inventory request
            $request = InventoryRequest::create([
                'user_id' => $supervisor->id,
                'department_id' => $requestData['department_id'],
                'date_time' => $requestData['date_time'],
                'status' => 'completed',
                'notes' => $requestData['notes'],
            ]);

            // Create the inventory request items
            foreach ($requestData['items'] as $itemData) {
                InventoryRequestItem::create([
                    'inventory_request_id' => $request->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }
        }
    }
}
