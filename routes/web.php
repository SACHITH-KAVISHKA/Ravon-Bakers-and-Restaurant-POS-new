<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SupervisorController;
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

    // Category Management - All users can view, only admin can modify
    Route::resource('categories', CategoryController::class);

    // Inventory Management - All users can view, only admin can modify
    Route::resource('inventory', InventoryController::class);

    // User Management - Only admin can access
    Route::resource('users', UserController::class)->middleware('can:manage-users');

    // Branch Management - Only admin can access
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('branches', BranchController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/api/branches/active', [BranchController::class, 'getActiveBranches'])->name('branches.active');
    });

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
        Route::post('/sale/{sale}/status', [SalesReportController::class, 'updateStatus'])->name('sale.update-status');
    });

    // Supervisor routes - Only supervisors can access
    Route::middleware('can:supervisor-access')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
        Route::get('/add-inventory', [SupervisorController::class, 'addInventory'])->name('add-inventory');
        Route::post('/store-inventory', [SupervisorController::class, 'storeInventory'])->name('store-inventory');
        Route::get('/inventory-history', [SupervisorController::class, 'inventoryHistory'])->name('inventory-history');
        Route::get('/create-department', [SupervisorController::class, 'createDepartment'])->name('create-department');
        Route::post('/store-department', [SupervisorController::class, 'storeDepartment'])->name('store-department');
        Route::get('/api/items', [SupervisorController::class, 'getItems'])->name('api.items');
    });
});

require __DIR__ . '/auth.php';
