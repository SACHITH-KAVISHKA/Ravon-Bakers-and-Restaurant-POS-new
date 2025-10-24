<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\StockTransfer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share pending stock transfers count with all views
        View::composer('*', function ($view) {
            $pendingStockTransfersCount = 0;
            
            if (auth()->check()) {
                $user = auth()->user();
                
                // For staff users, count pending transfers to their branch
                if ($user->role === 'staff' && $user->branch_id) {
                    $pendingStockTransfersCount = StockTransfer::where('to_branch_id', $user->branch_id)
                        ->where('status', 'pending')
                        ->count();
                }
            }
            
            $view->with('pendingStockTransfersCount', $pendingStockTransfersCount);
        });
    }
}
