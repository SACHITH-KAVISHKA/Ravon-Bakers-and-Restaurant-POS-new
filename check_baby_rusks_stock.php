<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Baby Rusks Stock Changes Across All Branches ===\n";
echo "Period: 2025-11-14 00:00:00 → Current Date\n";
echo "Calculation Method: FORWARD (Net Changes)\n\n";

// Find Baby Rusks item by ID
$itemId = 197;
$item = DB::table('items')->where('id', $itemId)->first();

if (!$item) {
    echo "Item with ID {$itemId} not found!\n";
    exit;
}

echo "Item Found: {$item->item_name} (ID: {$item->id})\n";
echo "Item Code: {$item->item_code}\n\n";

$fromDate = '2025-11-14 00:00:00';
$toDate = date('Y-m-d H:i:s'); // Current date/time
$mainBranchId = 1;

// Get all branches
$branches = DB::table('branches')->where('status', 1)->orderBy('id')->get();

echo "========================================\n";
echo "ANALYZING ALL BRANCHES:\n";
echo "========================================\n\n";

foreach ($branches as $branch) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "BRANCH: {$branch->name} (ID: {$branch->id})\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    // 1. Productions (only for Main Branch)
    $totalProduction = 0;
    if ($branch->id == $mainBranchId) {
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
        if ($productions->count() > 0) {
            foreach ($productions as $p) {
                echo "   {$p->date_time}: +{$p->quantity}\n";
            }
        } else {
            echo "   (No productions)\n";
        }
        echo "   TOTAL: +{$totalProduction}\n\n";
    }

    // 2. Transfers TO this branch
    echo "2. TRANSFERS IN to {$branch->name} (during period):\n";
    $transfersIn = DB::table('stock_transfers')
        ->join('stock_transfer_items', 'stock_transfers.id', '=', 'stock_transfer_items.transfer_id')
        ->where('stock_transfer_items.item_id', $item->id)
        ->where('stock_transfers.to_branch_id', $branch->id)
        ->where('stock_transfers.status', 'accepted')
        ->where('stock_transfers.date_time', '>=', $fromDate)
        ->where('stock_transfers.date_time', '<=', $toDate)
        ->select('stock_transfers.date_time', 'stock_transfer_items.quantity', 'stock_transfers.from_branch_id')
        ->get();

    $totalTransfersIn = $transfersIn->sum('quantity');
    if ($transfersIn->count() > 0) {
        foreach ($transfersIn as $t) {
            $fromBranch = DB::table('branches')->where('id', $t->from_branch_id)->value('name');
            echo "   {$t->date_time}: +{$t->quantity} (from {$fromBranch})\n";
        }
    } else {
        echo "   (No transfers in)\n";
    }
    echo "   TOTAL: +{$totalTransfersIn}\n\n";

    // 3. Transfers OUT from this branch
    echo "3. TRANSFERS OUT from {$branch->name} (during period):\n";
    $transfersOut = DB::table('stock_transfers')
        ->join('stock_transfer_items', 'stock_transfers.id', '=', 'stock_transfer_items.transfer_id')
        ->where('stock_transfer_items.item_id', $item->id)
        ->where('stock_transfers.from_branch_id', $branch->id)
        ->where('stock_transfers.status', 'accepted')
        ->where('stock_transfers.date_time', '>=', $fromDate)
        ->where('stock_transfers.date_time', '<=', $toDate)
        ->select('stock_transfers.date_time', 'stock_transfer_items.quantity', 'stock_transfers.to_branch_id')
        ->get();

    $totalTransfersOut = $transfersOut->sum('quantity');
    if ($transfersOut->count() > 0) {
        foreach ($transfersOut as $t) {
            $toBranch = DB::table('branches')->where('id', $t->to_branch_id)->value('name');
            echo "   {$t->date_time}: -{$t->quantity} (to {$toBranch})\n";
        }
    } else {
        echo "   (No transfers out)\n";
    }
    echo "   TOTAL: -{$totalTransfersOut}\n\n";

    // 4. Wastages from this branch
    echo "4. WASTAGES from {$branch->name} (during period):\n";
    $wastages = DB::table('wastages')
        ->join('wastage_items', 'wastages.id', '=', 'wastage_items.wastage_id')
        ->where('wastage_items.item_id', $item->id)
        ->where('wastages.branch_id', $branch->id)
        ->where('wastages.date_time', '>=', $fromDate)
        ->where('wastages.date_time', '<=', $toDate)
        ->select('wastages.date_time', 'wastage_items.wasted_quantity')
        ->get();

    $totalWastage = $wastages->sum('wasted_quantity');
    if ($wastages->count() > 0) {
        foreach ($wastages as $w) {
            echo "   {$w->date_time}: -{$w->wasted_quantity}\n";
        }
    } else {
        echo "   (No wastages)\n";
    }
    echo "   TOTAL: -{$totalWastage}\n\n";

    // 5. Sales from this branch
    echo "5. SALES from {$branch->name} (during period):\n";
    $sales = DB::table('sales')
        ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
        ->where('sale_items.item_id', $item->id)
        ->where('sales.branch_id', $branch->id)
        ->where('sales.status', 1)
        ->where('sales.created_at', '>=', $fromDate)
        ->where('sales.created_at', '<=', $toDate)
        ->select('sales.created_at', 'sale_items.quantity')
        ->get();

    $totalSales = $sales->sum('quantity');
    if ($sales->count() > 0) {
        foreach ($sales as $s) {
            echo "   {$s->created_at}: -{$s->quantity}\n";
        }
    } else {
        echo "   (No sales)\n";
    }
    echo "   TOTAL: -{$totalSales}\n\n";

    // Calculate Net Change for this branch
    echo "----------------------------------------\n";
    echo "NET CHANGE FOR {$branch->name}:\n";
    echo "----------------------------------------\n";
    if ($branch->id == $mainBranchId) {
        echo "Productions:     +{$totalProduction}\n";
    }
    echo "Transfers IN:    +{$totalTransfersIn}\n";
    echo "Transfers OUT:   -{$totalTransfersOut}\n";
    echo "Wastages:        -{$totalWastage}\n";
    echo "Sales:           -{$totalSales}\n";
    echo "----------------------------------------\n";

    $netChange = $totalProduction + $totalTransfersIn - $totalTransfersOut - $totalWastage - $totalSales;
    $changeSymbol = $netChange >= 0 ? '+' : '';
    echo "NET CHANGE:      {$changeSymbol}{$netChange}\n\n";

    // Get current stock from inventories table
    $currentStock = DB::table('inventories')
        ->where('item_id', $item->id)
        ->where('branch_id', $branch->id)
        ->value('current_stock') ?? 0;
    
    echo "CURRENT STOCK IN DATABASE: {$currentStock}\n";
    echo "\n";
}

