<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Cream Bun Stock Changes in MASKreeda Branch ===\n";
echo "Period: 2025-11-14 00:00:00 → Current Date\n";
echo "Calculation Method: FORWARD (Net Changes)\n\n";

// Find Cream Bun item
$item = DB::table('items')->where('item_name', 'LIKE', '%Cream Bun%')->first();

if (!$item) {
    echo "Item 'Cream Bun' not found!\n";
    exit;
}

echo "Item Found: {$item->item_name} (ID: {$item->id})\n";
echo "Branch: MASKreeda (ID: 5)\n\n";

$fromDate = '2025-11-14 00:00:00';
$toDate = date('Y-m-d H:i:s'); // Current date/time
$mainBranchId = 1;
$maskreedaBranchId = 5;

// 1. Productions (added to Main Branch) - BETWEEN from_date AND to_date
echo "1. PRODUCTIONS in Main Branch (during period):\n";
$productions = DB::table('inventory_requests')
    ->join('inventory_request_items', 'inventory_requests.id', '=', 'inventory_request_items.inventory_request_id')
    ->where('inventory_request_items.item_id', $item->id)
    ->where('inventory_requests.status', 'completed')
    ->where('inventory_requests.date_time', '>=', $fromDate)
    ->where('inventory_requests.date_time', '<=', $toDate)
    ->select('inventory_requests.date_time', 'inventory_request_items.quantity')
    ->get();

$totalProduction = $productions->sum('quantity');
foreach ($productions as $p) {
    echo "   {$p->date_time}: +{$p->quantity}\n";
}
echo "   TOTAL: +{$totalProduction}\n\n";

// 2. Transfers TO MASKreeda - BETWEEN from_date AND to_date
echo "2. TRANSFERS IN to MASKreeda (during period):\n";
$transfersIn = DB::table('stock_transfers')
    ->join('stock_transfer_items', 'stock_transfers.id', '=', 'stock_transfer_items.transfer_id')
    ->where('stock_transfer_items.item_id', $item->id)
    ->where('stock_transfers.to_branch_id', $maskreedaBranchId)
    ->where('stock_transfers.status', 'accepted')
    ->where('stock_transfers.date_time', '>=', $fromDate)
    ->where('stock_transfers.date_time', '<=', $toDate)
    ->select('stock_transfers.date_time', 'stock_transfer_items.quantity')
    ->get();

$totalTransfersIn = $transfersIn->sum('quantity');
foreach ($transfersIn as $t) {
    echo "   {$t->date_time}: +{$t->quantity}\n";
}
echo "   TOTAL: +{$totalTransfersIn}\n\n";

// 3. Transfers OUT from MASKreeda - BETWEEN from_date AND to_date
echo "3. TRANSFERS OUT from MASKreeda (during period):\n";
$transfersOut = DB::table('stock_transfers')
    ->join('stock_transfer_items', 'stock_transfers.id', '=', 'stock_transfer_items.transfer_id')
    ->where('stock_transfer_items.item_id', $item->id)
    ->where('stock_transfers.from_branch_id', $maskreedaBranchId)
    ->where('stock_transfers.status', 'accepted')
    ->where('stock_transfers.date_time', '>=', $fromDate)
    ->where('stock_transfers.date_time', '<=', $toDate)
    ->select('stock_transfers.date_time', 'stock_transfer_items.quantity')
    ->get();

$totalTransfersOut = $transfersOut->sum('quantity');
foreach ($transfersOut as $t) {
    echo "   {$t->date_time}: -{$t->quantity}\n";
}
echo "   TOTAL: -{$totalTransfersOut}\n\n";

// 4. Wastages from MASKreeda - BETWEEN from_date AND to_date
echo "4. WASTAGES from MASKreeda (during period):\n";
$wastages = DB::table('wastages')
    ->join('wastage_items', 'wastages.id', '=', 'wastage_items.wastage_id')
    ->where('wastage_items.item_id', $item->id)
    ->where('wastages.branch_id', $maskreedaBranchId)
    ->where('wastages.date_time', '>=', $fromDate)
    ->where('wastages.date_time', '<=', $toDate)
    ->select('wastages.date_time', 'wastage_items.wasted_quantity')
    ->get();

$totalWastage = $wastages->sum('wasted_quantity');
foreach ($wastages as $w) {
    echo "   {$w->date_time}: -{$w->wasted_quantity}\n";
}
echo "   TOTAL: -{$totalWastage}\n\n";

// 5. Sales from MASKreeda - BETWEEN from_date AND to_date
echo "5. SALES from MASKreeda (during period):\n";
$sales = DB::table('sales')
    ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
    ->where('sale_items.item_id', $item->id)
    ->where('sales.branch_id', $maskreedaBranchId)
    ->where('sales.status', 1)
    ->where('sales.created_at', '>=', $fromDate)
    ->where('sales.created_at', '<=', $toDate)
    ->select('sales.created_at', 'sale_items.quantity')
    ->get();

$totalSales = $sales->sum('quantity');
foreach ($sales as $s) {
    echo "   {$s->created_at}: -{$s->quantity}\n";
}
echo "   TOTAL: -{$totalSales}\n\n";

// Calculate Net Stock Changes
echo "========================================\n";
echo "NET STOCK CHANGES (Forward Calculation):\n";
echo "========================================\n";
echo "Period: {$fromDate} → {$toDate}\n";
echo "----------------------------------------\n";
echo "Transfers IN:    +{$totalTransfersIn}\n";
echo "Transfers OUT:   -{$totalTransfersOut}\n";
echo "Wastages:        -{$totalWastage}\n";
echo "Sales:           -{$totalSales}\n";
echo "----------------------------------------\n";

$netChange = $totalTransfersIn - $totalTransfersOut - $totalWastage - $totalSales;
echo "NET CHANGE:      {$netChange}\n";
echo "========================================\n";
echo "\nNote: This shows stock CHANGES during the period,\n";
echo "not the absolute stock quantity.\n";
echo "Positive = net increase, Negative = net decrease\n";
