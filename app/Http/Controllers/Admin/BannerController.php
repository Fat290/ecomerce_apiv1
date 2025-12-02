<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class BannerController extends Controller
{
    use HandlesImageUploads;

    /**
     * Ensure the authenticated user is an admin.
     */
    private function checkAdmin(): bool
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            return $user && $user->role === 'admin';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all banners for admin management.
     */
    public function index(Request $request): JsonResponse
    {
        if (!$this->checkAdmin()) {
            return $this->forbiddenResponse('Only administrators can manage banners.');
        }

        try {
            $query = Banner::query();

            if ($request->has('active')) {
                $active = filter_var($request->get('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($active !== null) {
                    $query->where('is_active', $active);
                }
            }

            $banners = $query

                ->orderByDesc('created_at')
                ->get();

            return $this->successResponse($banners, 'Banners retrieved successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve banners: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request): JsonResponse
    {
        if (!$this->checkAdmin()) {
            return $this->forbiddenResponse('Only administrators can create banners.');
        }

        // Normalize is_active to a real boolean so "0" is not treated as empty
        $request->merge([
            'is_active' => $request->boolean('is_active'),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:5120'], // 5MB
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            $imageUrl = $this->uploadImage($request->file('image'), 'banners');

            if (!$imageUrl) {
                return $this->serverErrorResponse('Failed to upload banner image.');
            }

            $banner = Banner::create([
                'title' => $validated['title'],
                'subtitle' => $validated['subtitle'] ?? null,
                'image_url' => $imageUrl,
                'is_active' => $validated['is_active'],
            ]);

            return $this->createdResponse($banner, 'Banner created successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create banner: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing banner.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if (!$this->checkAdmin()) {
            return $this->forbiddenResponse('Only administrators can update banners.');
        }

        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return $this->notFoundResponse('Banner not found.');
            }

            $data = [];

            // optional new image
            if ($request->hasFile('image')) {
                $request->validate([
                    'image' => ['image', 'max:5120'],
                ]);

                $newImageUrl = $this->uploadImage($request->file('image'), 'banners');
                if (!$newImageUrl) {
                    return $this->serverErrorResponse('Failed to upload new banner image.');
                }

                $oldImageUrl = $banner->image_url;
                $data['image_url'] = $newImageUrl;

                if ($oldImageUrl) {
                    $this->deleteImage($oldImageUrl);
                }
            }

            // optional title
            if ($request->has('title')) {
                $request->validate([
                    'title' => ['string', 'max:255'],
                ]);
                $data['title'] = $request->input('title');
            }

            // optional subtitle
            if ($request->has('subtitle')) {
                $request->validate([
                    'subtitle' => ['nullable', 'string', 'max:255'],
                ]);
                $data['subtitle'] = $request->input('subtitle');
            }

            // optional active flag
            if ($request->has('is_active')) {
                $data['is_active'] = $request->boolean('is_active');
            }

            if (!empty($data)) {
                $banner->fill($data);
                $banner->save();
            }

            return $this->successResponse($banner->fresh(), 'Banner updated successfully.');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update banner: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified banner.
     */
    public function destroy(string $id): JsonResponse
    {
        if (!$this->checkAdmin()) {
            return $this->forbiddenResponse('Only administrators can delete banners.');
        }

        try {
            $banner = Banner::find($id);

            if (!$banner) {
                return $this->notFoundResponse('Banner not found.');
            }

            if ($banner->image_url) {
                $this->deleteImage($banner->image_url);
            }

            $banner->delete();

            return $this->noContentResponse();
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete banner: ' . $e->getMessage());
        }
    }
}
