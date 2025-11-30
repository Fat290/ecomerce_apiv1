<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShopPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone can view shops (public listing)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shop $shop): bool
    {
        // Anyone can view a shop (public)
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only sellers and admins can create shops
        return in_array($user->role, ['seller', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shop $shop): bool
    {
        // Shop owner or admin can update
        return $user->id === $shop->owner_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shop $shop): bool
    {
        // Shop owner or admin can delete
        return $user->id === $shop->owner_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Shop $shop): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shop $shop): bool
    {
        return $user->role === 'admin';
    }
}
