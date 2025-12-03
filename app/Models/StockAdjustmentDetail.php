<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'item_id',
        'current_stock',
        'actual_stock',
        'variance',       // Quantity
        'variance_amount', // Value
        'status',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    // Changed relationship from product() to item()
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
