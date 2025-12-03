<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;
    protected $fillable = [
        'adjustment_date',
        'branch_id',
        'cashier_name',
        'supervisor_id',
        'notes',
        'total_variance', // Value
        'status',
    ];

/**
     * Relationship: One Adjustment has many Details (items).
     */
    public function details()
    {
        return $this->hasMany(StockAdjustmentDetail::class);
    }

    /**
     * Relationship: An adjustment belongs to a Branch.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relationship: An adjustment is created by a Supervisor (User).
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
