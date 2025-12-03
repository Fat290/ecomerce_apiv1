<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopFollower;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShopFollowerController extends Controller
{
    /**
     * Follow a shop.
     */
    public function follow(Request $request, string $shopId): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $shop = Shop::where('status', 'active')->find($shopId);
            if (!$shop) {
                return $this->notFoundResponse('Shop not found or not active');
            }

            if ($shop->owner_id === $user->id) {
                return $this->errorResponse('You cannot follow your own shop', 400);
            }

            $record = ShopFollower::firstOrCreate([
                'shop_id' => $shop->id,
                'user_id' => $user->id,
            ]);

            if (!$record->wasRecentlyCreated) {
                return $this->successResponse(null, 'You already follow this shop');
            }

            return $this->createdResponse(null, 'Shop followed successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to follow shop: ' . $e->getMessage());
        }
    }

    /**
     * Unfollow a shop.
     */
    public function unfollow(Request $request, string $shopId): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $deleted = ShopFollower::where('shop_id', $shopId)
                ->where('user_id', $user->id)
                ->delete();

            if (!$deleted) {
                return $this->notFoundResponse('You do not follow this shop');
            }

            return $this->successResponse(null, 'Shop unfollowed successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to unfollow shop: ' . $e->getMessage());
        }
    }

    /**
     * List followers for a shop (owner/admin only).
     */
    public function followers(string $shopId): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $shop = Shop::find($shopId);

            if (!$shop) {
                return $this->notFoundResponse('Shop not found');
            }

            if ($user->role !== 'admin' && $shop->owner_id !== $user->id) {
                return $this->forbiddenResponse('You do not have permission to view followers of this shop');
            }

            $followers = ShopFollower::where('shop_id', $shop->id)
                ->with(['user:id,name,email,avatar'])
                ->latest()
                ->paginate(20);

            return $this->paginatedResponse($followers, 'Shop followers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve followers: ' . $e->getMessage());
        }
    }

    /**
     * List shops followed by the current user.
     */
    public function myFollows(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $follows = ShopFollower::where('user_id', $user->id)
                ->with(['shop:id,name,logo,banner,status'])
                ->latest()
                ->paginate(20);

            return $this->paginatedResponse($follows, 'Followed shops retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve followed shops: ' . $e->getMessage());
        }
    }
}
