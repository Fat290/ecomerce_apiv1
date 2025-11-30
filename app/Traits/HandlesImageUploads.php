<?php

namespace App\Traits;

use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

trait HandlesImageUploads
{
    /**
     * Upload a single image to Cloudinary
     *
     * @param UploadedFile|string|null $file
     * @param string $folder
     * @return string|null
     */
    protected function uploadImage($file, string $folder = 'uploads'): ?string
    {
        if (!$file) {
            return null;
        }

        // If it's already a URL, return it
        if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
            return $file;
        }

        try {
            $cloudinaryService = app(CloudinaryService::class);
            return $cloudinaryService->uploadImage($file, $folder);
        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload multiple images to Cloudinary
     *
     * @param array|null $files
     * @param string $folder
     * @return array
     */
    protected function uploadImages(?array $files, string $folder = 'uploads'): array
    {
        if (!$files || empty($files)) {
            return [];
        }

        $uploadedUrls = [];
        $cloudinaryService = app(CloudinaryService::class);

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $url = $cloudinaryService->uploadImage($file, $folder);
                if ($url) {
                    $uploadedUrls[] = $url;
                }
            } elseif (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
                // Already a URL, keep it
                $uploadedUrls[] = $file;
            }
        }

        return $uploadedUrls;
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param string $url
     * @return bool
     */
    protected function deleteImage(string $url): bool
    {
        try {
            $cloudinaryService = app(CloudinaryService::class);
            $publicId = $cloudinaryService->extractPublicId($url);

            if ($publicId) {
                return $cloudinaryService->deleteImage($publicId);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Image deletion failed: ' . $e->getMessage());
            return false;
        }
    }
}
