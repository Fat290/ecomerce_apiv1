<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own transactions, admins can view all
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // User can view their own transactions, admins can view any
        return $user->id === $transaction->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Transactions are typically created by the system, but admins can create them
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Only admins can update transactions (e.g., change status)
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // Only admins can delete transactions
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin';
    }
}
