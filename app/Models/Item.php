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
        'item_type',
        'description',
        'is_active',
        'stock_count',
        'vat_applicable',
        'sscl_applicable',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'stock_count' => 'boolean',
        'vat_applicable' => 'boolean',
        'sscl_applicable' => 'boolean',
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
     * Get inventory for this item (across all branches)
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get inventory for main branch
     */
    public function mainInventory()
    {
        return $this->hasOne(Inventory::class)->whereHas('branch', function($query) {
            $query->where('name', 'Main Branch');
        });
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
     * Branch-specific prices for this item
     */
    public function branchPrices()
    {
        return $this->hasMany(ItemBranchPrice::class);
    }

    /**
     * Fallback price accessor: return the first branch price if items.price column is removed.
     */
    public function getPriceAttribute($value)
    {
        // If a DB value exists (rare if column was present) prefer it
        if (! is_null($value)) {
            return $value;
        }

        // Fallback: use the first branch price (if any)
        $bp = $this->branchPrices()->first();
        return $bp ? (float) $bp->price : 0.0;
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
