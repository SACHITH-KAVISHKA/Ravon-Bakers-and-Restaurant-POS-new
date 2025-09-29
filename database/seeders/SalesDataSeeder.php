<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SalesDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some items for the sale items
        $items = Item::take(10)->get();
        
        if ($items->count() == 0) {
            // Create some sample items if none exist
            for ($i = 1; $i <= 10; $i++) {
                Item::create([
                    'item_name' => "Sample Item $i",
                    'item_code' => 'SAMPLE' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'category' => 'General',
                    'price' => rand(500, 5000) / 100, // Random price between 5.00 and 50.00
                    'description' => "Description for sample item $i",
                ]);
            }
            $items = Item::take(10)->get();
        } else {
            // Update existing items that don't have names
            foreach ($items as $item) {
                if (empty($item->item_name)) {
                    $item->update([
                        'item_name' => "Item " . $item->id,
                        'description' => "Description for item " . $item->id,
                        'price' => $item->price ?: rand(500, 5000) / 100,
                    ]);
                }
            }
            $items = Item::take(10)->get(); // Refresh the collection
        }

        // Create sales for the last 7 days
        for ($day = 6; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            $salesCount = rand(5, 15); // Random number of sales per day
            
            for ($sale = 1; $sale <= $salesCount; $sale++) {
                // Create random sale data
                $subtotal = 0;
                $receiptNo = 'RCP' . $date->format('Ymd') . str_pad($sale, 3, '0', STR_PAD_LEFT);
                
                $saleData = Sale::create([
                    'receipt_no' => $receiptNo,
                    'branch' => 'Main Branch',
                    'terminal' => 'POS-01',
                    'user_name' => fake()->name(),
                    'subtotal' => 0, // Will be updated after creating items
                    'discount' => rand(0, 500) / 100, // Random discount 0-5.00
                    'tax' => 0, // Will be calculated
                    'total' => 0, // Will be calculated
                    'payment_method' => fake()->randomElement(['cash', 'card', 'card_and_cash', 'credit', 'complimentary', 'online']),
                    'card_type' => fake()->randomElement(['Visa', 'MasterCard', 'Amex', null]),
                    'card_no' => fake()->randomElement(['****1234', '****5678', '****9012', null]),
                    'customer_payment' => 0, // Will be calculated
                    'balance' => 0, // Will be calculated
                    'created_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                    'updated_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                ]);

                // Create 1-5 random items for this sale
                $itemCount = rand(1, 5);
                for ($item = 1; $item <= $itemCount; $item++) {
                    $selectedItem = $items->random();
                    $quantity = rand(1, 3);
                    $unitPrice = $selectedItem->price;
                    $totalPrice = $quantity * $unitPrice;
                    $subtotal += $totalPrice;
                    
                    SaleItem::create([
                        'sale_id' => $saleData->id,
                        'item_id' => $selectedItem->id,
                        'item_name' => $selectedItem->item_name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);
                }
                
                // Update sale totals
                $discount = $saleData->discount;
                $tax = ($subtotal - $discount) * 0.1; // 10% tax
                $total = $subtotal - $discount + $tax;
                $customerPayment = $total + rand(-200, 500) / 100; // Sometimes overpay, sometimes underpay
                $balance = $customerPayment - $total;
                
                $saleData->update([
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'customer_payment' => $customerPayment,
                    'balance' => $balance,
                ]);
            }
        }
        
        $this->command->info('Sample sales data created successfully!');
    }
}
