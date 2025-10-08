<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryRequest extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'date_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    /**
     * Get the user who created this request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department for this request
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the inventory request items
     */
    public function inventoryRequestItems(): HasMany
    {
        return $this->hasMany(InventoryRequestItem::class);
    }
}
