<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class VoucherController extends Controller
{
    /**
     * Ensure the authenticated user is an admin.
     */
    private function checkAdmin()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user && $user->role === 'admin') {
                return $user;
            }
        } catch (\Exception $e) {
            // fall through
        }

        return null;
    }

    /**
     * List all vouchers created by admin.
     */
    public function index(Request $request): JsonResponse
    {
        if (!$this->checkAdmin()) {
            return $this->forbiddenResponse('Only administrators can access this endpoint.');
        }

        try {
            $query = Voucher::where('creator_type', 'admin');

            if ($request->filled('voucher_type')) {
                $query->where('voucher_type', $request->voucher_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $vouchers = $query->latest()->get();

            return $this->successResponse($vouchers, 'Vouchers retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Create a new admin voucher (shipping or product).
     */
    public function store(Request $request): JsonResponse
    {
        if (!$this->checkAdmin()) {
            return $this->forbiddenResponse('Only administrators can create vouchers.');
        }

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:50', 'unique:vouchers,code'],
            'voucher_type' => ['required', 'in:shipping,product'],
            'discount_type' => ['required', 'in:percent,amount'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['nullable', 'in:active,expired,disabled'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $voucher = Voucher::create([
                'code' => strtoupper($request->code),
                'voucher_type' => $request->voucher_type,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'min_order_value' => $request->min_order_value ?? 0,
                'shop_id' => null,
                'creator_type' => 'admin',
                'start_date' => Carbon::parse($request->start_date),
                'end_date' => Carbon::parse($request->end_date),
                'status' => $request->status ?? 'active',
            ]);

            return $this->createdResponse($voucher, 'Voucher created successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create voucher: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing admin voucher.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if (!$this->checkAdmin()) {
            return $this->forbiddenResponse('Only administrators can update vouchers.');
        }

        $validator = Validator::make($request->all(), [
            'code' => ['sometimes', 'string', 'max:50', 'unique:vouchers,code,' . $id],
            'voucher_type' => ['sometimes', 'in:shipping,product'],
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

        try {
            $voucher = Voucher::where('creator_type', 'admin')->find($id);

            if (!$voucher) {
                return $this->notFoundResponse('Voucher not found.');
            }

            $voucher->fill([
                'code' => $request->has('code') ? strtoupper($request->code) : $voucher->code,
                'voucher_type' => $request->voucher_type ?? $voucher->voucher_type,
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
     * Delete an admin voucher.
     */
    public function destroy(string $id): JsonResponse
    {
        if (!$this->checkAdmin()) {
            return $this->forbiddenResponse('Only administrators can delete vouchers.');
        }

        try {
            $voucher = Voucher::where('creator_type', 'admin')->find($id);

            if (!$voucher) {
                return $this->notFoundResponse('Voucher not found.');
            }

            $voucher->delete();

            return $this->noContentResponse();
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete voucher: ' . $e->getMessage());
        }
    }
}
