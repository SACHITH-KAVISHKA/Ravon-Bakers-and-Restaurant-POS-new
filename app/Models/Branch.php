<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get users assigned to this branch.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get inventories for this branch.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get stock transfers received by this branch.
     */
    public function receivedTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Check if branch is active.
     */
    public function isActive(): bool
    {
        return $this->status == 1;
    }
}
