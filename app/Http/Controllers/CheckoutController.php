<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Voucher;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckoutController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Process checkout and create orders from cart.
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            // Get cart items
            $cartItems = Cart::where('user_id', $user->id)
                ->with(['product.shop'])
                ->get();

            if ($cartItems->isEmpty()) {
                return $this->errorResponse('Cart is empty', 400);
            }

            // Group cart items by shop
            $shopItems = [];
            $shopTotals = [];
            $orderTotal = 0;

            foreach ($cartItems as $item) {
                if (!$item->product || !$item->product->shop) {
                    continue;
                }

                $shopId = $item->product->shop_id;
                if (!isset($shopItems[$shopId])) {
                    $shopItems[$shopId] = [];
                }

                $lineTotal = $item->product->price * $item->quantity;
                $shopItems[$shopId][] = [
                    'product_id' => $item->product_id,
                    'qty' => $item->quantity,
                    'price' => $item->product->price,
                ];

                $shopTotals[$shopId] = ($shopTotals[$shopId] ?? 0) + $lineTotal;
                $orderTotal += $lineTotal;
            }

            if (empty($shopItems)) {
                return $this->errorResponse('No valid products in cart', 400);
            }

            $now = Carbon::now();
            $shippingAddress = $request->input('shipping_address');
            $paymentMethod = $request->input('payment_method');

            // Validate and apply vouchers
            $shippingVoucher = null;
            $productVoucher = null;
            $shopVouchers = [];

            // Shipping voucher (admin only)
            if ($request->filled('shipping_voucher_id')) {
                $shippingVoucher = Voucher::find($request->shipping_voucher_id);
                $validationError = $this->validateVoucher($shippingVoucher, [
                    'creator_type' => 'admin',
                    'voucher_type' => 'shipping',
                    'min_total' => $orderTotal,
                    'now' => $now,
                ]);

                if ($validationError) {
                    return $this->errorResponse($validationError, 400);
                }
            }

            // Admin product voucher
            if ($request->filled('product_voucher_id')) {
                $productVoucher = Voucher::find($request->product_voucher_id);
                $validationError = $this->validateVoucher($productVoucher, [
                    'creator_type' => 'admin',
                    'voucher_type' => 'product',
                    'min_total' => $orderTotal,
                    'now' => $now,
                ]);

                if ($validationError) {
                    return $this->errorResponse($validationError, 400);
                }
            }

            // Shop vouchers
            if ($request->filled('shop_vouchers')) {
                foreach ($request->shop_vouchers as $entry) {
                    $shopId = (int) $entry['shop_id'];
                    $voucher = Voucher::find($entry['voucher_id']);

                    if (!isset($shopTotals[$shopId])) {
                        return $this->errorResponse('Voucher applied to a shop that is not in the cart', 400);
                    }

                    $validationError = $this->validateVoucher($voucher, [
                        'creator_type' => 'seller',
                        'voucher_type' => 'product',
                        'expected_shop_id' => $shopId,
                        'min_total' => $shopTotals[$shopId],
                        'now' => $now,
                    ]);

                    if ($validationError) {
                        return $this->errorResponse($validationError, 400);
                    }

                    $shopVouchers[$shopId] = $voucher;
                }
            }

            // Calculate shipping fee (apply shipping voucher discount if applicable)
            $baseShippingFee = 10.00; // Default shipping fee, can be calculated based on business logic
            $shippingFee = $baseShippingFee;

            if ($shippingVoucher) {
                $shippingDiscount = $this->calculateDiscount($shippingVoucher, $baseShippingFee);
                $shippingFee = max(0, $baseShippingFee - $shippingDiscount);
            }

            // Create orders in a transaction
            DB::beginTransaction();

            try {
                $createdOrders = [];

                foreach ($shopItems as $shopId => $items) {
                    $shopTotal = $shopTotals[$shopId];
                    $shopDiscount = 0;

                    // Apply product voucher (admin) to all shops proportionally
                    if ($productVoucher) {
                        $proportionalTotal = $shopTotal;
                        $proportionalDiscount = $this->calculateDiscount($productVoucher, $proportionalTotal);
                        $shopDiscount += $proportionalDiscount;
                    }

                    // Apply shop-specific voucher
                    if (isset($shopVouchers[$shopId])) {
                        $shopVoucher = $shopVouchers[$shopId];
                        $shopDiscount += $this->calculateDiscount($shopVoucher, $shopTotal);
                    }

                    // Calculate final amount (product total - discounts + shipping fee)
                    $finalAmount = max(0, $shopTotal - $shopDiscount + $shippingFee);

                    // Create order
                    $order = Order::create([
                        'buyer_id' => $user->id,
                        'shop_id' => $shopId,
                        'items' => $items,
                        'total_amount' => round($finalAmount, 2),
                        'shipping_fee' => round($shippingFee, 2),
                        'payment_method' => $paymentMethod,
                        'status' => 'pending',
                        'shipping_address' => $shippingAddress,
                    ]);

                    $order->load(['shop', 'shop.owner', 'buyer']);
                    $createdOrders[] = $order;

                    // Send notification to shop owner
                    if ($order->shop && $order->shop->owner) {
                        $this->notificationService->sendOrderPlaced(
                            $order->shop->owner,
                            $order->id,
                            $user->name,
                            $order->total_amount
                        );
                    }
                }

                // Clear cart after successful order creation
                Cart::where('user_id', $user->id)->delete();

                // Send confirmation notification to buyer
                $orderCount = count($createdOrders);
                $this->notificationService->sendOrderUpdate(
                    $user,
                    $createdOrders[0]->id,
                    'pending',
                    "Your order has been placed successfully. {$orderCount} order(s) created."
                );

                DB::commit();

                return $this->successResponse([
                    'orders' => $createdOrders,
                    'total_orders' => count($createdOrders),
                ], 'Checkout completed successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to process checkout: ' . $e->getMessage());
        }
    }

    /**
     * Validate selected vouchers against the current cart.
     */
    public function applyVouchers(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated.');
            }

            $validator = Validator::make($request->all(), [
                'shipping_voucher_id' => ['nullable', 'exists:vouchers,id'],
                'product_voucher_id' => ['nullable', 'exists:vouchers,id'],
                'shop_vouchers' => ['nullable', 'array'],
                'shop_vouchers.*.shop_id' => ['required_with:shop_vouchers', 'exists:shops,id'],
                'shop_vouchers.*.voucher_id' => ['required_with:shop_vouchers', 'exists:vouchers,id'],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $cartItems = Cart::where('user_id', $user->id)
                ->with(['product.shop'])
                ->get();

            if ($cartItems->isEmpty()) {
                return $this->errorResponse('Cart is empty.', 400);
            }

            $shopTotals = [];
            $orderTotal = 0;

            foreach ($cartItems as $item) {
                if (!$item->product || !$item->product->shop) {
                    continue;
                }

                $lineTotal = $item->product->price * $item->quantity;
                $orderTotal += $lineTotal;
                $shopId = $item->product->shop_id;
                $shopTotals[$shopId] = ($shopTotals[$shopId] ?? 0) + $lineTotal;
            }

            $now = Carbon::now();
            $response = [
                'order_total' => round($orderTotal, 2),
                'shipping_voucher' => null,
                'product_voucher' => null,
                'shop_vouchers' => [],
            ];

            // Shipping voucher (admin only)
            if ($request->filled('shipping_voucher_id')) {
                $shippingVoucher = Voucher::find($request->shipping_voucher_id);
                $validationError = $this->validateVoucher($shippingVoucher, [
                    'creator_type' => 'admin',
                    'voucher_type' => 'shipping',
                    'min_total' => $orderTotal,
                    'now' => $now,
                ]);

                if ($validationError) {
                    return $this->errorResponse($validationError, 400);
                }

                $response['shipping_voucher'] = $shippingVoucher;
            }

            // Admin product voucher
            if ($request->filled('product_voucher_id')) {
                $productVoucher = Voucher::find($request->product_voucher_id);
                $validationError = $this->validateVoucher($productVoucher, [
                    'creator_type' => 'admin',
                    'voucher_type' => 'product',
                    'min_total' => $orderTotal,
                    'now' => $now,
                ]);

                if ($validationError) {
                    return $this->errorResponse($validationError, 400);
                }

                $response['product_voucher'] = $productVoucher;
            }

            // Shop vouchers (seller-specific)
            if ($request->filled('shop_vouchers')) {
                $shopVoucherMap = [];

                foreach ($request->shop_vouchers as $entry) {
                    $shopId = (int) $entry['shop_id'];
                    $voucher = Voucher::find($entry['voucher_id']);

                    if (isset($shopVoucherMap[$shopId])) {
                        return $this->errorResponse('Each shop can only apply one voucher.', 400);
                    }

                    if (!isset($shopTotals[$shopId])) {
                        return $this->errorResponse('Voucher applied to a shop that is not in the cart.', 400);
                    }

                    $validationError = $this->validateVoucher($voucher, [
                        'creator_type' => 'seller',
                        'voucher_type' => 'product',
                        'expected_shop_id' => $shopId,
                        'min_total' => $shopTotals[$shopId],
                        'now' => $now,
                    ]);

                    if ($validationError) {
                        return $this->errorResponse($validationError, 400);
                    }

                    $shopVoucherMap[$shopId] = [
                        'shop_id' => $shopId,
                        'voucher' => $voucher,
                    ];
                }

                $response['shop_vouchers'] = array_values($shopVoucherMap);
            }

            return $this->successResponse($response, 'Vouchers validated successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to apply vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Calculate discount amount based on voucher type.
     */
    private function calculateDiscount(Voucher $voucher, float $amount): float
    {
        if ($voucher->discount_type === 'percent') {
            return ($amount * $voucher->discount_value) / 100;
        } else {
            // amount type - return the discount value directly (capped at the amount)
            return min($voucher->discount_value, $amount);
        }
    }

    /**
     * Validate voucher against business rules.
     */
    private function validateVoucher(?Voucher $voucher, array $options): ?string
    {
        if (!$voucher) {
            return 'Voucher not found.';
        }

        if (!empty($options['creator_type']) && $voucher->creator_type !== $options['creator_type']) {
            return 'Voucher type mismatch.';
        }

        if (!empty($options['voucher_type']) && $voucher->voucher_type !== $options['voucher_type']) {
            return 'Voucher type mismatch.';
        }

        if (!empty($options['expected_shop_id'])) {
            if ((int) $voucher->shop_id !== (int) $options['expected_shop_id']) {
                return 'Voucher does not belong to the specified shop.';
            }
        }

        if ($voucher->status !== 'active') {
            return 'Voucher is not active.';
        }

        $now = $options['now'] ?? Carbon::now();
        if ($voucher->start_date && $now->lt($voucher->start_date)) {
            return 'Voucher is not yet active.';
        }

        if ($voucher->end_date && $now->gt($voucher->end_date)) {
            return 'Voucher has expired.';
        }

        $minTotal = $options['min_total'] ?? 0;
        if ($minTotal < $voucher->min_order_value) {
            return 'Minimum order value not met for voucher ' . $voucher->code;
        }

        return null;
    }
}
