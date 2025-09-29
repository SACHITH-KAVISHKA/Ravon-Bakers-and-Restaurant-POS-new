<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
    protected $fillable = [
        'item_name',
        'item_code',
        'category',
        'price',
        'description',
        'stock_quantity',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the inventory for this item
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

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
     * Check if item is in stock
     */
    public function isInStock(): bool
    {
        return $this->inventory && $this->inventory->current_stock > 0;
    }
    
    /**
     * Check if item has low stock
     */
    public function hasLowStock(): bool
    {
        return $this->inventory && $this->inventory->isLowStock();
    }
    
    /**
     * Check if item is out of stock
     */
    public function isOutOfStock(): bool
    {
        return !$this->inventory || $this->inventory->current_stock <= 0;
    }
    
    /**
     * Get current stock quantity
     */
    public function getCurrentStock(): int
    {
        return $this->inventory ? $this->inventory->current_stock : 0;
    }
    
    /**
     * Scope for in-stock items only (for POS system)
     */
    public function scopeInStock($query)
    {
        return $query->where('is_active', true)
                    ->whereHas('inventory', function ($q) {
                        $q->where('current_stock', '>', 0);
                    });
    }
    
    /**
     * Scope for low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->where('is_active', true)
                    ->whereHas('inventory', function ($q) {
                        $q->whereRaw('current_stock <= low_stock_alert');
                    });
    }
    
    /**
     * Scope for out of stock items
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('is_active', true)
                    ->whereHas('inventory', function ($q) {
                        $q->where('current_stock', '<=', 0);
                    });
    }
    
    /**
     * Reduce stock quantity (used for sales)
     */
    public function reduceStock(int $quantity): bool
    {
        if (!$this->inventory) {
            return false;
        }
        
        // Check if we have enough stock
        if ($this->inventory->current_stock < $quantity) {
            return false;
        }
        
        $newStock = $this->inventory->current_stock - $quantity;
        
        // Update both inventory and item model in a transaction
        DB::transaction(function () use ($newStock) {
            // Update inventory table
            $this->inventory->update(['current_stock' => $newStock]);
            
            // Update item model
            $this->update(['stock_quantity' => $newStock]);
        });
        
        return true;
    }
    
    /**
     * Add stock quantity (used for restocking)
     */
    public function addStock(int $quantity): bool
    {
        if (!$this->inventory) {
            return false;
        }
        
        $newStock = $this->inventory->current_stock + $quantity;
        
        // Update both inventory and item model in a transaction
        DB::transaction(function () use ($newStock) {
            // Update inventory table
            $this->inventory->update(['current_stock' => $newStock]);
            
            // Update item model
            $this->update(['stock_quantity' => $newStock]);
        });
        
        return true;
    }
    
    /**
     * Set exact stock quantity
     */
    public function setStock(int $quantity): bool
    {
        if (!$this->inventory) {
            return false;
        }
        
        // Update both inventory and item model in a transaction
        DB::transaction(function () use ($quantity) {
            // Update inventory table
            $this->inventory->update(['current_stock' => $quantity]);
            
            // Update item model
            $this->update(['stock_quantity' => $quantity]);
        });
        
        return true;
    }
}
