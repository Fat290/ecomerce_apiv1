<?php

namespace App\Http\Controllers;

use App\Models\BusinessType;
use Illuminate\Http\JsonResponse;

class BusinessTypeController extends Controller
{
    /**
     * List the available business types.
     */
    public function index(): JsonResponse
    {
        try {
            $types = BusinessType::where('is_active', true)
                ->orderBy('name')
                ->get();

            return $this->successResponse($types, 'Business types retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to load business types: ' . $e->getMessage());
        }
    }
}
