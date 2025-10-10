<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $fillable = [
        'to_branch_id',
        'date_time',
        'status',
        'created_by',
        'processed_by',
        'notes',
        'rejection_reason',
        'processed_at',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the source name (always "Central Inventory" since we removed branch-to-branch transfers)
     */
    public function getSourceNameAttribute(): string
    {
        return 'Central Inventory';
    }

    /**
     * Get the destination branch for this transfer
     */
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Get the user who created this transfer
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who processed this transfer
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the items for this transfer
     */
    public function transferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'transfer_id');
    }

    /**
     * Check if transfer is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transfer is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if transfer is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get total quantity being transferred
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->transferItems->sum('quantity');
    }

    /**
     * Get total number of items in transfer
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->transferItems->count();
    }
}