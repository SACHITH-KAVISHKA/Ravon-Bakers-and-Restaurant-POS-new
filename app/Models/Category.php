<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'status'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    // Scope to get only active categories (status = 1)
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Soft delete by setting status to 0
    public function softDelete()
    {
        $this->status = 0;
        $this->save();
    }

    // Restore by setting status back to 1
    public function restore()
    {
        $this->status = 1;
        $this->save();
    }

    // Check if category is active
    public function isActive()
    {
        return $this->status == 1;
    }
}
