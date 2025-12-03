<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartController extends Controller
{
    /**
     * Get all cart items for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $cartItems = Cart::where('user_id', $user->id)
                ->with(['product.category', 'product.shop'])
                ->latest()
                ->get();

            // Calculate total
            $total = $cartItems->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });

            return $this->successResponse([
                'items' => $cartItems,
                'total' => round($total, 2),
                'item_count' => $cartItems->count(),
            ], 'Cart retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve cart: ' . $e->getMessage());
        }
    }

    /**
     * Add a product to the cart.
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
                'quantity' => ['required', 'integer', 'min:1'],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $product = Product::find($request->product_id);

            // Check if product is available (active or out_of_stock)
            if (!in_array($product->status, ['active', 'out_of_stock'])) {
                return $this->errorResponse('Product is not available', 400);
            }

            // Check if product is in stock
            if ($product->stock < $request->quantity) {
                return $this->errorResponse('Insufficient stock. Available: ' . $product->stock, 400);
            }

            // Check if item already exists in cart
            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($cartItem) {
                // Update quantity
                $newQuantity = $cartItem->quantity + $request->quantity;

                // Check stock again
                if ($product->stock < $newQuantity) {
                    return $this->errorResponse('Insufficient stock. Available: ' . $product->stock, 400);
                }

                $cartItem->update(['quantity' => $newQuantity]);
                $cartItem->load(['product.category', 'product.shop']);

                return $this->successResponse($cartItem, 'Cart item updated successfully');
            } else {
                // Create new cart item
                $cartItem = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                ]);

                $cartItem->load(['product.category', 'product.shop']);

                return $this->createdResponse($cartItem, 'Product added to cart successfully');
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to add product to cart: ' . $e->getMessage());
        }
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $validator = Validator::make($request->all(), [
                'quantity' => ['required', 'integer', 'min:1'],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $cartItem = Cart::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$cartItem) {
                return $this->notFoundResponse('Cart item not found');
            }

            $product = $cartItem->product;

            // Check stock
            if ($product->stock < $request->quantity) {
                return $this->errorResponse('Insufficient stock. Available: ' . $product->stock, 400);
            }

            $cartItem->update(['quantity' => $request->quantity]);
            $cartItem->load(['product.category', 'product.shop']);

            return $this->successResponse($cartItem, 'Cart item updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update cart item: ' . $e->getMessage());
        }
    }

    /**
     * Remove a product from the cart.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $cartItem = Cart::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$cartItem) {
                return $this->notFoundResponse('Cart item not found');
            }

            $cartItem->delete();

            return $this->successResponse(null, 'Product removed from cart successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove product from cart: ' . $e->getMessage());
        }
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            Cart::where('user_id', $user->id)->delete();

            return $this->successResponse(null, 'Cart cleared successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to clear cart: ' . $e->getMessage());
        }
    }
}
