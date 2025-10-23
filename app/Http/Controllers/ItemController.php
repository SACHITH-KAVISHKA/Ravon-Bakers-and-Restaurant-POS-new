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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ItemController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only admin can access item management
        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can access item management.');
        }
        
        // Admin sees all items
    $query = Item::with(['inventory', 'branchPrices.branch'])
                    ->where('is_active', true);
        
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('item_code', 'LIKE', '%' . $search . '%')
                  ->orWhere('category', 'LIKE', '%' . $search . '%');
            });
        }
        
    $items = $query->orderBy('item_name', 'asc')
              ->paginate(100);

    $branches = Branch::orderBy('name')->get();

    return view('items.index', compact('items', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only admin can create items
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can create items.');
        }
        
        $this->authorize('create', Item::class);
        $categories = Category::active()->orderBy('name')->get();
        $nextItemCode = $this->generateNextItemCode();
        return view('items.create', compact('categories', 'nextItemCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only admin can store items
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can create items.');
        }
        
        $this->authorize('create', Item::class);
        
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
        ]);

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
        });

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        // Only admin can view item details
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can view item details.');
        }
        
        $item->load('purchases', 'saleItems');
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        // Only admin can edit items
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can edit items.');
        }
        
        $this->authorize('update', $item);
        $categories = Category::active()->orderBy('name')->get();
        return view('items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        // Only admin can update items
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can update items.');
        }
        
        $this->authorize('update', $item);
        
        $request->validate([
            'item_name' => [
                'required',
                'string',
                'max:255',
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
        ]);

        DB::transaction(function () use ($request, $item) {
            $item->update([
                'item_name' => $request->item_name,
                // item_code is not updated - it stays the same
                'category' => $request->category,
                //'price' => moved to branch prices
                'description' => $request->description,
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
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Item $item)
    {
        // Only admin can delete items
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only administrators can delete items.');
        }
        
        $this->authorize('delete', $item);
        
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
