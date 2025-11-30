<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Buyers can view their orders, sellers can view their shop orders, admins can view all
        return in_array($user->role, ['buyer', 'seller', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Buyer can view their own order, shop owner can view orders for their shop, admin can view any
        return $user->id === $order->buyer_id
            || $user->id === $order->shop->owner_id
            || $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only buyers can create orders
        return in_array($user->role, ['buyer', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        // Shop owner can update order status, buyer can cancel pending orders, admin can update any
        if ($user->role === 'admin') {
            return true;
        }

        // Shop owner can update orders for their shop
        if ($user->id === $order->shop->owner_id) {
            return true;
        }

        // Buyer can only cancel pending orders
        if ($user->id === $order->buyer_id && $order->status === 'pending') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only admins can delete orders
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }
}
