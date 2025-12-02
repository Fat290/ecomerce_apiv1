<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    /**
     * Public endpoint: fetch banners for the app.
     *
     * Returns only active banners, ordered by newest first.
     */
    public function index(): JsonResponse
    {
        try {
            $banners = Banner::where('is_active', true)
                ->orderByDesc('created_at')
                ->get();

            return $this->successResponse($banners, 'Banners retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve banners: ' . $e->getMessage());
        }
    }
}
