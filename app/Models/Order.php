<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_time',
        'branch_id',
        'user_id',
        'customer_name',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_method',
        'notes',
        'status',
        'order_status',
    ];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
