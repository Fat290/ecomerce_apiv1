<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class WishlistController extends Controller
{
    /**
     * Get all wishlist items for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $wishlistItems = Wishlist::where('user_id', $user->id)
                ->with(['product.category', 'product.brand', 'product.shop'])
                ->latest()
                ->paginate(15);

            return $this->paginatedResponse($wishlistItems, 'Wishlist retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve wishlist: ' . $e->getMessage());
        }
    }

    /**
     * Add a product to the wishlist.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $validator = Validator::make($request->all(), [
                'product_id' => ['required', 'exists:products,id'],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $product = Product::find($request->product_id);

            // Check if product is available (active or out_of_stock)
            if (!in_array($product->status, ['active', 'out_of_stock'])) {
                return $this->errorResponse('Product is not available', 400);
            }

            // Check if item already exists in wishlist
            $wishlistItem = Wishlist::where('user_id', $user->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($wishlistItem) {
                return $this->errorResponse('Product is already in your wishlist', 409);
            }

            // Create new wishlist item
            $wishlistItem = Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
            ]);

            $wishlistItem->load(['product.category', 'product.brand', 'product.shop']);

            return $this->createdResponse($wishlistItem, 'Product added to wishlist successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to add product to wishlist: ' . $e->getMessage());
        }
    }

    /**
     * Remove a product from the wishlist.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            // $id can be either wishlist item id or product_id
            $wishlistItem = Wishlist::where('user_id', $user->id)
                ->where(function ($query) use ($id) {
                    $query->where('id', $id)
                        ->orWhere('product_id', $id);
                })
                ->first();

            if (!$wishlistItem) {
                return $this->notFoundResponse('Wishlist item not found');
            }

            $wishlistItem->delete();

            return $this->successResponse(null, 'Product removed from wishlist successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove product from wishlist: ' . $e->getMessage());
        }
    }

    /**
     * Check if a product is in the wishlist.
     */
    public function check(Request $request, string $productId): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $isInWishlist = Wishlist::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            return $this->successResponse([
                'is_in_wishlist' => $isInWishlist,
            ], 'Wishlist status retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to check wishlist status: ' . $e->getMessage());
        }
    }
}
