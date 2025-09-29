<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_name',
        'purchase_date',
        'item_id',
        'quantity',
        'unit_price',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the item for this purchase
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
