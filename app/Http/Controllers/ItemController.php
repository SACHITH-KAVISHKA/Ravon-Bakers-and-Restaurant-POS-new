<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryRequestItem;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ItemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Apply 'manage-users' gate to all controller methods.
     * This will allow 'admin' and 'director' roles.
     */
    public function __construct()
    {
        $this->middleware('can:manage-users');
    }

    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     // Admin sees all items
    //     $query = Item::with(['inventory', 'branchPrices.branch'])
    //                  ->where('is_active', true);

    //     // Apply search filter
    //     if ($request->has('search') && !empty($request->search)) {
    //         $search = $request->search;
    //         $query->where(function($q) use ($search) {
    //             $q->where('item_name', 'LIKE', '%' . $search . '%')
    //               ->orWhere('item_code', 'LIKE', '%' . $search . '%')
    //               ->orWhere('category', 'LIKE', '%' . $search . '%');
    //         });
    //     }

    //     $items = $query->orderBy('item_name', 'asc')
    //                  ->paginate(100);

    //     $branches = Branch::orderBy('name')->get();

    //     return view('items.index', compact('items', 'branches'));
    // }

    public function index(Request $request)
    {
        $currentUser = auth()->user();

        $role = strtolower($currentUser->role ?? '');
        $name = str_replace(' ', '', strtolower($currentUser->name ?? ''));
        $username = str_replace(' ', '', strtolower($currentUser->username ?? ''));

        $isHolding = ($role === 'holding' || str_contains($name, 'adminh') || str_contains($username, 'adminh'));
        $isDelight = ($role === 'delight' || str_contains($name, 'admind') || str_contains($username, 'admind'));
        $isAdminOrDirector = in_array($role, ['admin', 'director']);

        if (!$isAdminOrDirector && !$isHolding && !$isDelight) {
            abort(403, 'Unauthorized access to Item Management.');
        }

        $query = \App\Models\Item::with(['inventory', 'branchPrices.branch'])
                     ->where('is_active', true);

        $branchQuery = \App\Models\Branch::where('id', '!=', 1)->orderBy('name');

        if ($isHolding) {
            $query->whereHas('branchPrices', function($q) {
                $q->whereIn('branch_id', [6, 7]);
            });
            $branchQuery->whereIn('id', [6, 7]);
        }
        elseif ($isDelight) {
            $query->whereHas('branchPrices', function($q) {
                $q->whereIn('branch_id', [2, 3, 4, 5]);
            });
            $branchQuery->whereIn('id', [2, 3, 4, 5]);
        }

        // Search Filter එක
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('item_code', 'LIKE', '%' . $search . '%')
                  ->orWhere('category', 'LIKE', '%' . $search . '%');
            });
        }

        $items = $query->orderBy('item_name', 'asc')->paginate(100);
        $branches = $branchQuery->get(); // View එකට යැවීමට Branches ලබාගැනීම

        $canManageItems = true;

        // $branches දත්තය compact හරහා පිටුවට යැවීම
        return view('items.index', compact('items', 'canManageItems', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $this->authorize('create', Item::class);
        $categories = Category::active()->orderBy('name')->get();
        $nextItemCode = $this->generateNextItemCode();
        return view('items.create', compact('categories', 'nextItemCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $this->authorize('create', Item::class);

        $request->validate([
            'item_name' => [
                'required',
                'string',
                'max:255',
                // Allow duplicate names only if existing item is inactive
                function ($attribute, $value, $fail) {
                    $existingActiveItem = Item::where('item_name', $value)
                                              ->where('is_active', true)
                                              ->exists();
                    if ($existingActiveItem) {
                        $fail('An active item with this name already exists.');
                    }
                }
            ],
            'category' => 'required|string|exists:categories,name,status,1',
            // 'price' moved to branch-specific pricing
            'description' => 'nullable|string',
            'stock_count' => 'required|boolean',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Check if an inactive item with the same name already exists
                $existingItem = Item::where('item_name', $request->item_name)
                                  ->where('is_active', false)
                                  ->first();

                if ($existingItem) {
                // Reactivate the existing item
                $existingItem->update([
                    'category' => $request->category,
                        //'price' => moved to branch prices
                    'description' => $request->description,
                    'is_active' => true,
                    'stock_count' => $request->stock_count ?? true,
                ]);
                $item = $existingItem;
            } else {
                // Generate next item code
                $itemCode = $this->generateNextItemCode();

                $item = Item::create([
                    'item_name' => $request->item_name,
                    'item_code' => $itemCode,
                    'category' => $request->category,
                    'description' => $request->description,
                    'is_active' => true,
                    'stock_count' => $request->stock_count ?? true,
                ]);
            }

            // Save branch-specific prices if provided
            if ($request->has('branch_prices') && is_array($request->branch_prices)) {
                foreach ($request->branch_prices as $bp) {
                    // Guard: ensure branch_id is present and numeric
                    if (!isset($bp['branch_id'])) continue;
                    // Sometimes client JS can send string 'undefined' - skip non-numeric values
                    if (!is_numeric($bp['branch_id'])) continue;

                    $branchId = (int) $bp['branch_id'];
                    $price = is_numeric($bp['price']) ? (float) $bp['price'] : 0;

                    \App\Models\ItemBranchPrice::updateOrCreate(
                        ['item_id' => $item->id, 'branch_id' => $branchId],
                        ['price' => $price]
                    );
                }
            }

            // Automatically create inventory record for Main Branch only (branch_id = 1)
            $existingInventory = Inventory::where('item_id', $item->id)
                                          ->where('branch_id', 1)
                                          ->first();

            if (!$existingInventory) {
                Inventory::create([
                    'item_id' => $item->id,
                    'branch_id' => 1, // Main Branch
                    'current_stock' => 0,
                    'low_stock_alert' => 10,
                ]);
            }
        });

        return redirect()->route('items.index')->with('success', 'Item created successfully.');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Item Store Database Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database error occurred while creating item. Please contact support.')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Item Store Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while creating item. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $item->load('purchases', 'saleItems');
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        // $this->authorize('update', $item);
        $categories = Category::active()->orderBy('name')->get();
        return view('items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        // $this->authorize('update', $item);

        $request->validate([
            'item_name' => [
                'required',
                'string',
                'max:2255',
                // Allow duplicate names only if it's the same item or existing item is inactive
                function ($attribute, $value, $fail) use ($item) {
                    $existingActiveItem = Item::where('item_name', $value)
                                              ->where('is_active', true)
                                              ->where('id', '!=', $item->id)
                                              ->exists();
                    if ($existingActiveItem) {
                        $fail('An active item with this name already exists.');
                    }
                }
            ],
            'category' => 'required|string|exists:categories,name,status,1',
            // 'price' moved to branch-specific pricing
            'description' => 'nullable|string',
            'stock_count' => 'required|boolean',
        ]);

        try {
            DB::transaction(function () use ($request, $item) {
                $item->update([
                    'item_name' => $request->item_name,
                // item_code is not updated - it stays the same
                'category' => $request->category,
                //'price' => moved to branch prices
                'description' => $request->description,
                'stock_count' => $request->stock_count ?? true,
            ]);

            // Update branch-specific prices if provided
            if ($request->has('branch_prices') && is_array($request->branch_prices)) {
                $incoming = [];
                foreach ($request->branch_prices as $bp) {
                    if (empty($bp['branch_id'])) continue;
                    $incoming[$bp['branch_id']] = $bp['price'] ?? 0;
                    \App\Models\ItemBranchPrice::updateOrCreate(
                        ['item_id' => $item->id, 'branch_id' => $bp['branch_id']],
                        ['price' => $bp['price'] ?? 0]
                    );
                }

                // Delete branch prices that were removed in the form
                \App\Models\ItemBranchPrice::where('item_id', $item->id)
                    ->whereNotIn('branch_id', array_keys($incoming))
                    ->delete();
                }
            });

            return redirect()->route('items.index')->with('success', 'Item updated successfully.');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Item Update Database Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Database error occurred while updating item. Please contact support.')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Item Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while updating item. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Item $item)
    {
        // $this->authorize('delete', $item);

        // Soft delete by setting is_active to false
        $item->update(['is_active' => false]);

        return redirect()->route('items.index')->with('success', 'Item deactivated successfully.');
    }

    /**
     * Generate the next item code in sequence (starting from 0001)
     */
    private function generateNextItemCode()
    {
        // Use database locking to prevent race conditions
        return DB::transaction(function () {
            // Get the latest item ordered by ID to ensure we get the most recently created
            $latestItem = Item::lockForUpdate()->orderBy('id', 'desc')->first();

            if (!$latestItem || !$latestItem->item_code) {
                // Start with 0001 if no items exist
                return '0001';
            }

            // Extract the numeric part from the item code
            $latestCode = $latestItem->item_code;

            // If the code is numeric, increment it
            if (is_numeric($latestCode)) {
                $nextNumber = (int)$latestCode + 1;
            } else {
                // If existing codes don't follow the numeric pattern,
                // find the highest numeric code or start from 1
                $highestNumericCode = Item::whereRaw('item_code REGEXP ?', ['^[0-9]+$'])
                                          ->lockForUpdate()
                                          ->orderByRaw('CAST(item_code AS UNSIGNED) DESC')
                                          ->value('item_code');

                $nextNumber = $highestNumericCode ? (int)$highestNumericCode + 1 : 1;
            }

            // Format as 4-digit padded string (0001, 0002, etc.)
            return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
