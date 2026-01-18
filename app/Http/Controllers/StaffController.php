<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\InventoryRequest;
use App\Models\InventoryRequestItem;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\User;
use App\Models\Wastage;
use App\Models\WastageItem;
use App\Models\Branch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;

class StaffController extends Controller
{
    /**
     * Display pending inventory requests for staff to accept
     */
    public function pendingInventoryRequests()
    {
        $pendingRequests = InventoryRequest::with(['department', 'user', 'inventoryRequestItems.item'])
            ->where('status', 'completed')
            ->whereHas('inventoryRequestItems', function($query) {
                $query->whereNull('received_by');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('staff.pending-inventory-requests', compact('pendingRequests'));
    }

    /**
     * Show details of a specific inventory request
     */
    public function showInventoryRequest(InventoryRequest $inventoryRequest)
    {
        $inventoryRequest->load(['department', 'user', 'inventoryRequestItems.item']);

        return view('staff.show-inventory-request', compact('inventoryRequest'));
    }

    /**
     * Accept specific items from an inventory request
     */
    public function acceptInventoryItems(Request $request, InventoryRequest $inventoryRequest)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*' => 'exists:inventory_request_items,id',
        ]);

        /** @var User|null $user */
        $user = Auth::user();
        $userId = (int) $user->id;
        $userBranchId = (int) $user->branch_id;

        // Ensure user exists and has a branch_id (staff should have branch assignment)
        if (!$user || !$userBranchId) {
            return redirect()->back()->with('error', 'You must be assigned to a branch to accept inventory items.');
        }

