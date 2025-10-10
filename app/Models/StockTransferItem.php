<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = [
        'transfer_id',
        'item_id',
        'quantity',
        'available_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
    ];

    /**
     * Get the transfer for this item
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'transfer_id');
    }

    /**
     * Get the item for this transfer
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}