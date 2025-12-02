<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckoutController extends Controller
{
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
