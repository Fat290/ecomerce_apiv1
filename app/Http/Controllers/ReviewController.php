<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\ReplyReviewRequest;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a product.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('product_id');

            if (!$productId) {
                return $this->errorResponse('Product ID is required', 400);
            }

            $reviews = Review::where('product_id', $productId)
                ->with(['buyer:id,name,avatar', 'product:id,name'])
                ->latest()
                ->paginate(15);

            return $this->paginatedResponse($reviews, 'Reviews retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve reviews: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific review.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $review = Review::with(['buyer:id,name,avatar', 'product:id,name,images'])
                ->find($id);

            if (!$review) {
                return $this->notFoundResponse('Review not found');
            }

            return $this->successResponse($review, 'Review retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve review: ' . $e->getMessage());
        }
    }

    /**
     * Create a new review.
     * Only buyers who have purchased the product can review.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            // Only buyers can create reviews
            if ($user->role !== 'buyer' && $user->role !== 'admin') {
                return $this->forbiddenResponse('Only buyers can create reviews');
            }

            $product = Product::find($request->product_id);

            if (!$product) {
                return $this->notFoundResponse('Product not found');
            }

            // Check if product is available
            if (!in_array($product->status, ['active', 'out_of_stock'])) {
                return $this->errorResponse('Cannot review this product', 400);
            }

            // Check if user already reviewed this product
            $existingReview = Review::where('product_id', $request->product_id)
                ->where('buyer_id', $user->id)
                ->first();

            if ($existingReview) {
                return $this->errorResponse('You have already reviewed this product', 409);
            }

            // Ensure the user purchased this product before reviewing
            $hasPurchased = Order::where('buyer_id', $user->id)
                ->whereIn('status', ['confirmed', 'shipping', 'completed'])
                ->where(function ($query) use ($product) {
                    $query->whereRaw(
                        "JSON_SEARCH(items, 'one', ?, NULL, '$[*].product_id') IS NOT NULL",
                        [$product->id]
                    );
                })
                ->exists();

            if (!$hasPurchased) {
                return $this->forbiddenResponse('You can only review products you have purchased');
            }

            // Create review
            $review = Review::create([
                'product_id' => $request->product_id,
                'buyer_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Update product rating
            $this->updateProductRating($product);

            $review->load(['buyer:id,name,avatar', 'product:id,name']);

            return $this->createdResponse($review, 'Review created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create review: ' . $e->getMessage());
        }
    }

    /**
     * Update a review.
     * Only the review owner can update.
     */
    public function update(UpdateReviewRequest $request, string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $review = Review::with('product')->find($id);

            if (!$review) {
                return $this->notFoundResponse('Review not found');
            }

            // Check if user owns the review or is admin
            if ($review->buyer_id !== $user->id && $user->role !== 'admin') {
                return $this->forbiddenResponse('You do not have permission to update this review');
            }

            $updateData = $request->only(['rating', 'comment']);

            $review->update($updateData);

            // Update product rating
            $this->updateProductRating($review->product);

            $review->load(['buyer:id,name,avatar', 'product:id,name']);

            return $this->successResponse($review, 'Review updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update review: ' . $e->getMessage());
        }
    }

    /**
     * Delete a review.
     * Only the review owner or admin can delete.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $review = Review::with('product')->find($id);

            if (!$review) {
                return $this->notFoundResponse('Review not found');
            }

            // Check if user owns the review or is admin
            if ($review->buyer_id !== $user->id && $user->role !== 'admin') {
                return $this->forbiddenResponse('You do not have permission to delete this review');
            }

            $product = $review->product;
            $review->delete();

            // Update product rating
            $this->updateProductRating($product);

            return $this->successResponse(null, 'Review deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete review: ' . $e->getMessage());
        }
    }

    /**
     * Shop owner replies to a review.
     */
    public function reply(ReplyReviewRequest $request, string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $review = Review::with('product.shop')->find($id);

            if (!$review) {
                return $this->notFoundResponse('Review not found');
            }

            // Check if user is the shop owner or admin
            $shop = $review->product->shop;
            if ($shop->owner_id !== $user->id && $user->role !== 'admin') {
                return $this->forbiddenResponse('Only the shop owner can reply to reviews');
            }

            // Check if reply already exists
            if ($review->reply) {
                return $this->errorResponse('A reply already exists for this review', 409);
            }

            $review->update(['reply' => $request->reply]);
            $review->load(['buyer:id,name,avatar', 'product:id,name']);

            return $this->successResponse($review, 'Reply added successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to add reply: ' . $e->getMessage());
        }
    }

    /**
     * Update shop owner's reply to a review.
     */
    public function updateReply(ReplyReviewRequest $request, string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $review = Review::with('product.shop')->find($id);

            if (!$review) {
                return $this->notFoundResponse('Review not found');
            }

            // Check if user is the shop owner or admin
            $shop = $review->product->shop;
            if ($shop->owner_id !== $user->id && $user->role !== 'admin') {
                return $this->forbiddenResponse('Only the shop owner can update replies');
            }

            if (!$review->reply) {
                return $this->errorResponse('No reply exists to update', 400);
            }

            $review->update(['reply' => $request->reply]);
            $review->load(['buyer:id,name,avatar', 'product:id,name']);

            return $this->successResponse($review, 'Reply updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update reply: ' . $e->getMessage());
        }
    }

    /**
     * Delete shop owner's reply to a review.
     */
    public function deleteReply(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $review = Review::with('product.shop')->find($id);

            if (!$review) {
                return $this->notFoundResponse('Review not found');
            }

            // Check if user is the shop owner or admin
            $shop = $review->product->shop;
            if ($shop->owner_id !== $user->id && $user->role !== 'admin') {
                return $this->forbiddenResponse('Only the shop owner can delete replies');
            }

            if (!$review->reply) {
                return $this->errorResponse('No reply exists to delete', 400);
            }

            $review->update(['reply' => null]);
            $review->load(['buyer:id,name,avatar', 'product:id,name']);

            return $this->successResponse($review, 'Reply deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete reply: ' . $e->getMessage());
        }
    }

    /**
     * Get reviews for products in a shop.
     * Only shop owner can access this.
     */
    public function shopReviews(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $shop = Shop::where('owner_id', $user->id)->first();

            if (!$shop) {
                return $this->notFoundResponse('Shop not found');
            }

            $reviews = Review::whereHas('product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
                ->with(['buyer:id,name,avatar', 'product:id,name,images'])
                ->latest()
                ->paginate(15);

            return $this->paginatedResponse($reviews, 'Shop reviews retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve shop reviews: ' . $e->getMessage());
        }
    }

    /**
     * Update product rating based on reviews.
     */
    private function updateProductRating(Product $product): void
    {
        $averageRating = Review::where('product_id', $product->id)
            ->avg('rating');

        $product->update(['rating' => round($averageRating ?? 0, 2)]);
    }
}
