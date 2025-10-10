<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Item;
use App\Models\InventoryRequest;
use App\Models\InventoryRequestItem;
use App\Models\Inventory;
use App\Models\Wastage;
use App\Models\WastageItem;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
{
    /**
     * Display supervisor dashboard
     */
    public function dashboard()
    {
        // Get recent inventory requests by this supervisor
        $recentRequests = InventoryRequest::with(['department', 'inventoryRequestItems.item'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get total inventory requests count
        $totalRequests = InventoryRequest::where('user_id', Auth::id())->count();

        // Get inventory summary
        $inventoryCount = Inventory::count();
        $lowStockItems = Inventory::whereRaw('current_stock <= low_stock_alert')->count();

        // Get wastage statistics
        $totalWastages = Wastage::where('user_id', Auth::id())->count();
        $recentWastages = Wastage::with(['wastageItems.item'])
            ->where('user_id', Auth::id())
            ->orderBy('date_time', 'desc')
            ->take(5)
            ->get();

        // Get stock transfer statistics
        $totalTransfers = StockTransfer::where('created_by', Auth::id())->count();
        $pendingTransfers = StockTransfer::where('created_by', Auth::id())
            ->where('status', 'pending')
            ->count();
        $recentTransfers = StockTransfer::with(['toBranch', 'transferItems.item'])
            ->where('created_by', Auth::id())
            ->orderBy('date_time', 'desc')
            ->take(5)
            ->get();

        return view('supervisor.dashboard', compact(
            'recentRequests',
            'totalRequests',
            'inventoryCount',
            'lowStockItems',
            'totalWastages',
            'recentWastages',
            'totalTransfers',
            'pendingTransfers',
            'recentTransfers'
        ));
    }

    /**
     * Show the form for adding inventory
     */
    public function addInventory()
    {
        $departments = Department::where('is_active', true)->get();
        $items = Item::with('inventory')
                    ->where('is_active', true)
                    ->get();

        return view('supervisor.add-inventory', compact('departments', 'items'));
    }

    /**
     * Store inventory request
     */
    public function storeInventory(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'date_time' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            // Create inventory request
            $inventoryRequest = InventoryRequest::create([
                'user_id' => Auth::id(),
                'department_id' => $request->department_id,
                'date_time' => $request->date_time,
                'status' => 'completed',
                'notes' => $request->notes,
            ]);

            // Create inventory request items and update inventory
            foreach ($request->items as $itemData) {
                // Create inventory request item
                InventoryRequestItem::create([
                    'inventory_request_id' => $inventoryRequest->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                ]);

                // Update inventory
                $inventory = Inventory::where('item_id', $itemData['item_id'])->first();
                if ($inventory) {
                    $inventory->increment('current_stock', $itemData['quantity']);
                } else {
                    // Create new inventory record if it doesn't exist
                    Inventory::create([
                        'item_id' => $itemData['item_id'],
                        'current_stock' => $itemData['quantity'],
                        'low_stock_alert' => 10, // default value
                    ]);
                }
            }
        });

        return redirect()->route('supervisor.dashboard')
            ->with('success', 'Inventory has been added successfully!');
    }

    /**
     * Get items data for AJAX requests
     */
    public function getItems()
    {
        $items = Item::with('inventory')
            ->where('is_active', true)
            ->select('id', 'item_name', 'item_code', 'price')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'item_code' => $item->item_code,
                    'price' => $item->price,
                    'current_stock' => $item->inventory ? $item->inventory->current_stock : 0,
                ];
            });

        return response()->json($items);
    }

    /**
     * Show the form for creating a new department
     */
    public function createDepartment()
    {
        return view('supervisor.create-department');
    }

    /**
     * Store a new department
     */
    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('supervisor.add-inventory')
            ->with('success', 'Department created successfully!');
    }

    /**
     * Show available stock items organized by category
     */
    public function inventoryHistory()
    {
        // Get all active items with their current inventory and group by category
        $itemsByCategory = Item::with('inventory')
            ->where('is_active', true)
            ->get()
            ->filter(function ($item) {
                return $item->inventory && $item->inventory->current_stock > 0;
            })
            ->groupBy('category')
            ->map(function ($categoryItems, $categoryName) {
                return [
                    'category_name' => $categoryName,
                    'total_items' => $categoryItems->count(),
                    'total_stock' => $categoryItems->sum(function ($item) {
                        return $item->inventory ? $item->inventory->current_stock : 0;
                    }),
                    'items' => $categoryItems->map(function ($item) {
                        $inventory = $item->inventory;
                        return [
                            'id' => $item->id,
                            'name' => $item->item_name,
                            'item_code' => $item->item_code,
                            'price' => $item->price,
                            'current_stock' => $inventory ? $inventory->current_stock : 0,
                            'is_low_stock' => $inventory && $inventory->current_stock <= 10
                        ];
                    })->values()->all()
                ];
            });

        return view('supervisor.inventory-history', compact('itemsByCategory'));
    }

    /**
     * Show the form for adding wastage
     */
    public function addWastage()
    {
        $items = Item::with('inventory')
            ->where('is_active', true)
            ->get()
            ->filter(function ($item) {
                return $item->inventory && $item->inventory->current_stock > 0;
            })
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'item_code' => $item->item_code,
                    'available_stock' => $item->inventory->current_stock
                ];
            });

        return view('supervisor.add-wastage', compact('items'));
    }

    /**
     * Store wastage record
     */
    public function storeWastage(Request $request)
    {
        $request->validate([
            'date_time' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.wasted_quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Custom validation to check available stock from inventory
        foreach ($request->items as $index => $itemData) {
            $inventory = Inventory::where('item_id', $itemData['item_id'])->first();
            $availableStock = $inventory ? $inventory->current_stock : 0;
            
            if ($itemData['wasted_quantity'] > $availableStock) {
                $item = Item::find($itemData['item_id']);
                return back()->withErrors([
                    "items.{$index}.wasted_quantity" => "Wasted quantity for '{$item->item_name}' cannot exceed available inventory stock ({$availableStock})."
                ])->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            // Create wastage record
            $wastage = Wastage::create([
                'user_id' => Auth::id(),
                'date_time' => $request->date_time,
                'remarks' => $request->remarks,
            ]);

            // Create wastage items and update inventory
            foreach ($request->items as $itemData) {
                $inventory = Inventory::where('item_id', $itemData['item_id'])->first();
                $previousStock = $inventory ? $inventory->current_stock : 0;

                // Create wastage item record
                WastageItem::create([
                    'wastage_id' => $wastage->id,
                    'item_id' => $itemData['item_id'],
                    'wasted_quantity' => $itemData['wasted_quantity'],
                    'previous_stock' => $previousStock,
                ]);

                // Reduce inventory stock
                if ($inventory) {
                    $inventory->decrement('current_stock', $itemData['wasted_quantity']);
                }
            }
        });

        return redirect()->route('supervisor.dashboard')
            ->with('success', 'Wastage has been recorded successfully and inventory has been updated!');
    }

    /**
     * Show wastage records for supervisor
     */
    public function wastageView(Request $request)
    {
        $query = Wastage::with(['wastageItems.item'])
            ->where('user_id', Auth::id())
            ->orderBy('date_time', 'desc');

        // Filter by date if provided
        if ($request->filled('date_from')) {
            $query->whereDate('date_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_time', '<=', $request->date_to);
        }

        // Filter by item name if provided
        if ($request->filled('item_name')) {
            $query->whereHas('wastageItems.item', function ($q) use ($request) {
                $q->where('item_name', 'LIKE', '%' . $request->item_name . '%');
            });
        }

        $wastages = $query->paginate(10);

        return view('supervisor.wastage-view', compact('wastages'));
    }
}
