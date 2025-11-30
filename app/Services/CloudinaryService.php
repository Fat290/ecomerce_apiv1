<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    private $cloudinary;

    public function __construct()
    {
        // Initialize Cloudinary configuration
        // Support both CLOUDINARY_URL format and individual variables
        // Compatible with cloudinary-labs/cloudinary-laravel package
        $cloudUrl = env('CLOUDINARY_URL');

        if ($cloudUrl) {
            // Use CLOUDINARY_URL if provided (format: cloudinary://KEY:SECRET@CLOUD_NAME)
            Configuration::instance($cloudUrl);
        } else {
            // Fall back to individual variables (also supports CLOUDINARY_KEY/SECRET from package)
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME') ?: env('CLOUDINARY_CLOUD'),
                    'api_key' => env('CLOUDINARY_API_KEY') ?: env('CLOUDINARY_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET') ?: env('CLOUDINARY_SECRET'),
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
        }

        $this->cloudinary = new Cloudinary();
    }

    /**
     * Upload a single image to Cloudinary
     *
     * @param UploadedFile|string $file The file to upload or a file path
     * @param string $folder The folder path in Cloudinary (e.g., 'products', 'shops', 'avatars')
     * @param array $options Additional upload options
     * @return string|null The uploaded image URL or null on failure
     */
    public function uploadImage($file, string $folder = 'uploads', array $options = []): ?string
    {
        try {
            // Default options
            $defaultOptions = [
                'folder' => $folder,
                'resource_type' => 'image',
                'overwrite' => true,
                'invalidate' => true,
            ];

            $uploadOptions = array_merge($defaultOptions, $options);

            // Handle file upload
            if ($file instanceof UploadedFile) {
                $result = $this->cloudinary->uploadApi()->upload(
                    $file->getRealPath(),
                    $uploadOptions
                );
            } elseif (is_string($file) && file_exists($file)) {
                $result = $this->cloudinary->uploadApi()->upload(
                    $file,
                    $uploadOptions
                );
            } else {
                // If it's already a URL, return it
                if (filter_var($file, FILTER_VALIDATE_URL)) {
                    return $file;
                }
                Log::error('Invalid file provided to CloudinaryService::uploadImage');
                return null;
            }

            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error('Cloudinary upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload multiple images to Cloudinary
     *
     * @param array $files Array of UploadedFile instances or file paths
     * @param string $folder The folder path in Cloudinary
     * @param array $options Additional upload options
     * @return array Array of uploaded image URLs
     */
    public function uploadImages(array $files, string $folder = 'uploads', array $options = []): array
    {
        $uploadedUrls = [];

        foreach ($files as $file) {
            $url = $this->uploadImage($file, $folder, $options);
            if ($url) {
                $uploadedUrls[] = $url;
            }
        }

        return $uploadedUrls;
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param string $publicId The public ID of the image to delete
     * @return bool True if successful, false otherwise
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);
            return isset($result['result']) && $result['result'] === 'ok';
        } catch (\Exception $e) {
            Log::error('Cloudinary delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract public ID from Cloudinary URL
     *
     * @param string $url The Cloudinary URL
     * @return string|null The public ID or null if not found
     */
    public function extractPublicId(string $url): ?string
    {
        // Cloudinary URLs format: https://res.cloudinary.com/{cloud_name}/image/upload/{public_id}.{format}
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[^.]+)?$/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get optimized image URL with transformations
     * Builds transformation URL manually for simplicity
     *
     * @param string $url The Cloudinary URL
     * @param array $transformations Transformation options (e.g., ['width' => 500, 'height' => 500, 'crop' => 'fill', 'quality' => 'auto'])
     * @return string The transformed URL
     */
    public function getOptimizedUrl(string $url, array $transformations = []): string
    {
        if (empty($transformations)) {
            return $url;
        }

        $publicId = $this->extractPublicId($url);
        if (!$publicId) {
            return $url;
        }

        try {
            // Build transformation string
            $transforms = [];

            if (isset($transformations['width']) || isset($transformations['height'])) {
                $width = $transformations['width'] ?? '';
                $height = $transformations['height'] ?? '';
                $crop = $transformations['crop'] ?? 'limit';
                $transforms[] = "w_{$width},h_{$height},c_{$crop}";
            }

            if (isset($transformations['quality'])) {
                $quality = $transformations['quality'];
                $transforms[] = "q_{$quality}";
            }

            if (isset($transformations['format'])) {
                $transforms[] = "f_{$transformations['format']}";
            }

            if (empty($transforms)) {
                return $url;
            }

            $transformString = implode(',', $transforms);
            $cloudName = env('CLOUDINARY_CLOUD_NAME') ?: env('CLOUDINARY_CLOUD');

            // Reconstruct URL with transformations
            return "https://res.cloudinary.com/{$cloudName}/image/upload/{$transformString}/{$publicId}";
        } catch (\Exception $e) {
            Log::error('Cloudinary transformation error: ' . $e->getMessage());
            return $url;
        }
    }
}
