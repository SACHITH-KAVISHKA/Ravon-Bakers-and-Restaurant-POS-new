<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryRequestItem extends Model
{
    protected $fillable = [
        'inventory_request_id',
        'item_id',
        'quantity',
    ];

    /**
     * Get the inventory request for this item
     */
    public function inventoryRequest(): BelongsTo
    {
        return $this->belongsTo(InventoryRequest::class);
    }

    /**
     * Get the item for this request item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
