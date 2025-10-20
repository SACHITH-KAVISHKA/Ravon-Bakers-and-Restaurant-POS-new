<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Branch;
use App\Models\ItemBranchPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemBranchPriceController extends Controller
{
    public function index(Item $item)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $item->load('branchPrices.branch');
        return view('items.prices.index', compact('item'));
    }

    public function store(Request $request, Item $item)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'price' => 'required|numeric|min:0',
        ]);

        $exists = ItemBranchPrice::where('item_id', $item->id)
            ->where('branch_id', $data['branch_id'])
            ->first();

        if ($exists) {
            $exists->update(['price' => $data['price']]);
        } else {
            ItemBranchPrice::create([
                'item_id' => $item->id,
                'branch_id' => $data['branch_id'],
                'price' => $data['price'],
            ]);
        }

        return redirect()->route('items.prices.index', $item)->with('success', 'Branch price saved.');
    }

    public function update(Request $request, Item $item, ItemBranchPrice $price)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $data = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $price->update(['price' => $data['price']]);

        return redirect()->route('items.prices.index', $item)->with('success', 'Branch price updated.');
    }

    public function destroy(Item $item, ItemBranchPrice $price)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $price->delete();
        return redirect()->route('items.prices.index', $item)->with('success', 'Branch price removed.');
    }
}
