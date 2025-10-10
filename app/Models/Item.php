<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'item_name',
        'item_code',
        'category',
        'price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get purchases for this item
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get sale items for this item
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get inventory for this item
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Get wastage items for this item
     */
    public function wastageItems(): HasMany
    {
        return $this->hasMany(WastageItem::class);
    }

    /**
     * Get inventory request items for this item
     */
    public function inventoryRequestItems(): HasMany
    {
        return $this->hasMany(InventoryRequestItem::class);
    }

    /**
     * Get available stock from inventory requests minus wastages
     */
    public function getAvailableStockFromRequestsAttribute(): int
    {
        $totalRequested = $this->inventoryRequestItems()->sum('quantity');
        $totalWasted = $this->wastageItems()->sum('wasted_quantity');
        return max(0, $totalRequested - $totalWasted);
    }
}