// Overall summary
echo "========================================\n";
echo "OVERALL SUMMARY\n";
echo "========================================\n";
echo "Period: {$fromDate} → {$toDate}\n\n";

// Calculate totals across all branches
$totalProductionsAll = DB::table('inventory_requests')
    ->join('inventory_request_items', 'inventory_requests.id', '=', 'inventory_request_items.inventory_request_id')
    ->where('inventory_request_items.item_id', $item->id)
    ->where('inventory_requests.status', 'completed')
    ->where('inventory_requests.date_time', '>=', $fromDate)
    ->where('inventory_requests.date_time', '<=', $toDate)
    ->sum('inventory_request_items.quantity');

$totalSalesAll = DB::table('sales')
    ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
    ->where('sale_items.item_id', $item->id)
    ->where('sales.status', 1)
    ->where('sales.created_at', '>=', $fromDate)
    ->where('sales.created_at', '<=', $toDate)
    ->sum('sale_items.quantity');

$totalWastageAll = DB::table('wastages')
    ->join('wastage_items', 'wastages.id', '=', 'wastage_items.wastage_id')
    ->where('wastage_items.item_id', $item->id)
    ->where('wastages.date_time', '>=', $fromDate)
    ->where('wastages.date_time', '<=', $toDate)
    ->sum('wastage_items.wasted_quantity');

$totalCurrentStock = DB::table('inventories')
    ->where('item_id', $item->id)
    ->sum('current_stock');

echo "Total Productions:  +{$totalProductionsAll}\n";
echo "Total Sales:        -{$totalSalesAll}\n";
echo "Total Wastages:     -{$totalWastageAll}\n";
echo "----------------------------------------\n";
$overallChange = $totalProductionsAll - $totalSalesAll - $totalWastageAll;
echo "Overall Net Change: " . ($overallChange >= 0 ? '+' : '') . "{$overallChange}\n";
echo "Total Current Stock: {$totalCurrentStock}\n";
echo "========================================\n";

echo "\nNote: This shows stock CHANGES during the period,\n";
echo "not the absolute stock quantity at a point in time.\n";
echo "Positive = net increase, Negative = net decrease\n";
