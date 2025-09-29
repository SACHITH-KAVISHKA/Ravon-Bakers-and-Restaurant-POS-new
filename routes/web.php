<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\SalesReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Item Management - All users can view, only admin can modify
    Route::resource('items', ItemController::class);
    
    // Stock Management routes (admin only)
    Route::middleware('can:manage-users')->group(function () {
        Route::post('/items/{item}/update-stock', [ItemController::class, 'updateStock'])->name('items.update-stock');
        Route::get('/items/low-stock', [ItemController::class, 'lowStockItems'])->name('items.low-stock');
        Route::get('/items/out-of-stock', [ItemController::class, 'outOfStockItems'])->name('items.out-of-stock');
    });

    // Category Management - All users can view, only admin can modify
    Route::resource('categories', CategoryController::class);

    // Inventory Management - All users can view, only admin can modify
    Route::resource('inventory', InventoryController::class);

    // User Management - Only admin can access
    Route::resource('users', UserController::class)->middleware('can:manage-users');

    // POS System - All authenticated users can access
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::post('/process-sale', [POSController::class, 'processSale'])->name('process-sale');
        Route::get('/receipt/{sale}', [POSController::class, 'receipt'])->name('receipt');
        Route::post('/clear-session', [POSController::class, 'clearSession'])->name('clear-session');
    });

    // Sales Report - All authenticated users can access
    Route::prefix('sales-report')->name('sales-report.')->group(function () {
        Route::get('/', [SalesReportController::class, 'index'])->name('index');
        Route::get('/sale-items/{sale}', [SalesReportController::class, 'getSaleItems'])->name('sale-items');
        Route::get('/export', [SalesReportController::class, 'exportExcel'])->name('export');
    });
});

require __DIR__ . '/auth.php';
