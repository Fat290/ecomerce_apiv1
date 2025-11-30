<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own notifications
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Notification $notification): bool
    {
        // User can only view their own notifications
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins and system can create notifications
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Notification $notification): bool
    {
        // User can update their own notifications (e.g., mark as read)
        return $user->id === $notification->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Notification $notification): bool
    {
        // User can delete their own notifications, admins can delete any
        return $user->id === $notification->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Notification $notification): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Notification $notification): bool
    {
        return $user->role === 'admin';
    }
}
