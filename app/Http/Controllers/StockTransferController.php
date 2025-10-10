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

        // Get all active branches for destination
        $branches = Branch::active()
            ->orderBy('name')
            ->get();

        // Get items available in central inventory (branch_id = null)
        $items = Item::with(['inventory' => function($query) {
            $query->whereNull('branch_id'); // Central inventory
        }])
        ->where('is_active', true)
        ->whereHas('inventory', function($query) {
            $query->whereNull('branch_id') // Central inventory
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
        
        $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'date_time' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $user) {
            // Create the stock transfer (from central inventory to branch)
            $transfer = StockTransfer::create([
                'to_branch_id' => $request->to_branch_id,
                'date_time' => $request->date_time,
                'status' => 'pending',
                'created_by' => $user->id,
                'notes' => $request->notes,
            ]);

            // Process each item
            foreach ($request->items as $itemData) {
                // Get current inventory for this item in central inventory
                $inventory = Inventory::where('item_id', $itemData['item_id'])
                    ->whereNull('branch_id') // Central inventory
                    ->first();

                if (!$inventory || $inventory->current_stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock in central inventory for item ID: {$itemData['item_id']}");
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
        $transfers = StockTransfer::with(['toBranch', 'transferItems.item'])
            ->where('created_by', $user->id)
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
        
        if (!$user->branch_id) {
            abort(403, 'You must be assigned to a branch to view transfers.');
        }

        $pendingTransfers = StockTransfer::with(['creator', 'transferItems.item'])
            ->where('to_branch_id', $user->branch_id)
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
        $status = $request->get('status', 'pending');
        
        // Get status counts for tabs
        if ($user->role === 'supervisor') {
            // Supervisor sees all transfers
            $statusCounts = [
                'pending' => StockTransfer::where('status', 'pending')->count(),
                'accepted' => StockTransfer::where('status', 'accepted')->count(),
                'rejected' => StockTransfer::where('status', 'rejected')->count(),
            ];
            
            $transfers = StockTransfer::with(['toBranch', 'creator', 'processor', 'transferItems.item'])
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
                
            $pageTitle = 'All Stock Transfers';
        } else {
            // Staff sees only transfers for their branch
            $statusCounts = [
                'pending' => StockTransfer::where('to_branch_id', $user->branch_id)->where('status', 'pending')->count(),
                'accepted' => StockTransfer::where('to_branch_id', $user->branch_id)->where('status', 'accepted')->count(),
                'rejected' => StockTransfer::where('to_branch_id', $user->branch_id)->where('status', 'rejected')->count(),
            ];
            
            $transfers = StockTransfer::with(['toBranch', 'creator', 'processor', 'transferItems.item'])
                ->where('to_branch_id', $user->branch_id)
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
        
        // Check authorization - either creator or receiving branch staff
        if ($stockTransfer->created_by !== $user->id && $stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized access to this transfer.');
        }

        $stockTransfer->load(['toBranch', 'creator', 'processor', 'transferItems.item']);

        return view('stock-transfer.show', compact('stockTransfer'));
    }

    /**
     * Accept a stock transfer
     */
    public function accept(StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Verify user can process this transfer
        if ($stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'You can only accept transfers sent to your branch.');
        }

        if (!$stockTransfer->isPending()) {
            return redirect()->back()->with('error', 'This transfer has already been processed.');
        }

        DB::transaction(function () use ($stockTransfer, $user) {
            // Update transfer status
            $stockTransfer->update([
                'status' => 'accepted',
                'processed_by' => $user->id,
                'processed_at' => now(),
            ]);

            // Process each item
            foreach ($stockTransfer->transferItems as $transferItem) {
                // Deduct from central inventory (source)
                $sourceInventory = Inventory::where('item_id', $transferItem->item_id)
                    ->whereNull('branch_id') // Central inventory
                    ->first();

                if ($sourceInventory && $sourceInventory->current_stock >= $transferItem->quantity) {
                    $sourceInventory->decrement('current_stock', $transferItem->quantity);
                }

                // Add to destination branch
                $destInventory = Inventory::where('item_id', $transferItem->item_id)
                    ->where('branch_id', $stockTransfer->to_branch_id)
                    ->first();

                if ($destInventory) {
                    $destInventory->increment('current_stock', $transferItem->quantity);
                } else {
                    // Create new inventory record for this branch
                    Inventory::create([
                        'item_id' => $transferItem->item_id,
                        'branch_id' => $stockTransfer->to_branch_id,
                        'current_stock' => $transferItem->quantity,
                        'low_stock_alert' => 10, // Default value
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Stock transfer has been accepted successfully!');
    }

    /**
     * Reject a stock transfer
     */
    public function reject(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Verify user can process this transfer
        if ($stockTransfer->to_branch_id !== $user->branch_id) {
            abort(403, 'You can only reject transfers sent to your branch.');
        }

        if (!$stockTransfer->isPending()) {
            return redirect()->back()->with('error', 'This transfer has already been processed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $stockTransfer->update([
            'status' => 'rejected',
            'processed_by' => $user->id,
            'processed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Stock transfer has been rejected.');
    }

    /**
     * Get available inventory for AJAX requests
     */
    public function getInventory(Item $item)
    {
        if (!Gate::allows('supervisor-access')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get inventory from central inventory (branch_id = null)
        $inventory = Inventory::where('item_id', $item->id)
            ->whereNull('branch_id') // Central inventory
            ->first();

        return response()->json([
            'available_quantity' => $inventory ? $inventory->current_stock : 0,
        ]);
    }
}