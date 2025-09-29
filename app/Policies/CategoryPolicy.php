<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Both admin and staff can view
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $category): bool
    {
        return true; // Both admin and staff can view
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // Only admin can create
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?Category $category = null): bool
    {
        return $user->isAdmin(); // Only admin can update
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?Category $category = null): bool
    {
        return $user->isAdmin(); // Only admin can delete
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        return $user->isAdmin(); // Only admin can restore
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return $user->isAdmin(); // Only admin can force delete
    }
}
