<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserManagementController extends Controller
{
    /**
     * Check if the authenticated user is an admin.
     */
    private function checkAdmin(): ?User
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user || $user->role !== 'admin') {
                return null;
            }

            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Display a listing of users.
     * Supports filtering by role and status.
     */
    public function index(Request $request): JsonResponse
    {
        $admin = $this->checkAdmin();
        if (!$admin) {
            return $this->forbiddenResponse('Only administrators can access this endpoint');
        }

        try {
            $query = User::query();

            // Filter by role
            if ($request->has('role') && in_array($request->role, ['admin', 'seller', 'buyer'])) {
                $query->where('role', $request->role);
            }

            // Filter by status
            if ($request->has('status') && in_array($request->status, ['active', 'banned', 'pending'])) {
                $query->where('status', $request->status);
            }

            // Search by name or email
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Load relationships
            $query->with(['shops', 'orders', 'reviews']);

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $users = $query->latest()->paginate($perPage);

            return $this->paginatedResponse($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve users: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user with full details.
     */
    public function show(string $id): JsonResponse
    {
        $admin = $this->checkAdmin();
        if (!$admin) {
            return $this->forbiddenResponse('Only administrators can access this endpoint');
        }

        try {
            $user = User::with([
                'shops.products',
                'shops.orders',
                'orders',
                'reviews',
                'transactions',
                'userNotifications'
            ])->find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            return $this->successResponse($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve user: ' . $e->getMessage());
        }
    }

    /**
     * Update user status (active, banned, pending).
     */
    public function updateStatus(UpdateUserStatusRequest $request, string $id): JsonResponse
    {
        $admin = $this->checkAdmin();
        if (!$admin) {
            return $this->forbiddenResponse('Only administrators can access this endpoint');
        }

        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            // Prevent admin from changing their own status
            if ($user->id === $admin->id) {
                return $this->errorResponse('You cannot change your own status', 403);
            }

            $oldStatus = $user->status;
            $newStatus = $request->status;

            $user->update(['status' => $newStatus]);

            // If banning a seller, also deactivate their shop(s)
            if ($newStatus === 'banned' && $user->role === 'seller') {
                Shop::where('owner_id', $user->id)->update(['status' => 'banned']);
            }

            // If activating a seller, optionally activate their shop(s)
            if ($newStatus === 'active' && $oldStatus === 'pending' && $user->role === 'seller') {
                // Optionally activate shops - you might want to review this first
                // Shop::where('owner_id', $user->id)->update(['status' => 'active']);
            }

            $user->load(['shops']);

            return $this->successResponse($user, "User status updated from '{$oldStatus}' to '{$newStatus}' successfully");
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update user status: ' . $e->getMessage());
        }
    }

    /**
     * Ban a user (convenience method).
     */
    public function ban(string $id): JsonResponse
    {
        $admin = $this->checkAdmin();
        if (!$admin) {
            return $this->forbiddenResponse('Only administrators can access this endpoint');
        }

        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            if ($user->id === $admin->id) {
                return $this->errorResponse('You cannot ban yourself', 403);
            }

            if ($user->status === 'banned') {
                return $this->errorResponse('User is already banned', 400);
            }

            $oldStatus = $user->status;
            $user->update(['status' => 'banned']);

            // If banning a seller, deactivate their shop(s)
            if ($user->role === 'seller') {
                Shop::where('owner_id', $user->id)->update(['status' => 'banned']);
            }

            // Revoke all refresh tokens for the banned user
            $user->refreshTokens()->update(['is_revoked' => true]);

            $user->load(['shops']);

            return $this->successResponse($user, "User has been banned successfully");
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to ban user: ' . $e->getMessage());
        }
    }

    /**
     * Unban a user (activate them).
     */
    public function unban(string $id): JsonResponse
    {
        $admin = $this->checkAdmin();
        if (!$admin) {
            return $this->forbiddenResponse('Only administrators can access this endpoint');
        }

        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            if ($user->status !== 'banned') {
                return $this->errorResponse('User is not banned', 400);
            }

            $user->update(['status' => 'active']);

            // If unbanning a seller, you might want to reactivate their shop(s) after review
            // For now, we'll leave shops inactive - admin can manually activate them

            $user->load(['shops']);

            return $this->successResponse($user, "User has been unbanned successfully");
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to unban user: ' . $e->getMessage());
        }
    }

    /**
     * Activate a seller (set status to active).
     */
    public function activateSeller(string $id): JsonResponse
    {
        $admin = $this->checkAdmin();
        if (!$admin) {
            return $this->forbiddenResponse('Only administrators can access this endpoint');
        }

        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            if ($user->role !== 'seller') {
                return $this->errorResponse('User is not a seller', 400);
            }

            if ($user->status === 'active') {
                return $this->errorResponse('Seller is already active', 400);
            }

            $oldStatus = $user->status;
            $user->update(['status' => 'active']);

            // Optionally activate their shop(s) as well
            $shop = Shop::where('owner_id', $user->id)->first();
            if ($shop && $shop->status === 'pending') {
                $shop->update(['status' => 'active']);
            }

            $user->load(['shops']);

            return $this->successResponse($user, "Seller status updated from '{$oldStatus}' to 'active' successfully");
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to activate seller: ' . $e->getMessage());
        }
    }

    /**
     * Get user statistics for admin dashboard.
     */
    public function statistics(): JsonResponse
    {
        $admin = $this->checkAdmin();
        if (!$admin) {
            return $this->forbiddenResponse('Only administrators can access this endpoint');
        }

        try {
            $stats = [
                'total_users' => User::count(),
                'total_sellers' => User::where('role', 'seller')->count(),
                'total_buyers' => User::where('role', 'buyer')->count(),
                'active_users' => User::where('status', 'active')->count(),
                'banned_users' => User::where('status', 'banned')->count(),
                'pending_users' => User::where('status', 'pending')->count(),
                'active_sellers' => User::where('role', 'seller')->where('status', 'active')->count(),
                'pending_sellers' => User::where('role', 'seller')->where('status', 'pending')->count(),
                'total_shops' => Shop::count(),
                'active_shops' => Shop::where('status', 'active')->count(),
                'pending_shops' => Shop::where('status', 'pending')->count(),
            ];

            return $this->successResponse($stats, 'Statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve statistics: ' . $e->getMessage());
        }
    }
}
