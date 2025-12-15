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
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\KotController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Exception\Exception as EscposException;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\StockAdjustmentController;

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
    // Item branch prices management (admin)
    Route::prefix('items')->name('items.')->group(function () {
        // Branch price management routes removed as requested
    });

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

        // Admin Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/stock-report', [SupervisorController::class, 'inventoryHistory'])->name('stock-report');
            Route::get('/item-sales', [App\Http\Controllers\ItemSalesController::class, 'itemSales'])->name('item-sales');
            Route::post('/item-sales/filter', [App\Http\Controllers\ItemSalesController::class, 'filterItemSales'])->name('item-sales.filter');
            Route::post('/item-sales/details', [App\Http\Controllers\ItemSalesController::class, 'getItemDetails'])->name('item-sales.details');
            Route::get('/item-sales/export', [App\Http\Controllers\ItemSalesController::class, 'exportExcel'])->name('item-sales.export');
            Route::get('/item-sales/export-item-details', [App\Http\Controllers\ItemSalesController::class, 'exportItemDetails'])->name('item-sales.export-item-details');

            // Production Summary Routes for Admin/Director
            Route::get('/production-summary', [App\Http\Controllers\SupervisorController::class, 'productionSummary'])
                ->name('production-summary');
            Route::get('/production-summary/export', [App\Http\Controllers\SupervisorController::class, 'exportProductionSummary'])
                ->name('production-summary.export');
            Route::get('/production-item-details', [App\Http\Controllers\SupervisorController::class, 'getProductionItemDetails'])
                ->name('production-item-details');

            // Wastage Summary Report (Re-using SupervisorController logic)
            Route::get('/wastage-summary', [SupervisorController::class, 'wastageSummary'])
                ->name('wastage-summary');
            Route::get('/wastage-summary/export', [SupervisorController::class, 'exportWastageSummary'])
                ->name('wastage-summary.export');
            Route::get('/wastage-item-details', [SupervisorController::class, 'getWastageItemDetails'])
                ->name('wastage-item-details');
        });
    });

    // POS System - ONLY staff members can access
    Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::post('/process-sale', [POSController::class, 'processSale'])->name('process-sale');
        Route::get('/receipt/{sale}', [POSController::class, 'receipt'])->name('receipt');
        Route::post('/clear-session', [POSController::class, 'clearSession'])->name('clear-session');
    });

    // Sales Report - Special authenticated users can access
    Route::prefix('sales-report')->name('sales-report.')->group(function () {
        Route::get('/', [SalesReportController::class, 'index'])->name('index');
        Route::get('/sale-items/{sale}', [SalesReportController::class, 'getSaleItems'])->name('sale-items');
        Route::get('/receipt/{sale}', [SalesReportController::class, 'receipt'])->name('receipt');
        Route::get('/export', [SalesReportController::class, 'exportExcel'])->name('export');
        Route::post('/sale/{sale}/status', [SalesReportController::class, 'updateStatus'])->name('sale.update-status');
        Route::get('/deleted', [SalesReportController::class, 'delete'])->name('deleted');
        Route::get('/deleted/export', [SalesReportController::class, 'exportDeleted'])->name('deleted.export');
    });

    // Sales Report - All authenticated users can access
    Route::prefix('sales-report2')->name('sales-report2.')->group(function () {
        Route::get('/', [SalesReportController::class, 'index2'])->name('index2');
        Route::get('/sale-items/{sale}', [SalesReportController::class, 'getSaleItems'])->name('sale-items');
        Route::get('/receipt/{sale}', [SalesReportController::class, 'receipt'])->name('receipt');
        Route::get('/export', [SalesReportController::class, 'exportExcel2'])->name('export2');
        Route::post('/sale/{sale}/status', [SalesReportController::class, 'updateStatus'])->name('sale.update-status');
    });


    // Supervisor routes - Only supervisors can access
    Route::middleware('can:supervisor-access')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');
        Route::get('/add-inventory', [SupervisorController::class, 'addInventory'])->name('add-inventory');
        Route::post('/store-inventory', [SupervisorController::class, 'storeInventory'])->name('store-inventory');
        Route::get('/inventory-history', [SupervisorController::class, 'inventoryHistory'])->name('inventory-history');
        Route::get('/inventory-history/export', [SupervisorController::class, 'exportInventoryHistory'])->name('inventory-history.export');
        Route::get('/create-department', [SupervisorController::class, 'createDepartment'])->name('create-department');
        Route::post('/store-department', [SupervisorController::class, 'storeDepartment'])->name('store-department');
        Route::get('/api/items', [SupervisorController::class, 'getItems'])->name('api.items');

        // Wastage routes
        Route::get('/add-wastage', [SupervisorController::class, 'addWastage'])->name('add-wastage');
        Route::post('/store-wastage', [SupervisorController::class, 'storeWastage'])->name('store-wastage');
        Route::get('/wastage-view', [SupervisorController::class, 'wastageView'])->name('wastage-view');

        Route::get('/reports/item-transaction', [SupervisorController::class, 'itemTransactionDetails'])
            ->name('reports.item-transaction');

        // Production (Inventory Requests added by supervisor to main stock)
        Route::prefix('productions')->name('productions.')->group(function () {
            Route::get('/', [SupervisorController::class, 'productions'])->name('index');
            Route::get('/export', [SupervisorController::class, 'exportProductions'])->name('export');
            Route::get('/{inventoryRequest}', [SupervisorController::class, 'showProduction'])->name('show');
            Route::get('/{inventoryRequest}/export', [SupervisorController::class, 'exportProductionDetails'])->name('export-details');
            Route::get('/{inventoryRequest}/edit', [SupervisorController::class, 'editProduction'])->name('edit');
            Route::put('/{inventoryRequest}', [SupervisorController::class, 'updateProduction'])->name('update');
            Route::delete('/{inventoryRequest}', [SupervisorController::class, 'destroyProduction'])->name('destroy');
        });

        // Stock Transfer routes (Supervisor only)
        Route::prefix('stock-transfer')->name('stock-transfer.')->group(function () {
            Route::get('/', [StockTransferController::class, 'index'])->name('index');
            Route::get('/by-status', [StockTransferController::class, 'byStatus'])->name('by-status');
            Route::get('/create', [StockTransferController::class, 'create'])->name('create');
            Route::post('/', [StockTransferController::class, 'store'])->name('store');
            Route::get('/{stockTransfer}', [StockTransferController::class, 'show'])->name('show');
            Route::delete('/{stockTransfer}', [StockTransferController::class, 'destroy'])->name('destroy');
            Route::get('/api/inventory/{item}', [StockTransferController::class, 'getInventory'])->name('api.inventory');
            Route::get('/api/all-inventory', [StockTransferController::class, 'getAllInventory'])->name('api.all-inventory');
        });

        Route::get('/stock-adjustment', [StockAdjustmentController::class, 'index'])
        ->name('stock-adjustment.index'); // List View

        // --- ADD STOCK ADJUSTMENT ROUTES HERE ---
        Route::get('/stock-adjustment/create', [StockAdjustmentController::class, 'create'])
            ->name('stock-adjustment.create');

        Route::post('/stock-adjustment/store', [StockAdjustmentController::class, 'store'])
            ->name('stock-adjustment.store');

        Route::get('/stock-adjustment/{id}', [StockAdjustmentController::class, 'show'])
            ->name('stock-adjustment.show'); // Detail View

        // View Route for Production Summary Report
        Route::get('/reports/production-summary', [SupervisorController::class, 'productionSummary'])
        ->name('reports.production-summary');
        // Export Route
        Route::get('/reports/production-summary/export', [SupervisorController::class, 'exportProductionSummary'])
        ->name('reports.production-summary.export');
        // API route to fetch details for the modal
        Route::get('/reports/production-item-details', [SupervisorController::class, 'getProductionItemDetails'])
        ->name('reports.production-item-details');

        // Wastage Summary Report
        Route::get('/reports/wastage-summary', [SupervisorController::class, 'wastageSummary'])
            ->name('reports.wastage-summary');
        Route::get('/reports/wastage-summary/export', [SupervisorController::class, 'exportWastageSummary'])
            ->name('reports.wastage-summary.export');
        Route::get('/reports/wastage-item-details', [SupervisorController::class, 'getWastageItemDetails'])
            ->name('reports.wastage-item-details');
    });






    // Staff routes for inventory management
    Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('/branch-inventory', [App\Http\Controllers\StaffController::class, 'branchInventory'])->name('branch-inventory');

        // Branch Wastage routes
        Route::get('/add-branch-wastage', [App\Http\Controllers\StaffController::class, 'addBranchWastage'])->name('add-branch-wastage');
        Route::post('/store-branch-wastage', [App\Http\Controllers\StaffController::class, 'storeBranchWastage'])->name('store-branch-wastage');
        Route::get('/branch-wastage-view', [App\Http\Controllers\StaffController::class, 'branchWastageView'])->name('branch-wastage-view');

        // Staff Stock Transfer routes
        Route::prefix('stock-transfer')->name('stock-transfer.')->group(function () {
            Route::get('/', [App\Http\Controllers\StaffController::class, 'stockTransferIndex'])->name('index');
            Route::get('/create', [App\Http\Controllers\StaffController::class, 'createStockTransfer'])->name('create');
            Route::post('/', [App\Http\Controllers\StaffController::class, 'storeStockTransfer'])->name('store');
            Route::get('/api/inventory/{item}', [App\Http\Controllers\StaffController::class, 'getStaffInventory'])->name('api.inventory');
            Route::get('/api/all-inventory', [App\Http\Controllers\StaffController::class, 'getAllStaffInventory'])->name('api.all-inventory');
        });

        // --- NEW ORDER ROUTES ---
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [App\Http\Controllers\OrderController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\OrderController::class, 'create'])->name('create');
            Route::post('/store', [App\Http\Controllers\OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('show');
        });
    });

    // Stock Transfer routes for all branch staff (to receive transfers)
    Route::prefix('stock-transfer')->name('stock-transfer.')->group(function () {
        Route::get('/transfers', [StockTransferController::class, 'showTransfers'])->name('transfers');
        Route::get('/pending', [StockTransferController::class, 'pending'])->name('pending');
        Route::get('/{stockTransfer}', [StockTransferController::class, 'show'])->name('show');
        Route::post('/{stockTransfer}/accept', [StockTransferController::class, 'accept'])->name('accept');
        Route::post('/{stockTransfer}/reject', [StockTransferController::class, 'reject'])->name('reject');
    });
});

Route::post('/qz/sign', [PrinterController::class, 'signQzRequest']);

// Test page for double-click prevention (can be removed in production)
Route::get('/test-double-click-prevention', function () {
    return view('test-double-click-prevention');
})->name('test.double-click');

require __DIR__ . '/auth.php';
