<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'receipt_no',
        'terminal',
        'user_name',
        'subtotal',
        'discount',
        'tax',
        'total',
        'payment_method',
        'customer_payment',
        'card_payment',
        'balance',
        'credit_balance',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'customer_payment' => 'decimal:2',
        'card_payment' => 'decimal:2',
        'balance' => 'decimal:2',
        'credit_balance' => 'decimal:2',
    ];

    /**
     * Get sale items for this sale
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
