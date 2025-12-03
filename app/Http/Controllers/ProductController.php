<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Wishlist;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
{
    use HandlesImageUploads;
    /**
     * Display a listing of products with search and filters.
     * For sellers: shows only their shop's products
     * For others: shows all active products
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $query = Product::query();

            // If user is a seller, show only their shop's products
            if (in_array($user->role, ['seller', 'admin'])) {
                $shop = Shop::where('owner_id', $user->id)->first();

                if ($shop) {
                    $query->where('shop_id', $shop->id);
                } else {
                    $query->whereRaw('1 = 0'); // Return empty result
                }
            } else {
                // For buyers and public: show only active or out_of_stock products
                $query->whereIn('status', ['active', 'out_of_stock']);
            }

            // Search by name or description
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            // Filter by status (only for sellers/admins)
            if ($request->filled('status') && in_array($user->role, ['seller', 'admin'])) {
                $query->where('status', $request->input('status'));
            }

            // Filter by price range
            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->input('min_price'));
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->input('max_price'));
            }

            // Filter by rating
            if ($request->filled('min_rating')) {
                $query->where('rating', '>=', $request->input('min_rating'));
            }

            // Filter by stock availability
            if ($request->filled('in_stock')) {
                if ($request->input('in_stock') == 'true' || $request->input('in_stock') == 1) {
                    $query->where('stock', '>', 0);
                }
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            // Validate sort_by to prevent SQL injection
            $allowedSortFields = ['created_at', 'price', 'rating', 'sold_count', 'name'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }

            // Validate sort_order
            $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

            $query->orderBy($sortBy, $sortOrder);

            // Load relationships
            $query->with(['category.variants', 'shop', 'variantOptions.variant']);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $perPage = min(max(1, (int)$perPage), 100); // Limit between 1 and 100

            $products = $query->paginate($perPage);

            return $this->paginatedResponse($products, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve products: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created product.
     * Only active sellers can create products.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            // Check if user is a seller or admin
            if (!in_array($user->role, ['seller', 'admin'])) {
                return $this->forbiddenResponse('Only sellers can create products');
            }

            // Check if seller's account is active
            if ($user->status !== 'active') {
                return $this->forbiddenResponse('Your account must be active to create products. Current status: ' . $user->status);
            }

            // Get seller's shop
            $shop = Shop::where('owner_id', $user->id)->first();

            if (!$shop) {
                return $this->errorResponse('You must create a shop first before adding products', 404);
            }

            // Check if shop is active
            if ($shop->status !== 'active') {
                return $this->errorResponse('Your shop must be active to create products. Current shop status: ' . $shop->status, 403);
            }

            // Upload images to Cloudinary if provided
            $imageUrls = [];
            if ($request->hasFile('images')) {
                $imageUrls = $this->uploadImages($request->file('images'), 'products');
            } elseif ($request->filled('images') && is_array($request->images)) {
                // Handle case where images might already be URLs
                $imageUrls = array_filter($request->images, function ($img) {
                    return filter_var($img, FILTER_VALIDATE_URL) || ($img instanceof \Illuminate\Http\UploadedFile);
                });
                // Upload any file objects
                $fileImages = array_filter($imageUrls, fn($img) => $img instanceof \Illuminate\Http\UploadedFile);
                if (!empty($fileImages)) {
                    $uploadedUrls = $this->uploadImages($fileImages, 'products');
                    $imageUrls = array_merge(
                        array_filter($imageUrls, fn($img) => !($img instanceof \Illuminate\Http\UploadedFile)),
                        $uploadedUrls
                    );
                }
            }

            // Create product
            $product = Product::create([
                'shop_id' => $shop->id, // Automatically set from seller's shop
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'images' => !empty($imageUrls) ? $imageUrls : null,
                'price' => $request->price,
                'stock' => $request->stock,
                'status' => $request->status ?? 'draft',
                'rating' => $request->rating ?? 0,
                'sold_count' => $request->sold_count ?? 0,
            ]);

            $product->load('category');
            $this->syncProductVariantOptions($product, $request->input('variants', []));

            // Load relationships for response
            $product->load(['category.variants', 'shop', 'variantOptions.variant']);

            return $this->createdResponse($product, 'Product created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Public catalog listing (no authentication required).
     */
    public function publicIndex(Request $request): JsonResponse
    {
        try {
            $query = Product::whereIn('status', ['active', 'out_of_stock']);

            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->input('shop_id'));
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->input('min_price'));
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->input('max_price'));
            }

            if ($request->filled('min_rating')) {
                $query->where('rating', '>=', $request->input('min_rating'));
            }

            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
            $allowedSortFields = ['created_at', 'price', 'rating', 'sold_count', 'name'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }

            $query->orderBy($sortBy, $sortOrder)
                ->with(['category.variants', 'shop', 'variantOptions.variant']);

            $perPage = min(max(1, (int) $request->input('per_page', 20)), 100);
            $products = $query->paginate($perPage);

            return $this->paginatedResponse($products, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve products: ' . $e->getMessage());
        }
    }

    /**
     * Public search endpoint (keyword-focused).
     */
    public function publicSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'], // backwards compatibility
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $term = $request->input('q');
            $perPage = (int) ($request->input('per_page', $request->input('limit', 20)));
            $perPage = min(max($perPage, 1), 100);

            $products = Product::whereIn('status', ['active', 'out_of_stock'])
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                })
                ->with(['category.variants', 'shop', 'variantOptions.variant'])
                ->orderByDesc('sold_count')
                ->orderByDesc('rating')
                ->paginate($perPage);

            return $this->paginatedResponse($products, 'Search results retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to search products: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product.
     * Public endpoint - no authentication required.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $product = Product::with(['category.variants', 'shop', 'reviews', 'variantOptions.variant'])
                ->find($id);

            if (!$product) {
                return $this->notFoundResponse('Product not found');
            }

            // Only show active or out_of_stock products to public (unless authenticated seller viewing their own)
            try {
                $user = JWTAuth::parseToken()->authenticate();
                $isOwner = $user && in_array($user->role, ['seller', 'admin'])
                    && $product->shop->owner_id === $user->id;

                if (!$isOwner && !in_array($product->status, ['active', 'out_of_stock'])) {
                    return $this->notFoundResponse('Product not found');
                }
            } catch (\Exception $e) {
                // Not authenticated - only show active or out_of_stock products
                if (!in_array($product->status, ['active', 'out_of_stock'])) {
                    return $this->notFoundResponse('Product not found');
                }
            }

            return $this->successResponse($product, 'Product retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve product: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified product.
     * Only the product's shop owner (active seller) can update.
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $product = Product::with('shop')->find($id);

            if (!$product) {
                return $this->notFoundResponse('Product not found');
            }

            // Check if user is the shop owner or admin
            if ($user->role !== 'admin' && $product->shop->owner_id !== $user->id) {
                return $this->forbiddenResponse('You do not have permission to update this product');
            }

            // Check if seller's account is active (unless admin)
            if ($user->role !== 'admin' && $user->status !== 'active') {
                return $this->forbiddenResponse('Your account must be active to update products. Current status: ' . $user->status);
            }

            // Check if shop is active (unless admin)
            if ($user->role !== 'admin' && $product->shop->status !== 'active') {
                return $this->errorResponse('Your shop must be active to update products. Current shop status: ' . $product->shop->status, 403);
            }

            // Handle image updates
            $updateData = $request->only([
                'category_id',
                'name',
                'description',
                'price',
                'stock',
                'status',
                'rating',
                'sold_count',
            ]);

            // Upload new images if provided
            if ($request->hasFile('images')) {
                $newImageUrls = $this->uploadImages($request->file('images'), 'products');
                // Merge with existing images if they exist
                $existingImages = $product->images ?? [];
                $updateData['images'] = array_merge($existingImages, $newImageUrls);
            } elseif ($request->filled('images') && is_array($request->images)) {
                // Handle mixed URLs and files
                $imageUrls = [];
                foreach ($request->images as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile) {
                        $url = $this->uploadImage($img, 'products');
                        if ($url) $imageUrls[] = $url;
                    } elseif (filter_var($img, FILTER_VALIDATE_URL)) {
                        $imageUrls[] = $img;
                    }
                }
                if (!empty($imageUrls)) {
                    $updateData['images'] = $imageUrls;
                }
            }

            $product->update($updateData);

            $product->refresh()->load('category');

            if ($request->has('variants')) {
                $this->syncProductVariantOptions($product, $request->input('variants', []));
            }

            // Load relationships for response
            $product->load(['category.variants', 'shop', 'variantOptions.variant']);

            return $this->successResponse($product, 'Product updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product.
     * Only the product's shop owner (active seller) can delete.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $product = Product::with('shop')->find($id);

            if (!$product) {
                return $this->notFoundResponse('Product not found');
            }

            // Check if user is the shop owner or admin
            if ($user->role !== 'admin' && $product->shop->owner_id !== $user->id) {
                return $this->forbiddenResponse('You do not have permission to delete this product');
            }

            // Check if seller's account is active (unless admin)
            if ($user->role !== 'admin' && $user->status !== 'active') {
                return $this->forbiddenResponse('Your account must be active to delete products. Current status: ' . $user->status);
            }

            // Check if shop is active (unless admin)
            if ($user->role !== 'admin' && $product->shop->status !== 'active') {
                return $this->errorResponse('Your shop must be active to delete products. Current shop status: ' . $product->shop->status, 403);
            }

            $product->delete();

            return $this->successResponse(null, 'Product deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Return variant definitions for the provided category to assist product creation.
     */
    public function categoryVariants(Category $category): JsonResponse
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

    /**
     * Recommended products for a user (if logged in) or generic fallback.
     */
    public function recommended(Request $request): JsonResponse
    {
        try {
            $user = null;

            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (\Exception $e) {
                $user = null;
            }

            // Fallback: no auth -> show top sellers + most favorited
            if (!$user) {
                $products = Product::where('status', 'active')
                    ->withCount('wishlists')
                    ->orderByDesc('sold_count')
                    ->orderByDesc('wishlists_count')
                    ->limit(20)
                    ->get();

                return $this->successResponse($products, 'Recommended products (generic).');
            }

            // Authenticated user: find related categories/brands from past orders or wishlists
            $categoryIds = [];
            $purchasedProductIds = [];

            $orders = Order::where('buyer_id', $user->id)->get();
            $purchasedProductIds = $orders->flatMap(function ($order) {
                if (!is_array($order->items)) {
                    return [];
                }
                return collect($order->items)
                    ->pluck('product_id')
                    ->filter();
            })->unique();

            if ($purchasedProductIds->isNotEmpty()) {
                $categoryIds = Product::whereIn('id', $purchasedProductIds)
                    ->whereNotNull('category_id')
                    ->pluck('category_id');
            } else {
                // fall back to wishlist interests if no purchases
                $wishlistProductIds = Wishlist::where('user_id', $user->id)->pluck('product_id');
                if ($wishlistProductIds->isNotEmpty()) {
                    $categoryIds = Product::whereIn('id', $wishlistProductIds)
                        ->whereNotNull('category_id')
                        ->pluck('category_id');
                }
            }

            $query = Product::where('status', 'active')
                ->withCount('wishlists')
                ->orderByDesc('sold_count');

            if (!empty($categoryIds)) {
                $query->whereIn('category_id', $categoryIds);
            }

            $products = $query->limit(20)->get();

            if ($products->isEmpty()) {
                // fallback to generic
                $products = Product::where('status', 'active')
                    ->withCount('wishlists')
                    ->orderByDesc('sold_count')
                    ->orderByDesc('wishlists_count')
                    ->limit(20)
                    ->get();
            }

            return $this->successResponse($products, 'Recommended products retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to fetch recommended products: ' . $e->getMessage());
        }
    }

    /**
     * Persist per-product variant overrides provided by the seller.
     */
    protected function syncProductVariantOptions(Product $product, array $variants): void
    {
        $category = $product->category ?? $product->category()->first();

        if (!$category) {
            $product->variantOptions()->delete();
            return;
        }

        $allowedVariants = collect($category->aggregatedVariants())->keyBy('id');

        $normalized = [];
        foreach ($variants as $entry) {
            $variantId = (int) ($entry['variant_id'] ?? 0);
            if (!$variantId || !$allowedVariants->has($variantId)) {
                continue;
            }

            $options = $entry['options'] ?? [];
            if (!is_array($options)) {
                continue;
            }

            $options = array_values(array_filter(array_map(function ($value) {
                return is_string($value) ? trim($value) : '';
            }, $options), fn($value) => $value !== ''));

            if (empty($options)) {
                continue;
            }

            $normalized[$variantId] = [
                'options' => $options,
                'is_required' => array_key_exists('is_required', $entry)
                    ? (bool) $entry['is_required']
                    : (bool) ($allowedVariants[$variantId]->is_required ?? true),
            ];
        }

        if (empty($normalized)) {
            $product->variantOptions()->delete();
            return;
        }

        $product->variantOptions()
            ->whereNotIn('variant_id', array_keys($normalized))
            ->delete();

        foreach ($normalized as $variantId => $data) {
            $product->variantOptions()->updateOrCreate(
                ['variant_id' => $variantId],
                [
                    'options' => $data['options'],
                    'is_required' => $data['is_required'],
                ]
            );
        }
    }
}
