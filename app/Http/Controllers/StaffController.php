<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryRequest;
use App\Models\InventoryRequestItem;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\User;

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

        // Ensure user exists and has a branch_id (staff should have branch assignment)
        if (!$user || !$user->branch_id) {
            return redirect()->back()->with('error', 'You must be assigned to a branch to accept inventory items.');
        }

        DB::transaction(function () use ($request, $inventoryRequest, $user) {
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
                    'received_by' => $user->id,
                    'received_at' => now(),
                ]);

                // Add or update inventory for the staff member's branch
                $inventory = Inventory::where('item_id', $requestItem->item_id)
                    ->where('branch_id', $user->branch_id)
                    ->first();

                if ($inventory) {
                    // Update existing inventory
                    $inventory->increment('current_stock', $requestItem->quantity);
                } else {
                    // Create new inventory record for this branch
                    Inventory::create([
                        'item_id' => $requestItem->item_id,
                        'branch_id' => $user->branch_id,
                        'current_stock' => $requestItem->quantity,
                        'low_stock_alert' => 10, // Default low stock alert
                    ]);
                }
            }
        });

        $acceptedCount = count($request->items);
        
        return redirect()->route('staff.pending-inventory-requests')
            ->with('success', "Successfully accepted {$acceptedCount} items and added them to your branch inventory!");
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

        // Ensure user exists and has a branch
        if (!$user || !$user->branch_id) {
            return redirect()->back()->with('error', 'You must be assigned to a branch to view inventory.');
        }

        $inventoryItems = Inventory::with(['item'])
            ->where('branch_id', $user->branch_id)
            ->orderBy('current_stock', 'asc')
            ->paginate(20);

        return view('staff.branch-inventory', compact('inventoryItems'));
    }

    /**
     * Get items that the current staff member can see (for POS and item management)
     */
    public function getAvailableItems()
    {
        /** @var User|null $user */
        $user = Auth::user();

        // If there's no authenticated user, or user is not staff, return all items
        if (!$user) {
            return Item::where('is_active', true)->get();
        }

        if (!$user->isStaff()) {
            return Item::where('is_active', true)->get();
        }

        // For staff, only return items they have accepted
        $acceptedItemIds = InventoryRequestItem::where('received_by', $user->id)
            ->pluck('item_id')
            ->unique()
            ->toArray();

        return Item::whereIn('id', $acceptedItemIds)
            ->where('is_active', true)
            ->get();
    }
}
