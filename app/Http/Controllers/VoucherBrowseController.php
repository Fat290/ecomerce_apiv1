<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherBrowseController extends Controller
{
    /**
     * List vouchers that can be used right now (active and within date range).
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $now = Carbon::now();

            $query = Voucher::query()
                ->where('status', 'active')
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now);

            $query = $this->applyFilters($query, $request)
                ->orderByDesc('updated_at')
                ->with('shop');

            $vouchers = $query->paginate($this->perPage($request));

            return $this->paginatedResponse($vouchers, 'Available vouchers retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve available vouchers: ' . $e->getMessage());
        }
    }

    /**
     * List vouchers that are scheduled to start soon (claim in advance).
     */
    public function claimable(Request $request): JsonResponse
    {
        try {
            $now = Carbon::now();

            $query = Voucher::query()
                ->where('status', 'active')
                ->where('start_date', '>', $now)
                ->where('end_date', '>=', $now);

            $query = $this->applyFilters($query, $request)
                ->orderBy('start_date')
                ->with('shop');

            $vouchers = $query->paginate($this->perPage($request));

            return $this->paginatedResponse($vouchers, 'Claimable vouchers retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve claimable vouchers: ' . $e->getMessage());
        }
    }

    /**
     * List all admin-issued vouchers (both available and claimable).
     * This endpoint is specifically for users to browse admin vouchers.
     */
    public function adminVouchers(Request $request): JsonResponse
    {
        try {
            $now = Carbon::now();

            $query = Voucher::query()
                ->where('creator_type', 'admin')
                ->where('status', 'active')
                ->where('end_date', '>=', $now);

            // Filter by voucher type if provided
            if ($request->filled('voucher_type')) {
                $query->where('voucher_type', $request->input('voucher_type'));
            }

            // Filter by discount type if provided
            if ($request->filled('discount_type')) {
                $query->where('discount_type', $request->input('discount_type'));
            }

            // Filter by availability status
            if ($request->filled('availability')) {
                $availability = $request->input('availability');
                if ($availability === 'available') {
                    // Currently active vouchers
                    $query->where('start_date', '<=', $now);
                } elseif ($availability === 'claimable') {
                    // Future vouchers
                    $query->where('start_date', '>', $now);
                }
            }

            // Order by start date (available first, then claimable)
            $query->orderBy('start_date')
                ->orderByDesc('updated_at');

            $vouchers = $query->paginate($this->perPage($request));

            return $this->paginatedResponse($vouchers, 'Admin vouchers retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve admin vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Apply optional filters from the request query string.
     */
    protected function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->input('voucher_type'));
        }

        if ($request->filled('creator_type')) {
            $query->where('creator_type', $request->input('creator_type'));
        }

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->input('shop_id'));
        }

        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->input('discount_type'));
        }

        if ($request->boolean('admin_only')) {
            $query->where('creator_type', 'admin');
        }

        if ($request->boolean('seller_only')) {
            $query->where('creator_type', 'seller');
        }

        return $query;
    }

    protected function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
