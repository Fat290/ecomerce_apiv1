<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Return the category tree with representative images and configured variants.
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Category::with([
                'variants',
                'children.variants',
                'children.children.variants',
            ])
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();

            return $this->successResponse($categories, 'Categories retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to load categories: ' . $e->getMessage());
        }
    }

    /**
     * Return the variants required for the supplied category, including inheritance from parents.
     */
    public function variants(Category $category): JsonResponse
    {
        try {
            return $this->successResponse([
                'category' => $category->load('parent'),
                'variants' => $category->aggregatedVariants(),
            ], 'Category variants retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to load category variants: ' . $e->getMessage());
        }
    }
}
