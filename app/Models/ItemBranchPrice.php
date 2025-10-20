<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemBranchPrice extends Model
{
    protected $table = 'item_branch_prices';

    protected $fillable = [
        'item_id',
        'branch_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
