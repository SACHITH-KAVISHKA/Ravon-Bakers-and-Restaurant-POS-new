<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $totalItems = Item::count();
        $totalPurchases = Purchase::count();
        
        // Calculate stock value
        $stockValue = DB::table('items')
            ->join('inventories', 'items.id', '=', 'inventories.item_id')
            ->sum(DB::raw('items.price * inventories.current_stock'));
        
        // Get today's sales
        $todaySales = Sale::whereDate('created_at', today())->sum('total');
        
        // Get low stock items
        $lowStockItems = Inventory::with('item')
            ->whereRaw('current_stock <= low_stock_alert')
            ->limit(5)
            ->get();
        
        // Get recent sales
        $recentSales = Sale::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalItems',
            'totalPurchases', 
            'stockValue',
            'todaySales',
            'lowStockItems',
            'recentSales'
        ));
    }
}
