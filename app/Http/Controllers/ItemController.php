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
        $items = Item::with('inventory')->latest()->paginate(15);
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
            'item_name' => 'required|string|max:255',
            'category' => 'required|string|exists:categories,name,status,1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_alert' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
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
            'item_name' => 'required|string|max:255',
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
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $this->authorize('delete', $item);
        
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
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
