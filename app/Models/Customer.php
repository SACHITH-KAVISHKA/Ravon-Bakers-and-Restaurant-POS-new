<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vat_no',
        'telephone',
        'address',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_name', 'name');
    }
}
