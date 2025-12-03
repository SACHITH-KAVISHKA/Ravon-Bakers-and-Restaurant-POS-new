<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\Item;
use App\Models\Branch;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    public function create()
    {
        $branches = Branch::all();

        // Load 'inventory' (your relationship) and 'branchPrices' to get accurate data
        $items = Item::where('is_active', true)
                     ->with(['inventory', 'branchPrices'])
                     ->orderBy('item_name', 'asc')
                     ->get();

        return view('stock_adjustment.create', compact('branches', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'adjustment_date' => 'required|date',
            'branch_id'       => 'required|exists:branches,id',
            'cashier_name'    => 'required|string|max:255',
            'items'           => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.actual_stock' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create Header
            $adjustment = StockAdjustment::create([
                'adjustment_date' => $request->adjustment_date,
                'branch_id'       => $request->branch_id,
                'cashier_name'    => $request->cashier_name,
                'supervisor_id'   => Auth::id(),
                'status'          => 1,
                'total_variance'  => 0,
            ]);

            $calculatedTotalAmount = 0;

            // 2. Loop through Items
            foreach ($request->items as $row) {
                // Eager load branchPrices to get server-side price check
                $itemModel = Item::with('branchPrices')->find($row['item_id']);

                // Try to find price for THIS branch.
                $branchPrice = $itemModel->branchPrices->where('branch_id', $request->branch_id)->first();
                $price = $branchPrice ? $branchPrice->price : $itemModel->price;

                // Fetch stock for THIS branch from Inventory table
                $inventory = Inventory::where('branch_id', $request->branch_id)
                                      ->where('item_id', $row['item_id'])
                                      ->first();

                $currentStock = $inventory ? (int) $inventory->current_stock : 0;
                $actualStock  = (int) $row['actual_stock'];

                $varianceQty = $actualStock - $currentStock;
                $varianceAmount = $varianceQty * $price;
                $calculatedTotalAmount += $varianceAmount;

                // --- UPDATE INVENTORY TABLE START ---
                if ($inventory) {
                    // Update existing record
                    $inventory->update(['current_stock' => $actualStock]);
                } else {
                    // Create new record if it doesn't exist (Optional, but safe)
                    Inventory::create([
                        'branch_id'     => $request->branch_id,
                        'item_id'       => $row['item_id'],
                        'current_stock' => $actualStock,
                        'low_stock_alert' => 0 // Set a default value if required
                    ]);
                }

                StockAdjustmentDetail::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'item_id'             => $row['item_id'],
                    'current_stock'       => $currentStock,
                    'actual_stock'        => $actualStock,
                    'variance'            => $varianceQty,
                    'variance_amount'     => $varianceAmount,
                    'status'              => 1,
                ]);
            }

            // 3. Update Header Total
            $adjustment->update(['total_variance' => $calculatedTotalAmount]);

            DB::commit();
            return redirect()->back()->with('success', 'Stock adjustment saved.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // public function index()
    // {
    //     // Fetch adjustments with related Branch and Supervisor data
    //     // Ordered by latest first
    //     $adjustments = StockAdjustment::with(['branch', 'supervisor'])
    //                                   ->orderBy('created_at', 'desc')
    //                                   ->paginate(15);

    //     return view('stock_adjustment.index', compact('adjustments'));
    // }

    public function index(Request $request)
    {
        // 1. Start the query
        $query = StockAdjustment::with(['branch', 'supervisor'])
                                ->orderBy('adjustment_date', 'desc');

        // 2. Apply Date Range Filter
        if ($request->filled('start_date')) {
            $query->whereDate('adjustment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('adjustment_date', '<=', $request->end_date);
        }

        // 3. Apply Branch Filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // 4. Fetch results with pagination (appends ensures filters stay when clicking page 2)
        $adjustments = $query->paginate(15)->appends($request->all());

        // 5. Get branches for the filter dropdown
        $branches = Branch::all();

        return view('stock_adjustment.index', compact('adjustments', 'branches'));
    }

    /**
     * Display the specified adjustment details.
     */
    public function show($id)
    {
        // Fetch the header with details and the specific items for those details
        $adjustment = StockAdjustment::with(['branch', 'supervisor', 'details.item'])
                                     ->findOrFail($id);

        return view('stock_adjustment.show', compact('adjustment'));
    }
}
