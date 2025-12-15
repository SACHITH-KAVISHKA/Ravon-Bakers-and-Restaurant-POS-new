<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Auth;

class InventoryObserver
{
    public function updated(Inventory $inventory)
    {

        if ($inventory->isDirty('current_stock')) {

            $oldStock = $inventory->getOriginal('current_stock');
            $newStock = $inventory->current_stock;
            $diff = $newStock - $oldStock;

            InventoryLog::create([
                'inventory_id'   => $inventory->id,
                'item_id'        => $inventory->item_id,
                'branch_id'      => $inventory->branch_id,
                'previous_stock' => $oldStock,
                'new_stock'      => $newStock,
                'quantity_change'=> $diff,
                'reason'         => 'Stock Update',
                'user_id'        => Auth::id() ?? null,
            ]);
        }
    }
}
