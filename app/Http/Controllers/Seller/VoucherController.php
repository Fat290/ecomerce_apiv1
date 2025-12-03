<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class VoucherController extends Controller
{
    /**
     * Ensure the authenticated user is a seller.
     */
    private function getSeller()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user && $user->role === 'seller') {
                return $user;
            }
        } catch (\Exception $e) {
            // ignore
        }

        return null;
    }

    /**
     * List vouchers for the seller's shops.
     */
    public function index(): JsonResponse
    {
        $seller = $this->getSeller();
        if (!$seller) {
            return $this->forbiddenResponse('Only sellers can access this endpoint.');
        }

        try {
            $shopIds = Shop::where('owner_id', $seller->id)->pluck('id');

            $vouchers = Voucher::whereIn('shop_id', $shopIds)
                ->where('creator_type', 'seller')
                ->with('shop')
                ->latest()
                ->get();

            return $this->successResponse($vouchers, 'Vouchers retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Create a new voucher for a seller shop.
     */
    public function store(Request $request): JsonResponse
    {
        $seller = $this->getSeller();
        if (!$seller) {
            return $this->forbiddenResponse('Only sellers can create vouchers.');
        }

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50', 'unique:vouchers,code'],
            'discount_type' => ['required', 'in:percent,amount'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'shop_id' => ['required', 'exists:shops,id'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $shop = Shop::where('id', $request->shop_id)
            ->where('owner_id', $seller->id)
            ->first();

        if (!$shop) {
            return $this->forbiddenResponse('You can only create vouchers for your own shops.');
        }

        try {
            $voucher = Voucher::create([
                'code' => strtoupper($request->code),
                'voucher_type' => 'product',
                'creator_type' => 'seller',
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'min_order_value' => $request->min_order_value ?? 0,
                'shop_id' => $shop->id,
                'start_date' => Carbon::parse($request->start_date),
                'end_date' => Carbon::parse($request->end_date),
                'status' => 'active',
            ]);

            return $this->createdResponse($voucher, 'Voucher created successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create voucher: ' . $e->getMessage());
        }
    }

    /**
     * Update a seller voucher.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $seller = $this->getSeller();
        if (!$seller) {
            return $this->forbiddenResponse('Only sellers can update vouchers.');
        }

        $validator = Validator::make($request->all(), [
            'code' => ['sometimes', 'string', 'max:50', 'unique:vouchers,code,' . $id],
            'discount_type' => ['sometimes', 'in:percent,amount'],
            'discount_value' => ['sometimes', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'status' => ['sometimes', 'in:active,expired,disabled'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $voucher = Voucher::where('id', $id)
            ->where('creator_type', 'seller')
            ->with('shop')
            ->first();

        if (!$voucher || !$voucher->shop || $voucher->shop->owner_id !== $seller->id) {
            return $this->forbiddenResponse('You can only update vouchers for your own shops.');
        }

        try {
            $voucher->fill([
                'code' => $request->has('code') ? strtoupper($request->code) : $voucher->code,
                'discount_type' => $request->discount_type ?? $voucher->discount_type,
                'discount_value' => $request->discount_value ?? $voucher->discount_value,
                'min_order_value' => $request->min_order_value ?? $voucher->min_order_value,
                'start_date' => $request->has('start_date') ? Carbon::parse($request->start_date) : $voucher->start_date,
                'end_date' => $request->has('end_date') ? Carbon::parse($request->end_date) : $voucher->end_date,
                'status' => $request->status ?? $voucher->status,
            ]);

            $voucher->save();

            return $this->successResponse($voucher->fresh(), 'Voucher updated successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update voucher: ' . $e->getMessage());
        }
    }

    /**
     * Delete a seller voucher.
     */
    public function destroy(string $id): JsonResponse
    {
        $seller = $this->getSeller();
        if (!$seller) {
            return $this->forbiddenResponse('Only sellers can delete vouchers.');
        }

        $voucher = Voucher::where('id', $id)
            ->where('creator_type', 'seller')
            ->with('shop')
            ->first();

        if (!$voucher || $voucher->shop->owner_id !== $seller->id) {
            return $this->forbiddenResponse('You can only delete vouchers for your own shops.');
        }

        $voucher->delete();

        return $this->noContentResponse();
    }
}
