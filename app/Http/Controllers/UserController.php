<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    use HandlesImageUploads;

    /**
     * Get the authenticated user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            // Load relationships
            $user->load(['shops', 'cartItems.product', 'wishlistItems.product']);

            return $this->successResponse($user, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve profile: ' . $e->getMessage());
        }
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $updateData = $request->only(['name', 'email', 'phone', 'address']);

            // Handle avatar upload if provided
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    $this->deleteImage($user->avatar);
                }
                $avatarUrl = $this->uploadImage($request->file('avatar'), 'avatars');
                if ($avatarUrl) {
                    $updateData['avatar'] = $avatarUrl;
                }
            } elseif ($request->filled('avatar') && filter_var($request->avatar, FILTER_VALIDATE_URL)) {
                // If avatar is a URL, use it directly
                $updateData['avatar'] = $request->avatar;
            }

            // Handle password update if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            $user->refresh();
            $user->load(['shops']);

            return $this->successResponse($user, 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific user's public profile.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            // Only show public information
            $publicData = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at,
            ];

            // If user has shops, include shop info
            if ($user->shops()->exists()) {
                $publicData['shops'] = $user->shops()->where('status', 'active')->get(['id', 'name', 'logo', 'rating']);
            }

            return $this->successResponse($publicData, 'User profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve user profile: ' . $e->getMessage());
        }
    }
}
