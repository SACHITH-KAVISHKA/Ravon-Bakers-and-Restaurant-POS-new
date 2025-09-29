<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ItemController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::with('inventory')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate(15);
        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_alert' => 'required|integer|min:0',
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
                    'price' => $request->price,
                    'description' => $request->description,
                    'stock_quantity' => $request->stock_quantity,
                    'is_active' => true,
                ]);
                
                // Update inventory record
                if ($existingItem->inventory) {
                    $existingItem->inventory->update([
                        'current_stock' => $request->stock_quantity,
                        'low_stock_alert' => $request->low_stock_alert,
                    ]);
                } else {
                    // Create inventory record if it doesn't exist
                    Inventory::create([
                        'item_id' => $existingItem->id,
                        'current_stock' => $request->stock_quantity,
                        'low_stock_alert' => $request->low_stock_alert,
                    ]);
                }
            } else {
                // Generate next item code
                $itemCode = $this->generateNextItemCode();
                
                $item = Item::create([
                    'item_name' => $request->item_name,
                    'item_code' => $itemCode,
                    'category' => $request->category,
                    'price' => $request->price,
                    'description' => $request->description,
                    'stock_quantity' => $request->stock_quantity,
                    'is_active' => true,
                ]);

                // Create inventory record
                Inventory::create([
                    'item_id' => $item->id,
                    'current_stock' => $request->stock_quantity,
                    'low_stock_alert' => $request->low_stock_alert,
                ]);
            }
        });

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $item->load('inventory', 'purchases', 'saleItems');
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $this->authorize('update', $item);
        $categories = Category::active()->orderBy('name')->get();
        return view('items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
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
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_alert' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $item) {
            $item->update([
                'item_name' => $request->item_name,
                // item_code is not updated - it stays the same
                'category' => $request->category,
                'price' => $request->price,
                'description' => $request->description,
                'stock_quantity' => $request->stock_quantity,
            ]);

            // Update inventory record
            $item->inventory->update([
                'current_stock' => $request->stock_quantity,
                'low_stock_alert' => $request->low_stock_alert,
            ]);
        });

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Item $item)
    {
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
    
    /**
     * Update stock quantity for an item (for restocking purposes)
     */
    public function updateStock(Request $request, Item $item)
    {
        $this->authorize('update', $item);
        
        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'operation' => 'required|in:set,add,subtract',
            'reason' => 'nullable|string|max:255'
        ]);
        
        $success = false;
        
        switch ($request->operation) {
            case 'set':
                $success = $item->setStock($request->stock_quantity);
                break;
            case 'add':
                $success = $item->addStock($request->stock_quantity);
                break;
            case 'subtract':
                $currentStock = $item->getCurrentStock();
                $newStock = max(0, $currentStock - $request->stock_quantity);
                $success = $item->setStock($newStock);
                break;
        }
        
        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock. Please try again.'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully',
            'new_stock' => $item->fresh()->inventory->current_stock
        ]);
    }
    
    /**
     * Get low stock items
     */
    public function lowStockItems()
    {
        $lowStockItems = Item::with('inventory')
            ->where('is_active', true)
            ->whereHas('inventory', function ($query) {
                $query->whereRaw('current_stock <= low_stock_alert');
            })
            ->get();
            
        return response()->json([
            'low_stock_items' => $lowStockItems
        ]);
    }
    
    /**
     * Get out of stock items
     */
    public function outOfStockItems()
    {
        $outOfStockItems = Item::with('inventory')
            ->where('is_active', true)
            ->whereHas('inventory', function ($query) {
                $query->where('current_stock', '<=', 0);
            })
            ->get();
            
        return response()->json([
            'out_of_stock_items' => $outOfStockItems
        ]);
    }
}
