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
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupervisorController extends Controller
{
    /**
     * Display supervisor dashboard
     */
    public function dashboard()
    {
        $userId = (int) Auth::id();
        
        // Get recent inventory requests by this supervisor
        $recentRequests = InventoryRequest::with(['department', 'inventoryRequestItems.item'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get total inventory requests count
        $totalRequests = InventoryRequest::where('user_id', $userId)->count();

        // Get inventory summary
        $inventoryCount = Inventory::count();
        $lowStockItems = Inventory::whereRaw('current_stock <= low_stock_alert')->count();

        // Get wastage statistics
        $totalWastages = Wastage::where('user_id', $userId)->count();
        $recentWastages = Wastage::with(['wastageItems.item'])
            ->where('user_id', $userId)
            ->orderBy('date_time', 'desc')
            ->take(5)
            ->get();

        // Get stock transfer statistics
        $totalTransfers = StockTransfer::where('created_by', $userId)->count();
        $pendingTransfers = StockTransfer::where('created_by', $userId)
            ->where('status', 'pending')
            ->count();
        $recentTransfers = StockTransfer::with(['toBranch', 'transferItems.item'])
            ->where('created_by', $userId)
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
        $items = Item::with('inventory', 'branchPrices')
            ->where('is_active', true)
            ->orderBy('item_name', 'asc')
            ->get();

        // Determine main branch (if exists) to show main-branch stock in the form
        $mainBranch = Branch::where('name', 'Main Branch')->first();
        if (! $mainBranch) {
            $mainBranch = Branch::where('status', 1)->first();
        }

        // Attach a convenient available_stock attribute (main branch stock) to each item
        foreach ($items as $item) {
            if ($item->relationLoaded('inventory')) {
                if ($mainBranch) {
                    $mainInv = $item->inventory->where('branch_id', $mainBranch->id)->first();
                    $item->available_stock = $mainInv ? $mainInv->current_stock : 0;
                } else {
                    // Fallback: sum all inventories
                    $item->available_stock = (int) $item->inventory->sum('current_stock');
                }
            } else {
                $item->available_stock = 0;
            }
        }

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
            $userId = (int) Auth::id();
            
            // Create inventory request
            $inventoryRequest = InventoryRequest::create([
                'user_id' => $userId,
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
                    // Create new inventory record if it doesn't exist (main stock: branch_id = 1)
                    Inventory::create([
                        'item_id' => $itemData['item_id'],
                        'branch_id' => 1,
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
            ->orderBy('item_name', 'asc')
            ->get()
            ->map(function ($item) {
                $firstBp = $item->branchPrices->first();
                // inventory is now a collection (multiple branches); sum or use first
                $available = 0;
                if ($item->relationLoaded('inventory')) {
                    // Try to find main branch inventory first
                    $main = $item->inventory->first();
                    $available = $item->inventory->sum('current_stock');
                }

                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'item_code' => $item->item_code,
                    'price' => $firstBp ? $firstBp->price : 0,
                    'current_stock' => $available,
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
    public function inventoryHistory(Request $request)
    {
        // Get date and time filters
        $filterDate = $request->input('date');
        $filterTime = $request->input('time');

        // Get all branches
        $branches = Branch::where('status', 1)->orderBy('name')->get();
        $mainBranch = $branches->where('name', 'Main Branch')->first();

        // If no main branch found, use the first branch as main
        if (!$mainBranch) {
            $mainBranch = $branches->first();
        }

        $otherBranches = $branches->where('id', '!=', $mainBranch->id ?? null);

        // If date/time filter is applied, query historical data
        if ($filterDate || $filterTime) {
            $itemsCollection = $this->getHistoricalStockData($filterDate, $filterTime, $mainBranch, $otherBranches);
        } else {
            // Show current inventory if no filter
            $itemsCollection = $this->getCurrentStockData($mainBranch, $otherBranches);
        }

        // Paginate the collection to show 100 rows per page
        $perPage = 100;
        $page = request()->get('page', 1);
        $total = $itemsCollection->count();
        $itemsForCurrentPage = $itemsCollection->forPage($page, $perPage)->values();

        $allItems = new LengthAwarePaginator($itemsForCurrentPage, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('supervisor.inventory-history', compact('allItems', 'branches', 'mainBranch', 'otherBranches', 'filterDate', 'filterTime'));
    }

    /**
     * Export inventory history/current stock as CSV (Excel-friendly)
     */
    public function exportInventoryHistory(Request $request)
    {
        $filterDate = $request->input('date');
        $filterTime = $request->input('time');

        // Get branches and determine main branch same as inventoryHistory
        $branches = Branch::where('status', 1)->orderBy('name')->get();
        $mainBranch = $branches->where('name', 'Main Branch')->first();
        if (! $mainBranch) {
            $mainBranch = $branches->first();
        }
        $otherBranches = $branches->where('id', '!=', $mainBranch->id ?? null);

        if ($filterDate || $filterTime) {
            $itemsCollection = $this->getHistoricalStockData($filterDate, $filterTime, $mainBranch, $otherBranches);
        } else {
            $itemsCollection = $this->getCurrentStockData($mainBranch, $otherBranches);
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $columns = ['Item', 'Item Code', 'Main Stock'];
        foreach ($otherBranches as $branch) {
            $columns[] = $branch->name;
        }

        // Write header row
        $sheet->fromArray([$columns], null, 'A1');

        // Style header row
        $headerStyle = $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4CAF50');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

        // Write data rows
        $rowIndex = 2;
        foreach ($itemsCollection as $item) {
            $row = [
                $item['name'] ?? '',
                $item['item_code'] ?? '',
                $item['main_stock'] ?? 0,
            ];

            foreach ($otherBranches as $branch) {
                $row[] = $item['branch_stocks'][$branch->name] ?? 0;
            }

            $sheet->fromArray([$row], null, 'A' . $rowIndex);
            $rowIndex++;
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create filename
        $fileName = 'inventory-history-' . now()->format('Ymd-His') . '.xlsx';

        // Create response
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Get current stock data from inventory table
     */
    private function getCurrentStockData($mainBranch, $otherBranches)
    {
        return Item::with('inventory.branch')
            ->where('is_active', true)
            ->get()
            ->map(function ($item) use ($mainBranch, $otherBranches) {
                // Get main branch stock
                $mainStock = $item->inventory->where('branch_id', $mainBranch->id)->first();

                // Get other branches stock
                $branchStocks = [];
                foreach ($otherBranches as $branch) {
                    $branchInventory = $item->inventory->where('branch_id', $branch->id)->first();
                    $branchStocks[$branch->name] = $branchInventory ? $branchInventory->current_stock : 0;
                }

                return [
                    'id' => $item->id,
                    'name' => $item->item_name,
                    'item_code' => $item->item_code,
                    'category' => $item->category,
                    'main_stock' => $mainStock ? $mainStock->current_stock : 0,
                    'branch_stocks' => $branchStocks,
                    'last_updated' => $mainStock ? $mainStock->updated_at : null
                ];
            })
            ->sortBy('name')
            ->values();
    }

    /**
     * Get historical stock data based on date/time filters
     */
    private function getHistoricalStockData($filterDate, $filterTime, $mainBranch, $otherBranches)
    {
        // Build datetime condition
        $dateTimeCondition = function ($query) use ($filterDate, $filterTime) {
            if ($filterDate && $filterTime) {
                $dateTime = $filterDate . ' ' . $filterTime;
                $query->where('date_time', '>=', $dateTime);
            } elseif ($filterDate) {
                $query->whereDate('date_time', $filterDate);
            } elseif ($filterTime) {
                $query->whereTime('date_time', '>=', $filterTime);
            }
        };

        // Get inventory requests (main stock additions) with date/time filter
        $inventoryRequests = InventoryRequest::with(['inventoryRequestItems.item'])
            ->where($dateTimeCondition)
            ->where('status', 'completed')
            ->get();

        // Get stock transfers (branch distributions) with date/time filter
        $stockTransfers = StockTransfer::with(['transferItems.item', 'toBranch'])
            ->where($dateTimeCondition)
            ->where('status', 'accepted')
            ->get();

        // Get all active items
        $allItems = Item::where('is_active', true)->get();

        // Build result array
        $result = $allItems->map(function ($item) use ($inventoryRequests, $stockTransfers, $mainBranch, $otherBranches) {
            // Calculate main stock from inventory requests
            $mainStock = $inventoryRequests->flatMap(function ($request) {
                return $request->inventoryRequestItems;
            })
                ->where('item_id', $item->id)
                ->sum('quantity');

            // Calculate branch stocks from stock transfers
            $branchStocks = [];
            foreach ($otherBranches as $branch) {
                $branchStock = $stockTransfers
                    ->where('to_branch_id', $branch->id)
                    ->flatMap(function ($transfer) {
                        return $transfer->transferItems;
                    })
                    ->where('item_id', $item->id)
                    ->sum('quantity');

                $branchStocks[$branch->name] = (int) $branchStock;
            }

            return [
                'id' => $item->id,
                'name' => $item->item_name,
                'item_code' => $item->item_code,
                'category' => $item->category,
                'main_stock' => (int) $mainStock,
                'branch_stocks' => $branchStocks,
                'last_updated' => null
            ];
        })
            ->sortBy('name')
            ->values();

        return $result;
    }

    /**
     * Show the form for adding wastage
     */
    public function addWastage()
    {
        $items = Item::with(['inventory' => function ($query) {
            $query->where('branch_id', 1);
        }])
            ->where('is_active', true)
            ->get()
            ->filter(function ($item) {
                return $item->inventory && $item->inventory->sum('current_stock') > 0;
            })
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'item_code' => $item->item_code,
                    'available_stock' => (int) $item->inventory->sum('current_stock')
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


        foreach ($request->items as $index => $itemData) {
            $inventory = Inventory::where('item_id', $itemData['item_id'])
                ->where('branch_id', 1)
                ->first();
            $availableStock = $inventory ? $inventory->current_stock : 0;

            if ($itemData['wasted_quantity'] > $availableStock) {
                $item = Item::find($itemData['item_id']);
                return back()->withErrors([
                    "items.{$index}.wasted_quantity" => "Wasted quantity for '{$item->item_name}' cannot exceed available main stock ({$availableStock})."
                ])->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            $userId = (int) Auth::id();
            
            // Create wastage record
            $wastage = Wastage::create([
                'user_id' => $userId,
                'branch_id' => 1,
                'date_time' => $request->date_time,
                'remarks' => $request->remarks,
            ]);


            foreach ($request->items as $itemData) {
                $inventory = Inventory::where('item_id', $itemData['item_id'])
                    ->where('branch_id', 1)
                    ->first();
                $previousStock = $inventory ? $inventory->current_stock : 0;

                // Create wastage item record
                WastageItem::create([
                    'wastage_id' => $wastage->id,
                    'item_id' => $itemData['item_id'],
                    'wasted_quantity' => $itemData['wasted_quantity'],
                    'previous_stock' => $previousStock,
                ]);

                // Reduce main branch inventory stock
                if ($inventory) {
                    $inventory->decrement('current_stock', $itemData['wasted_quantity']);
                }
            }
        });

        return redirect()->route('supervisor.dashboard')
            ->with('success', 'Wastage has been recorded successfully and main inventory has been updated!');
    }

    /**
     * Show wastage records for supervisor
     */
    public function wastageView(Request $request)
    {
        $userId = (int) Auth::id();
        
        $query = Wastage::with(['wastageItems.item'])
            ->where('user_id', $userId)
            ->where('branch_id', 1)
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

    /**
     * List productions (inventory requests created by this supervisor)
     */
    public function productions(Request $request)
    {
        $userId = (int) Auth::id();
        
        $query = InventoryRequest::with(['inventoryRequestItems.item'])
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('date_time', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('date_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_time', '<=', $request->date_to);
        }

        $productions = $query->paginate(15);

        return view('supervisor.productions.index', compact('productions'));
    }

    /**
     * Show a single production (inventory request and its items)
     */
    public function showProduction(InventoryRequest $inventoryRequest)
    {
        $userId = (int) Auth::id();
        
        // Authorization: only owner can view
        if ($inventoryRequest->user_id !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        $inventoryRequest->load(['department', 'inventoryRequestItems.item']);
        return view('supervisor.productions.show', compact('inventoryRequest'));
    }

    /**
     * Edit production
     */
    public function editProduction(InventoryRequest $inventoryRequest)
    {
        $userId = (int) Auth::id();
        
        // Authorization: only owner can edit
        if ($inventoryRequest->user_id !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        $inventoryRequest->load(['inventoryRequestItems.item']);
        return view('supervisor.productions.edit', compact('inventoryRequest'));
    }

    /**
     * Update production (allow updating quantities and notes)
     */
    public function updateProduction(Request $request, InventoryRequest $inventoryRequest)
    {
        $userId = (int) Auth::id();
        
        // Authorization: only owner can update
        if ($inventoryRequest->user_id !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'date_time' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($request, $inventoryRequest) {
                // Update request-level fields
                $inventoryRequest->update([
                    'date_time' => $request->date_time,
                    'notes' => $request->notes,
                ]);

                // Build incoming items map by item_id => quantity
                $incoming = collect($request->items)->mapWithKeys(function ($it) {
                    return [$it['item_id'] => (int) $it['quantity']];
                })->all();

                // Existing items map
                $existing = $inventoryRequest->inventoryRequestItems->mapWithKeys(function ($it) {
                    return [$it->item_id => (int) $it->quantity];
                })->all();

                // Process diffs: for each item in union of keys
                $allItemIds = array_unique(array_merge(array_keys($incoming), array_keys($existing)));

                foreach ($allItemIds as $itemId) {
                    $newQty = $incoming[$itemId] ?? 0;
                    $oldQty = $existing[$itemId] ?? 0;
                    $delta = $newQty - $oldQty; // positive: increase main stock; negative: reduce main stock

                    // Apply to main inventory (branch_id = 1 or first inventory record)
                    $inventory = Inventory::where('item_id', $itemId)->first();

                    if ($delta !== 0) {
                        if ($inventory) {
                            // If reducing stock, prevent negative inventory
                            if ($delta < 0) {
                                $available = $inventory->current_stock;
                                $reduce = abs($delta);
                                if ($reduce > $available) {
                                    $item = Item::find($itemId);
                                    $itemName = $item ? $item->item_name : "Item ID {$itemId}";
                                    throw new \Exception("Cannot reduce {$itemName} by {$reduce}; only {$available} in stock.");
                                }
                                $inventory->decrement('current_stock', $reduce);
                            } else {
                                $inventory->increment('current_stock', $delta);
                            }
                        } else {
                            // If no inventory exists and delta > 0, create one
                            if ($delta > 0) {
                                Inventory::create([
                                    'item_id' => $itemId,
                                    'branch_id' => 1,
                                    'current_stock' => $delta,
                                    'low_stock_alert' => 10,
                                ]);
                            } else {
                                $item = Item::find($itemId);
                                $itemName = $item ? $item->item_name : "Item ID {$itemId}";
                                throw new \Exception("Inventory record missing for {$itemName} when trying to reduce stock.");
                            }
                        }
                    }
                }

                // Sync inventory request items: delete and recreate to match the requested list
                $inventoryRequest->inventoryRequestItems()->delete();
                foreach ($incoming as $itemId => $qty) {
                    InventoryRequestItem::create([
                        'inventory_request_id' => $inventoryRequest->id,
                        'item_id' => $itemId,
                        'quantity' => $qty,
                    ]);
                }
            });

            return redirect()->route('supervisor.productions.show', $inventoryRequest)
                ->with('success', 'Production updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Destroy a production record (and rollback inventory if desired)
     */
    public function destroyProduction(InventoryRequest $inventoryRequest)
    {
        $userId = (int) Auth::id();
        
        // Authorization: only owner can delete
        if ($inventoryRequest->user_id !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($inventoryRequest) {
            // Optionally rollback inventory (reduce main branch stock)
            foreach ($inventoryRequest->inventoryRequestItems as $iri) {
                $inventory = Inventory::where('item_id', $iri->item_id)->first();
                if ($inventory) {
                    $inventory->decrement('current_stock', $iri->quantity);
                }
            }

            $inventoryRequest->inventoryRequestItems()->delete();
            $inventoryRequest->delete();
        });

        return redirect()->route('supervisor.productions.index')
            ->with('success', 'Production deleted and inventory rolled back.');
    }
}
