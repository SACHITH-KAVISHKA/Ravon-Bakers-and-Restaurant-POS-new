<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Item;
use App\Models\InventoryRequest;
use App\Models\InventoryRequestItem;
use App\Models\Inventory;
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

        return view('supervisor.dashboard', compact(
            'recentRequests',
            'totalRequests',
            'inventoryCount',
            'lowStockItems'
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
     * Show inventory history for supervisor
     */
    public function inventoryHistory()
    {
        $inventoryRequests = InventoryRequest::with(['department', 'inventoryRequestItems.item'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('supervisor.inventory-history', compact('inventoryRequests'));
    }
}
