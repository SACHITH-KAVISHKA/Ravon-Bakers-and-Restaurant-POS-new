<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Branch;
use App\Models\Item;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class StockTransferController extends Controller
{
    /**
     * Display stock transfer form for supervisors
     */
    public function create()
    {
        if (!Gate::allows('supervisor-access')) {
            abort(403, 'Unauthorized access. Only supervisors can create stock transfers.');
        }
        $branches = Branch::active()
            ->orderBy('name')
            ->get();

        $items = Item::with(['inventory' => function($query) {
            $query->where('branch_id', 1);
        }])
        ->where('is_active', true)
        ->whereHas('inventory', function($query) {
            $query->where('branch_id', 1)
                  ->where('current_stock', '>', 0);
        })
        ->orderBy('item_name')
        ->get();

        return view('supervisor.stock-transfer.create', compact('branches', 'items'));
    }

    /**
     * Store a new stock transfer request
     */
    public function store(Request $request)
    {
        if (!Gate::allows('supervisor-access')) {
            abort(403, 'Unauthorized access. Only supervisors can create stock transfers.');
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
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

        try {
            DB::transaction(function () use ($request, $userId) {
                // Create the stock transfer (from central inventory to branch)
                // Explicitly set from_branch_id to 1 for supervisor transfers (main/central inventory)
                $transfer = StockTransfer::create([
                'from_branch_id' => 1, // Main/Central inventory branch
                'to_branch_id' => $request->to_branch_id,
                'date_time' => $request->date_time,
                'status' => 'pending',
                'created_by' => $userId,
                'notes' => $request->notes,
            ]);

            // Process each item
            foreach ($request->items as $itemData) {

                $inventory = Inventory::where('item_id', $itemData['item_id'])
                    ->where('branch_id', 1) // Main branch inventory
                    ->first();

                if (!$inventory) {
                    throw new \Exception("Inventory not found for item ID: {$itemData['item_id']}");
                }

                // Allow negative quantities for negative stock transfers
                // Only check if transferring more positive stock than available
                if ($itemData['quantity'] > 0 && $inventory->current_stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock in main branch inventory for item ID: {$itemData['item_id']}");
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

            return redirect()->route('supervisor.stock-transfer.by-status')
                ->with('success', 'Stock transfer request has been sent successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Stock Transfer Store Database Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database error occurred while creating stock transfer. Please contact support.')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Stock Transfer Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display list of stock transfers for supervisors
     */
    public function index()
    {
        if (!Gate::allows('supervisor-access')) {
            abort(403, 'Unauthorized access. Only supervisors can view stock transfers.');
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'transferItems.item'])
            ->where('created_by', $userId)
            ->orderBy('date_time', 'desc')
            ->paginate(10);

        return view('supervisor.stock-transfer.index', compact('transfers'));
    }

    /**
     * Display transfers by status for supervisors (legacy route - redirects to new transfers page)
     */
    public function byStatus(Request $request)
    {
        $status = $request->get('status', 'pending');
        return redirect()->route('stock-transfer.transfers', ['status' => $status]);
    }

    /**
     * Display pending transfers for branch staff
     */
    public function pending()
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $userBranchId = (int) $user->branch_id;

        if (!$userBranchId) {
            abort(403, 'You must be assigned to a branch to view transfers.');
        }

        // Load fromBranch relationship to show source of transfer
        $pendingTransfers = StockTransfer::with(['fromBranch', 'toBranch', 'creator', 'transferItems.item'])
            ->where('to_branch_id', $userBranchId)
            ->where('status', 'pending')
            ->orderBy('date_time', 'desc')
            ->paginate(10);

        return view('stock-transfer.pending', compact('pendingTransfers'));
    }

    /**
     * Show transfers by status with tabs (for both supervisor and staff)
     */
    public function showTransfers(Request $request)
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $userBranchId = (int) $user->branch_id;
        $status = $request->get('status', 'pending');

        // Get status counts for tabs
        if ($user->role === 'supervisor') {
            // Supervisor sees all transfers
            $statusCounts = [
                'pending' => StockTransfer::where('status', 'pending')->count(),
                'accepted' => StockTransfer::where('status', 'accepted')->count(),
                'rejected' => StockTransfer::where('status', 'rejected')->count(),
            ];

            $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'creator', 'processor', 'transferItems.item'])
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $pageTitle = 'All Stock Transfers';
        } else {
            // Staff sees transfers related to their branch (incoming TO or outgoing FROM their branch)
            $statusCounts = [
                'pending' => StockTransfer::where(function($q) use ($userBranchId) {
                    $q->where('to_branch_id', $userBranchId)
                      ->orWhere('from_branch_id', $userBranchId);
                })->where('status', 'pending')->count(),
                'accepted' => StockTransfer::where(function($q) use ($userBranchId) {
                    $q->where('to_branch_id', $userBranchId)
                      ->orWhere('from_branch_id', $userBranchId);
                })->where('status', 'accepted')->count(),
                'rejected' => StockTransfer::where(function($q) use ($userBranchId) {
                    $q->where('to_branch_id', $userBranchId)
                      ->orWhere('from_branch_id', $userBranchId);
                })->where('status', 'rejected')->count(),
            ];

            $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'creator', 'processor', 'transferItems.item'])
                ->where(function($q) use ($userBranchId) {
                    $q->where('to_branch_id', $userBranchId)
                      ->orWhere('from_branch_id', $userBranchId);
                })
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $pageTitle = 'Branch Stock Transfers';
        }

        return view('stock-transfer.transfers', compact('transfers', 'status', 'statusCounts', 'pageTitle'));
    }

    /**
     * Show transfer details
     */
    public function show(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $userBranchId = (int) $user->branch_id;

        // // Allow access if:
        // // 1. User created this transfer, OR
        // // 2. Transfer is sent to user's branch (for accepting/rejecting), OR
        // // 3. Transfer is from user's branch (staff transfer they initiated)
        // $canView = (int)$stockTransfer->created_by === $userId
        //         || (int)$stockTransfer->to_branch_id === $userBranchId
        //         || (int)$stockTransfer->from_branch_id === $userBranchId;

        // if (!$canView) {
        //     abort(403, 'Unauthorized access to this transfer.');
        // }

        $stockTransfer->load(['fromBranch', 'toBranch', 'creator', 'processor', 'transferItems.item']);

        return view('stock-transfer.show', compact('stockTransfer'));
    }

    /**
     * Accept a stock transfer
     */
    public function accept(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $userBranchId = (int) $user->branch_id;
        $isSupervisor = Gate::allows('supervisor-access');

        // Verify user can process this transfer
        // Allow if:
        // 1. Transfer is to user's assigned branch
        // 2. User is Supervisor AND destination is Main Branch (ID 1)
        $isDestinedToUser = ($stockTransfer->to_branch_id === $userBranchId);
        $isSupervisorToMain = ($isSupervisor && $stockTransfer->to_branch_id === 1);

        if (!$isDestinedToUser && !$isSupervisorToMain) {
            abort(403, 'You can only accept transfers sent to your branch (or Main Branch if you are a Supervisor).');
        }

        if (!$stockTransfer->isPending()) {
            return redirect()->back()->with('error', 'This transfer has already been processed.');
        }

        try {
            DB::transaction(function () use ($stockTransfer, $userId) {
                // Update transfer status
                $stockTransfer->update([
                'status' => 'accepted',
                'processed_by' => $userId,
                'processed_at' => now(),
            ]);

            // Process each item
            foreach ($stockTransfer->transferItems as $transferItem) {
                // Determine source branch (1 for central/main, or from_branch_id for staff transfers)
                $sourceBranchId = $stockTransfer->from_branch_id ?? 1;

                // Deduct from source branch inventory
                $sourceInventory = Inventory::where('item_id', $transferItem->item_id)
                    ->where('branch_id', $sourceBranchId)
                    ->lockForUpdate() // transactional lock same time
                    ->first();

                // Given the Exception will be thrown if source inventory is not found
                    if (!$sourceInventory) {
                    throw new \Exception("Source inventory not found for item ID: {$transferItem->item_id}");
                }

                // Ensure sufficient stock in source inventory
                if ($sourceInventory->current_stock < $transferItem->quantity) {
                    throw new \Exception("Insufficient stock to accept transfer for item: {$transferItem->item->item_name}. Available: {$sourceInventory->current_stock}");
                }

                // Deduct stock from source branch
                $sourceInventory->decrement('current_stock', $transferItem->quantity);

                // if ($sourceInventory && $sourceInventory->current_stock >= $transferItem->quantity) {
                //     $sourceInventory->decrement('current_stock', $transferItem->quantity);
                // }

                // Add to destination branch: update if exists, otherwise create
                $destInventory = Inventory::firstOrNew([
                    'item_id' => $transferItem->item_id,
                    'branch_id' => $stockTransfer->to_branch_id,
                ]);

                if ($destInventory->exists) {
                    // existing record -> increment
                    $destInventory->increment('current_stock', $transferItem->quantity);
                } else {
                    // new record -> set initial values
                    // try to copy low_stock_alert from source inventory if available
                    $defaultLowAlert = 10;
                    if (isset($sourceInventory) && $sourceInventory) {
                        $defaultLowAlert = $sourceInventory->low_stock_alert ?? $defaultLowAlert;
                    }

                    $destInventory->current_stock = $transferItem->quantity;
                    $destInventory->low_stock_alert = $defaultLowAlert;
                    $destInventory->save();
                }
                }
            });

            return redirect()->back()->with('success', 'Stock transfer has been accepted successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Stock Transfer Accept Database Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database error occurred while accepting transfer. Please contact support.');

        } catch (\Exception $e) {
            Log::error('Stock Transfer Accept Error: ' . $e->getMessage());


            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a stock transfer
     */
    public function reject(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $userBranchId = (int) $user->branch_id;
        $isSupervisor = Gate::allows('supervisor-access');

        // Verify user can process this transfer
        // Allow if:
        // 1. Transfer is to user's assigned branch
        // 2. User is Supervisor AND destination is Main Branch (ID 1)
        $isDestinedToUser = ($stockTransfer->to_branch_id === $userBranchId);
        $isSupervisorToMain = ($isSupervisor && $stockTransfer->to_branch_id === 1);

        if (!$isDestinedToUser && !$isSupervisorToMain) {
            abort(403, 'You can only reject transfers sent to your branch (or Main Branch if you are a Supervisor).');
        }

        if (!$stockTransfer->isPending()) {
            return redirect()->back()->with('error', 'This transfer has already been processed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $stockTransfer->update([
            'status' => 'rejected',
            'processed_by' => $userId,
            'processed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Stock transfer has been rejected.');
    }

    /**
     * Destroy (delete) a pending stock transfer - supervisor only
     */
    public function destroy(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        $userId = (int) $user->id;

        // Only supervisors can delete transfers via supervisor routes
        if ($user->role !== 'supervisor') {
            abort(403, 'Unauthorized.');
        }

        // Only allow deleting transfers created by this supervisor and still pending
        if ($stockTransfer->created_by !== $userId) {
            return redirect()->back()->with('error', 'You can only delete transfers you created.');
        }

        if (!$stockTransfer->isPending()) {
            return redirect()->back()->with('error', 'Only pending transfers can be deleted.');
        }

        try {
            DB::transaction(function () use ($stockTransfer) {
                // Delete transfer items first
                $stockTransfer->transferItems()->delete();

                // Delete the transfer itself
                $stockTransfer->delete();
            });

            return redirect()->route('supervisor.stock-transfer.by-status')
                ->with('success', 'Pending stock transfer has been deleted.');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Stock Transfer Delete Database Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database error occurred while deleting transfer. Please contact support.');

        } catch (\Exception $e) {
            Log::error('Stock Transfer Delete Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while deleting transfer. Please try again.');
        }
    }

    /**
     * Get available inventory for AJAX requests
     */
    public function getInventory(Item $item)
    {
        if (!Gate::allows('supervisor-access')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get inventory from main branch (branch_id = 1)
        $inventory = Inventory::where('item_id', $item->id)
            ->where('branch_id', 1) // Main branch inventory
            ->first();

        return response()->json([
            'available_quantity' => $inventory ? $inventory->current_stock : 0,
        ]);
    }

    /**
     * Get all available inventory items from main branch
     */
    public function getAllInventory()
    {
        if (!Gate::allows('supervisor-access')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get all inventory items from main branch including negative stock
        $inventoryItems = Inventory::where('branch_id', 1)
            ->where('current_stock', '>', 0)
            ->with('item')
            ->get()
            ->map(function($inventory) {
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
