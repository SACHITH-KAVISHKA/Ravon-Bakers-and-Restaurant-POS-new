<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wastage extends Model
{
    protected $fillable = [
        'user_id',
        'date_time',
        'remarks',
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    /**
     * Get the user who created this wastage record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wastage items for this wastage
     */
    public function wastageItems(): HasMany
    {
        return $this->hasMany(WastageItem::class);
    }

    /**
     * Get total wasted items count
     */
    public function getTotalWastedAttribute(): int
    {
        return $this->wastageItems()->sum('wasted_quantity');
    }
}
