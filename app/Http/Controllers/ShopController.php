<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\StoreShopRequest;
use App\Http\Requests\Shop\UpdateShopRequest;
use App\Models\Shop;
use App\Traits\HandlesImageUploads;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShopController extends Controller
{
    use HandlesImageUploads;
    /**
     * Display a listing of shops.
     * For sellers: shows their own shop
     * For others: shows all active shops
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            // Admins: see all shops (including pending)
            if ($user->role === 'admin') {
                $query = Shop::with(['owner', 'businessType'])
                    ->latest()
                    ->when($request->filled('status'), function ($q) use ($request) {
                        $q->where('status', $request->input('status'));
                    })
                    ->when($request->filled('search'), function ($q) use ($request) {
                        $term = $request->input('search');
                        $q->where(function ($sub) use ($term) {
                            $sub->where('name', 'like', "%{$term}%")
                                ->orWhereHas('owner', function ($ownerQuery) use ($term) {
                                    $ownerQuery->where('name', 'like', "%{$term}%")
                                        ->orWhere('email', 'like', "%{$term}%");
                                });
                        });
                    });

                $shops = $query->paginate(15);

                $message = $request->filled('status')
                    ? 'Shops retrieved successfully for status ' . $request->input('status')
                    : 'All shops retrieved successfully';

                $totals = Shop::selectRaw('status, COUNT(*) as aggregate')
                    ->groupBy('status')
                    ->pluck('aggregate', 'status')
                    ->toArray();

                return $this->paginatedResponse($shops, $message, [
                    'meta' => [
                        'totals' => [
                            'pending' => $totals['pending'] ?? 0,
                            'active' => $totals['active'] ?? 0,
                            'banned' => $totals['banned'] ?? 0,
                        ],
                    ],
                ]);
            }

            // Sellers: show only their shop
            if ($user->role === 'seller') {
                $shop = Shop::where('owner_id', $user->id)
                    ->with(['owner', 'products', 'businessType'])
                    ->first();

                if ($shop) {
                    return $this->successResponse($shop, 'Shop retrieved successfully');
                } else {
                    return $this->notFoundResponse('You do not have a shop yet');
                }
            } else {
                // Buyers/others: show all active shops
                $shops = Shop::where('status', 'active')
                    ->with(['owner', 'businessType'])
                    ->latest()
                    ->when($request->filled('search'), function ($q) use ($request) {
                        $term = $request->input('search');
                        $q->where(function ($sub) use ($term) {
                            $sub->where('name', 'like', "%{$term}%")
                                ->orWhereHas('owner', function ($ownerQuery) use ($term) {
                                    $ownerQuery->where('name', 'like', "%{$term}%")
                                        ->orWhere('email', 'like', "%{$term}%");
                                });
                        });
                    })
                    ->paginate(15);

                return $this->paginatedResponse($shops, 'Shops retrieved successfully');
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve shops: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified shop.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $shop = Shop::with(['owner', 'products', 'businessType'])
                ->find($id);

            if (!$shop) {
                return $this->notFoundResponse('Shop not found');
            }

            return $this->successResponse($shop, 'Shop retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve shop: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created shop in storage.
     * Only authenticated (registered) users can create shops.
     */
    public function store(StoreShopRequest $request): JsonResponse
    {
        try {
            // Get authenticated user
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            // Check if user already has a shop
            $existingShop = Shop::where('owner_id', $user->id)->first();
            if ($existingShop) {
                return $this->errorResponse('You already have a shop', 409);
            }

            // Upload logo to Cloudinary if provided
            $logoUrl = null;
            if ($request->hasFile('logo')) {
                $logoUrl = $this->uploadImage($request->file('logo'), 'shops');
            } elseif ($request->filled('logo') && filter_var($request->logo, FILTER_VALIDATE_URL)) {
                $logoUrl = $request->logo;
            }

            // Upload banner if provided
            $bannerUrl = null;
            if ($request->hasFile('banner')) {
                $bannerUrl = $this->uploadImage($request->file('banner'), 'shops');
            } elseif ($request->filled('banner') && filter_var($request->banner, FILTER_VALIDATE_URL)) {
                $bannerUrl = $request->banner;
            }

            // Create shop with authenticated user as owner
            $shop = Shop::create([
                'owner_id' => $user->id, // Automatically set from authenticated user
                'name' => $request->name,
                'logo' => $logoUrl,
                'banner' => $bannerUrl,
                'description' => $request->description,
                'business_type_id' => $request->business_type_id->toInteger(),
                'join_date' => $request->join_date ? Carbon::parse($request->join_date) : Carbon::now(),
                'address' => $request->address,
                'rating' => $request->rating ?? 0,
                'status' => 'pending',
            ]);

            // Load owner relationship for response
            $shop->load(['owner', 'businessType']);

            return $this->createdResponse($shop, 'Shop created successfully and is pending approval.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create shop: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified shop.
     * Only the shop owner (active seller) can update.
     */
    public function update(UpdateShopRequest $request, string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $shop = Shop::find($id);

            if (!$shop) {
                return $this->notFoundResponse('Shop not found');
            }

            // Check if user is the shop owner or admin
            if ($user->role !== 'admin' && $shop->owner_id !== $user->id) {
                return $this->forbiddenResponse('You do not have permission to update this shop');
            }

            // Check if seller's account is active (unless admin)
            if ($user->role !== 'admin' && $user->status !== 'active') {
                return $this->forbiddenResponse('Your account must be active to update shop information. Current status: ' . $user->status);
            }

            // Prepare update data
            $updateData = $request->only([
                'name',
                'description',
                'business_type_id',
                'address',
                'rating',
                'status',
            ]);

            $previousStatus = $shop->status;

            if ($request->filled('join_date')) {
                $updateData['join_date'] = Carbon::parse($request->join_date);
            }

            // Handle logo upload if provided
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($shop->logo) {
                    $this->deleteImage($shop->logo);
                }
                $updateData['logo'] = $this->uploadImage($request->file('logo'), 'shops');
            } elseif ($request->filled('logo') && filter_var($request->logo, FILTER_VALIDATE_URL)) {
                // Already a URL
                $updateData['logo'] = $request->logo;
            }

            if ($request->hasFile('banner')) {
                if ($shop->banner) {
                    $this->deleteImage($shop->banner);
                }
                $updateData['banner'] = $this->uploadImage($request->file('banner'), 'shops');
            } elseif ($request->filled('banner') && filter_var($request->banner, FILTER_VALIDATE_URL)) {
                $updateData['banner'] = $request->banner;
            }

            $shop->update($updateData);

            // When an admin activates a shop, promote the owner to seller
            if (
                $user->role === 'admin'
                && array_key_exists('status', $updateData)
                && $previousStatus !== 'active'
                && $shop->status === 'active'
            ) {
                $owner = $shop->owner()->first();
                if ($owner) {
                    $ownerUpdate = ['status' => 'active'];

                    if ($owner->role !== 'admin') {
                        $ownerUpdate['role'] = 'seller';
                    }

                    $owner->update($ownerUpdate);
                }
            }

            // Load relationships for response
            $shop->load(['owner', 'products', 'businessType']);

            return $this->successResponse($shop, 'Shop updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update shop: ' . $e->getMessage());
        }
    }
}
