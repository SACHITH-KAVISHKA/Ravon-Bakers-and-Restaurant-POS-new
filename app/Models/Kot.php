<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kot extends Model
{
    protected $fillable = [
        'kot_no',
        'type',
        'sale_id',
        'branch_id',
        'user_id',
        'user_name',
        'notes',
    ];

    protected $casts = [
        // Removed status-related timestamp casts
    ];

    /**
     * Get KOT items for this KOT/BOT
     */
    public function kotItems(): HasMany
    {
        return $this->hasMany(KotItem::class);
    }

    /**
     * Get the sale associated with this KOT/BOT
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the branch for this KOT/BOT
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user (cashier/waiter) who created this KOT/BOT
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total amount for this KOT/BOT
     */
    public function getTotalAttribute()
    {
        return $this->kotItems->sum('total_price');
    }
}

