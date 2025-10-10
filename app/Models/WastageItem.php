<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastageItem extends Model
{
    protected $fillable = [
        'wastage_id',
        'item_id',
        'wasted_quantity',
        'previous_stock',
    ];

    /**
     * Get the wastage record for this item
     */
    public function wastage(): BelongsTo
    {
        return $this->belongsTo(Wastage::class);
    }

    /**
     * Get the item for this wastage item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get remaining stock after wastage (from inventory table)
     */
    public function getRemainingStockAttribute(): int
    {
        return $this->previous_stock - $this->wasted_quantity;
    }
}
