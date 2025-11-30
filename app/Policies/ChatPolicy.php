<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own chats
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Chat $chat): bool
    {
        // User can only view chats they're part of (as buyer or seller)
        return $user->id === $chat->buyer_id || $user->id === $chat->seller_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a chat
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chat $chat): bool
    {
        // User can update chats they're part of
        return $user->id === $chat->buyer_id || $user->id === $chat->seller_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chat $chat): bool
    {
        // User can delete chats they're part of, admins can delete any
        return $user->id === $chat->buyer_id
            || $user->id === $chat->seller_id
            || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Chat $chat): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Chat $chat): bool
    {
        return $user->role === 'admin';
    }
}
