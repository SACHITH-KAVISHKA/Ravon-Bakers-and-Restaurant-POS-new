<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Redirect supervisors to their own dashboard
        if (Auth::check() && Auth::user()->role === 'supervisor') {
            return redirect()->route('supervisor.dashboard');
        }

        // Get dashboard statistics
        $totalItems = Item::count();
        $totalPurchases = Purchase::count();
        
        // Calculate total value (simplified without stock tracking)
        $totalValue = 0; // Since we removed inventory, this can be set to 0 or calculated differently
        
        // Get today's sales
        $todaySales = Sale::whereDate('created_at', today())
        ->where('status', 1) // Only count completed sales
        ->sum('total');
        
        // Get recent sales
        $recentSales = Sale::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalItems',
            'totalPurchases', 
            'totalValue',
            'todaySales',
            'recentSales'
        ));
    }
}
