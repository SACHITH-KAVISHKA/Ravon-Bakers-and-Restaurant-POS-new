<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KotItem extends Model
{
    protected $fillable = [
        'kot_id',
        'item_id',
        'item_name',
        'quantity',
        'unit_price',
        'total_price',
        'special_instructions',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the KOT/BOT for this item
     */
    public function kot(): BelongsTo
    {
        return $this->belongsTo(Kot::class);
    }

    /**
     * Get the item for this KOT item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

