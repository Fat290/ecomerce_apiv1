<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Auth\Access\Response;

class VoucherPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone can view active vouchers (public)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Voucher $voucher): bool
    {
        // Anyone can view a voucher (public)
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only sellers and admins can create vouchers
        return in_array($user->role, ['seller', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Voucher $voucher): bool
    {
        // Voucher's shop owner or admin can update
        return $user->id === $voucher->shop->owner_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Voucher $voucher): bool
    {
        // Voucher's shop owner or admin can delete
        return $user->id === $voucher->shop->owner_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Voucher $voucher): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Voucher $voucher): bool
    {
        return $user->role === 'admin';
    }
}
