<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use HandlesImageUploads;

    /**
     * List categories with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Category::with(['parent', 'variants'])->orderBy('name');

            if ($request->filled('search')) {
                $term = $request->input('search');
                $query->where('name', 'like', "%{$term}%");
            }

            if ($request->filled('parent_id')) {
                $query->where('parent_id', $request->input('parent_id'));
            }

            if ($request->boolean('only_parents')) {
                $query->whereNull('parent_id');
            }

            if ($request->boolean('all')) {
                $categories = $query->get();

                return $this->successResponse($categories, 'Categories retrieved successfully.');
            }

            $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
            $categories = $query->paginate($perPage);

            return $this->paginatedResponse($categories, 'Categories retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to load categories: ' . $e->getMessage());
        }
    }

    /**
     * Create a new category with optional variants.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->only(['name', 'parent_id']);

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'), 'categories');
            }

            $category = Category::create($data);

            $this->syncVariants($category, $request->input('variants', []));

            $category->load(['parent', 'variants']);

            return $this->createdResponse($category, 'Category created successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create category: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing category and its variants.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $data = $request->only(['name', 'parent_id']);

            if ($request->hasFile('image')) {
                if ($category->image) {
                    $this->deleteImage($category->image);
                }

                $data['image'] = $this->uploadImage($request->file('image'), 'categories');
            }

            $category->update($data);

            if ($request->has('variants')) {
                $this->syncVariants($category, $request->input('variants', []));
            }

            $category->load(['parent', 'variants']);

            return $this->successResponse($category, 'Category updated successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            if ($category->image) {
                $this->deleteImage($category->image);
            }

            $category->delete();

            return $this->successResponse(null, 'Category deleted successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete category: ' . $e->getMessage());
        }
    }

    /**
     * Sync variants for the given category.
     */
    protected function syncVariants(Category $category, array $variants): void
    {
        $category->variants()->delete();

        $position = 0;
        foreach ($variants as $variant) {
            $name = $variant['name'] ?? null;
            if (!$name) {
                continue;
            }

            $options = $this->normalizeOptions($variant['options'] ?? []);
            $category->variants()->create([
                'name' => $name,
                'options' => $options,
                'is_required' => (bool) ($variant['is_required'] ?? false),
                'position' => $variant['position'] ?? $position,
            ]);

            $position++;
        }
    }

    /**
     * Normalize variant options input.
     */
    protected function normalizeOptions($options): array
    {
        if (is_string($options)) {
            $options = array_map('trim', explode(',', $options));
        }

        if (is_array($options)) {
            return array_values(array_filter($options, fn($value) => $value !== null && $value !== ''));
        }

        return [];
    }
}
