<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        // Redirect supervisors to their own dashboard
        if (Auth::check() && Auth::user()->role === 'supervisor') {
            return redirect()->route('supervisor.dashboard');
        }

        // Redirect admins to the daily sales report page
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('sales-report.index');
        }
        $user = Auth::user();

        // If staff, scope statistics to their branch
        if ($user && $user->role === 'staff' && $user->branch_id) {
            $branchId = $user->branch_id;

            // Total items in branch inventory (distinct item records)
            $totalItems = Inventory::where('branch_id', $branchId)->count();

            // Purchases related to items present in this branch (best-effort)
            $itemIds = Inventory::where('branch_id', $branchId)->pluck('item_id')->toArray();
            $totalPurchases = Purchase::whereIn('item_id', $itemIds)->count();

            // Total value of inventory in this branch (current_stock * item price)
            $totalValue = Inventory::where('branch_id', $branchId)
                ->join('items', 'inventories.item_id', '=', 'items.id')
                ->selectRaw('COALESCE(SUM(inventories.current_stock * items.price), 0) as total')
                ->value('total');

            // Prefer using sale.branch_id (available after migration); fall back to user_name mapping.
            $todaySalesQuery = Sale::whereDate('created_at', today())->where('status', 1);
            $recentSalesQuery = Sale::whereDate('created_at', today());

            // If sales records have branch_id populated, use it
            $hasBranchColumn = Schema::hasColumn('sales', 'branch_id');
            if ($hasBranchColumn) {
                $todaySalesQuery->where('branch_id', $branchId);
                $recentSalesQuery->where('branch_id', $branchId);
            } else {
                $userNames = User::where('branch_id', $branchId)->pluck('name')->toArray();
                if (count($userNames) > 0) {
                    $todaySalesQuery->whereIn('user_name', $userNames);
                    $recentSalesQuery->whereIn('user_name', $userNames);
                }
            }

            $todaySales = $todaySalesQuery->sum('total');

            $recentSales = $recentSalesQuery->orderBy('created_at', 'desc')->limit(5)->get();
        } else {
            // Global stats for admin/supervisor
            $totalItems = Item::count();
            $totalPurchases = Purchase::count();

            // Calculate total value globally (sum of central inventory or available inventory)
            $totalValue = 0;

            $todaySales = Sale::whereDate('created_at', today())
                ->where('status', 1) // Only count completed sales
                ->sum('total');

            $recentSales = Sale::whereDate('created_at', today())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact(
            'totalItems',
            'totalPurchases',
            'totalValue',
            'todaySales',
            'recentSales'
        ));
    }
}
