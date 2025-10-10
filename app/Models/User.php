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
        'email',
        'password',
        'role',
        'branch_id',
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
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
        return $this->isAdmin() || $this->isSupervisor();
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
}
