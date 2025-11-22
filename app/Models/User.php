<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'branch_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'password' => 'hashed',
        ];
    }

    /**
     * Casts property for Eloquent.
     */
    protected $casts = [
        'status' => 'boolean',
        'branch_id' => 'integer',
    ];

    /**
     * Get the column name for the "username" column (for authentication).
     */
    public function username(): string
    {
        return 'username';
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 1 || $this->status === true;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
       /**
     * Check if user is director
     */
    public function isDirector(): bool
    {
        return $this->role === 'director';
    }
    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    /**
     * Check if user has management privileges (admin or supervisor)
     */
    public function hasManagementPrivileges(): bool
    {
        return $this->isAdmin() || $this->isSupervisor() || $this->isDirector();
    }

    /**
     * Get the branch that the user belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get inventory requests created by this user
     */
    public function inventoryRequests()
    {
        return $this->hasMany(InventoryRequest::class);
    }

    /**
     * Get wastages created by this user
     */
    public function wastages()
    {
        return $this->hasMany(Wastage::class);
    }

    /**
     * Get stock transfers created by this user
     */
    public function createdStockTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'created_by');
    }

    /**
     * Get stock transfers processed by this user
     */
    public function processedStockTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'processed_by');
    }

    /**
     * Get inventory request items received by this user
     */
    public function receivedInventoryRequestItems()
    {
        return $this->hasMany(InventoryRequestItem::class, 'received_by');
    }
}