        try {
            DB::transaction(function () use ($request, $inventoryRequest, $userId, $userBranchId) {
                $itemIds = $request->items;

            // Get the inventory request items to be accepted
            $itemsToAccept = InventoryRequestItem::with('item')
                ->whereIn('id', $itemIds)
                ->where('inventory_request_id', $inventoryRequest->id)
                ->whereNull('received_by')
                ->get();

            foreach ($itemsToAccept as $requestItem) {
                // Update the inventory request item with acceptance details
                $requestItem->update([
                    'received_by' => $userId,
                    'received_at' => now(),
                ]);

                // Add or update inventory for the staff member's branch
                $inventory = Inventory::where('item_id', $requestItem->item_id)
                    ->where('branch_id', $userBranchId)
                    ->first();

                if ($inventory) {
                    // Update existing inventory
                    $inventory->increment('current_stock', $requestItem->quantity);
                } else {
                    // Create new inventory record for this branch
                    Inventory::create([
                        'item_id' => $requestItem->item_id,
                        'branch_id' => $userBranchId,
                        'current_stock' => $requestItem->quantity,
                        'low_stock_alert' => 10, // Default low stock alert
                    ]);
                }
                }
            });

            $acceptedCount = count($request->items);

            return redirect()->route('staff.pending-inventory-requests')
                ->with('success', "Successfully accepted {$acceptedCount} items and added them to your branch inventory!");

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Accept Inventory Items Database Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database error occurred while accepting inventory. Please contact support.');

        } catch (\Exception $e) {
            Log::error('Accept Inventory Items Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while accepting inventory. Please try again.');
        }
    }

    /**
     * Display items that the current staff member has accepted
     */
    public function myAcceptedItems()
    {
        $acceptedItems = InventoryRequestItem::with(['item', 'inventoryRequest.department'])
            ->where('received_by', Auth::id())
            ->orderBy('received_at', 'desc')
            ->paginate(15);

        return view('staff.my-accepted-items', compact('acceptedItems'));
    }

    /**
     * Display branch inventory status for staff
     */
    public function branchInventory()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $userBranchId = (int) $user->branch_id;

        // Ensure user exists and has a branch
        if (!$user || !$userBranchId) {
            return redirect()->back()->with('error', 'You must be assigned to a branch to view inventory.');
        }

        $inventoryItems = Inventory::with(['item'])
            ->join('items', 'items.id', '=', 'inventories.item_id')
            ->where('branch_id', $userBranchId)
            ->where('items.is_active','=','1')
            ->where('items.stock_count','=','1') // Only items that track stock
            ->orderBy('current_stock', 'asc')
            ->paginate(100);

        return view('staff.branch-inventory', compact('inventoryItems'));
    }

    /**
     * Get items that the current staff member can see (for POS and item management)
     */
    public function getAvailableItems()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $userId = (int) $user->id;

        // If there's no authenticated user, or user is not staff, return all items
        if (!$user) {
            return Item::where('is_active', true)->get();
        }

        if (!$user->isStaff()) {
            return Item::where('is_active', true)->get();
        }

        // For staff, only return items they have accepted
        $acceptedItemIds = InventoryRequestItem::where('received_by', $userId)
            ->pluck('item_id')
            ->unique()
            ->toArray();

        return Item::whereIn('id', $acceptedItemIds)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Show the form for adding branch wastage
     */
    public function addBranchWastage()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $userBranchId = (int) $user->branch_id;

        // Ensure user has a branch
        if (!$user || !$userBranchId) {
            return redirect()->back()->with('error', 'You must be assigned to a branch to add wastage.');
        }

        // Get items available in the staff's branch with stock > 0
        $items = Item::with(['inventory' => function($query) use ($userBranchId) {
                $query->where('branch_id', $userBranchId);
            }])
            ->where('is_active', true)
            ->get()
            ->filter(function ($item) {
                return $item->inventory->isNotEmpty() && $item->inventory->sum('current_stock') > 0;
            })
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'item_code' => $item->item_code,
                    'available_stock' => (int) $item->inventory->sum('current_stock')
                ];
            });

        return view('staff.add-branch-wastage', compact('items'));
    }

    /**
     * Store branch wastage record
     */
    public function storeBranchWastage(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $userId = (int) $user->id;
        $userBranchId = (int) $user->branch_id;

        // Ensure user has a branch
        if (!$user || !$userBranchId) {
            return redirect()->back()->with('error', 'You must be assigned to a branch to add wastage.');
        }

        $request->validate([
            'date_time' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.wasted_quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Custom validation to check available stock from branch inventory
        foreach ($request->items as $index => $itemData) {
            $inventory = Inventory::where('item_id', $itemData['item_id'])
                ->where('branch_id', $userBranchId)
                ->first();
            $availableStock = $inventory ? $inventory->current_stock : 0;

            if ($itemData['wasted_quantity'] > $availableStock) {
                $item = Item::find($itemData['item_id']);
                return back()->withErrors([
                    "items.{$index}.wasted_quantity" => "Wasted quantity for '{$item->item_name}' cannot exceed available branch stock ({$availableStock})."
                ])->withInput();
            }
        }

        try {
            DB::transaction(function () use ($request, $userId, $userBranchId) {
                // Create wastage record
                $wastage = Wastage::create([
                'user_id' => $userId,
                'branch_id' => $userBranchId,
                'date_time' => $request->date_time,
                'remarks' => $request->remarks,
            ]);

            // Create wastage items and update branch inventory
            foreach ($request->items as $itemData) {
                $inventory = Inventory::where('item_id', $itemData['item_id'])
                    ->where('branch_id', $userBranchId)
                    ->first();
                $previousStock = $inventory ? $inventory->current_stock : 0;

                // Create wastage item record
                WastageItem::create([
                    'wastage_id' => $wastage->id,
                    'item_id' => $itemData['item_id'],
                    'wasted_quantity' => $itemData['wasted_quantity'],
                    'previous_stock' => $previousStock,
                ]);

                // Reduce branch inventory stock
                if ($inventory) {
                    $inventory->decrement('current_stock', $itemData['wasted_quantity']);
                }
                }
            });

            return redirect()->route('staff.branch-inventory')
                ->with('success', 'Branch wastage has been recorded successfully and inventory has been updated!');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Store Branch Wastage Database Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database error occurred while recording wastage. Please contact support.')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Store Branch Wastage Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while recording wastage. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show branch wastage records for staff
     */
    public function branchWastageView(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $userBranchId = (int) $user->branch_id;

        // Ensure user has a branch
        if (!$user || !$userBranchId) {
            return redirect()->back()->with('error', 'You must be assigned to a branch to view wastage.');
        }

        $query = Wastage::with(['wastageItems.item', 'user'])
            ->where('branch_id', $userBranchId)
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

        return view('staff.branch-wastage-view', compact('wastages'));
    }

    /**
     * Show the form for creating a staff stock transfer
     */
    public function createStockTransfer()
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Check authentication first
        if (!$user) {
            Log::warning('Unauthorized access attempt to createStockTransfer - no authenticated user');
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        // Cast branch_id to int and check if valid
        $userBranchId = $user->branch_id ? (int) $user->branch_id : null;

        // Ensure user has a branch
        if (!$userBranchId) {
            Log::warning('User ' . $user->id . ' attempted to create stock transfer without branch assignment');
            return redirect()->back()->with('error', 'You must be assigned to a branch to create stock transfers.');
        }

        // Get all branches except the current user's branch
        $branches = Branch::active()
            ->where('id', '!=', $userBranchId)
            ->orderBy('name')
            ->get();

        // Get items available in the staff's branch with stock > 0
        $items = Item::with(['inventory' => function($query) use ($userBranchId) {
                $query->where('branch_id', $userBranchId);
            }])
            ->where('is_active', true)
            ->whereHas('inventory', function($query) use ($userBranchId) {
                $query->where('branch_id', $userBranchId)
                      ->where('current_stock', '>', 0);
            })
            ->orderBy('item_name')
            ->get();

        return view('staff.stock-transfer.create', compact('branches', 'items'));
    }

    /**
     * Store a new staff stock transfer request
     */
    public function storeStockTransfer(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Check authentication first
        if (!$user) {
            Log::warning('Unauthorized access attempt to storeStockTransfer - no authenticated user');
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        $userId = (int) $user->id;
        // Cast branch_id to int and check if valid
        $userBranchId = $user->branch_id ? (int) $user->branch_id : null;

        // Ensure user has a branch
        if (!$userBranchId) {
            Log::warning('User ' . $user->id . ' attempted to store stock transfer without branch assignment');
            return redirect()->back()->with('error', 'You must be assigned to a branch to create stock transfers.');
        }

        $request->validate([
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'date_time' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => ['required', 'numeric', function ($attribute, $value, $fail) {
                if ($value == 0) {
                    $fail('The quantity cannot be zero.');
                }
            }],
        ]);

        // Validate that the destination branch is not the same as the source
        if ((int)$request->to_branch_id === $userBranchId) {
            return back()->withErrors(['to_branch_id' => 'You cannot transfer to your own branch.'])->withInput();
        }

        try {
            DB::transaction(function () use ($request, $userId, $userBranchId) {
                // Create the stock transfer from staff's branch to another branch
                $transfer = StockTransfer::create([
                'from_branch_id' => $userBranchId, // Staff's branch as source
                'to_branch_id' => $request->to_branch_id,
                'date_time' => $request->date_time,
                'status' => 'pending',
                'created_by' => $userId,
                'notes' => $request->notes,
            ]);

            // Process each item
            foreach ($request->items as $itemData) {
                $inventory = Inventory::where('item_id', $itemData['item_id'])
                    ->where('branch_id', $userBranchId) // Staff's branch inventory
                    ->first();

                if (!$inventory) {
                    throw new \Exception("Inventory not found for item ID: {$itemData['item_id']}");
                }

                // Allow negative quantities for negative stock transfers
                // Only check if transferring more positive stock than available
                if ($itemData['quantity'] > 0 && $inventory->current_stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock in your branch inventory for item ID: {$itemData['item_id']}");
                }

                // Create transfer item
                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'available_quantity' => $inventory->current_stock,
                ]);
                }
            });

            return redirect()->route('stock-transfer.transfers')
                ->with('success', 'Stock transfer request has been sent successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Staff Stock Transfer Store Database Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database error occurred while creating stock transfer. Please contact support.')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Staff Stock Transfer Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display list of stock transfers created by staff
     */
    public function stockTransferIndex()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $userId = (int) $user->id;
        $userBranchId = (int) $user->branch_id;

        // Ensure user has a branch
        if (!$user || !$userBranchId) {
            return redirect()->back()->with('error', 'You must be assigned to a branch to view stock transfers.');
        }

        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'transferItems.item'])
            ->where('created_by', $userId)
            ->orderBy('date_time', 'desc')
            ->paginate(10);

        return view('staff.stock-transfer.index', compact('transfers'));
    }

    /**
     * Get available inventory for AJAX requests (for staff)
     */
    public function getStaffInventory(Item $item)
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Check authentication first
        if (!$user) {
            Log::warning('Unauthorized access attempt to getStaffInventory - no authenticated user');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Cast branch_id to int and check if valid
        $userBranchId = $user->branch_id ? (int) $user->branch_id : null;

        // Ensure user has a branch
        if (!$userBranchId) {
            Log::warning('User ' . $user->id . ' attempted to access inventory without branch assignment');
            return response()->json(['error' => 'You must be assigned to a branch.'], 403);
        }

        // Get inventory from staff's branch
        $inventory = Inventory::where('item_id', $item->id)
            ->where('branch_id', $userBranchId)
            ->first();

        return response()->json([
            'available_quantity' => $inventory ? $inventory->current_stock : 0,
        ]);
    }

    /**
     * Get all available inventory items from staff's branch
     */
    public function getAllStaffInventory()
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Check authentication first
        if (!$user) {
            Log::warning('Unauthorized access attempt to getAllStaffInventory - no authenticated user');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Cast branch_id to int and check if valid
        $userBranchId = $user->branch_id ? (int) $user->branch_id : null;

        // Ensure user has a branch
        if (!$userBranchId) {
            Log::warning('User ' . $user->id . ' attempted to access all inventory without branch assignment');
            return response()->json(['error' => 'You must be assigned to a branch.'], 403);
        }

        // Get all inventory items from staff's branch including negative stock
        $inventoryItems = Inventory::where('branch_id', $userBranchId)
            ->whereHas('item', function($query) {
                $query->where('is_active', 1);
            })
            ->where('current_stock', '>', 0)
            ->with('item')
            ->get()
            ->map(function ($inventory) {
                return [
                    'item_id' => $inventory->item_id,
                    'item_name' => $inventory->item->item_name,
                    'available_quantity' => $inventory->current_stock,
                ];
            });

        return response()->json([
            'items' => $inventoryItems,
        ]);
    }
}
