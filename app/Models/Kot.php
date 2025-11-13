<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kot extends Model
{
    use HasFactory;

    protected $fillable = [
        'kot_no',
        'sale_id',
        'branch_id',
        'user_id',
        'user_name',
        'type', // 'KOT' or 'BOT'
        'items',
        'notes',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    /**
     * Get the sale associated with this KOT/BOT
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the branch associated with this KOT/BOT
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this KOT/BOT
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
