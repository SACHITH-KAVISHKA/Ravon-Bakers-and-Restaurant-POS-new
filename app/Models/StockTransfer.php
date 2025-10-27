<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    protected $fillable = [
        'from_branch_id',
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
        'from_branch_id' => 'integer',
        'to_branch_id' => 'integer',
        'created_by' => 'integer',
        'processed_by' => 'integer',
        'date_time' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the source branch for this transfer
     */
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * Get the source name (Central Inventory if from_branch_id is null or 1, otherwise branch name)
     */
    public function getSourceNameAttribute(): string
    {
        if (!$this->from_branch_id || $this->from_branch_id === 1) {
            return 'Central Inventory';
        }
        return $this->fromBranch ? $this->fromBranch->name : 'Unknown Branch';
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