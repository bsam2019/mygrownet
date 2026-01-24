<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Centralized Media Upload Service
 * 
 * Provides consistent media upload handling across all modules.
 * Optional service - existing upload implementations can continue to work.
 * 
 * Usage:
 * $service = new MediaUploadService();
 * $url = $service->uploadLogo($request->file('logo'));
 */
class MediaUploadService
{
    /**
     * Upload and optimize a logo
     *
     * @param UploadedFile $file
     * @param string $disk Storage disk (default: 'public')
     * @param string $directory Directory within disk (default: 'logos')
     * @param int $maxWidth Maximum width in pixels (default: 500)
     * @return string Public URL of uploaded file
     */
    public function uploadLogo(
        UploadedFile $file,
        string $disk = 'public',
        string $directory = 'logos',
        int $maxWidth = 500
    ): string {
        // Validate file
        $this->validateImage($file, 5 * 1024); // 5MB max

        // Generate unique filename
        $filename = $this->generateFilename($file);
        $path = "{$directory}/{$filename}";

        // Optimize and store image
        if ($this->shouldOptimize($file)) {
            $optimized = $this->optimizeImage($file, $maxWidth);
            Storage::disk($disk)->put($path, $optimized);
        } else {
            // Store as-is (e.g., SVG files)
            Storage::disk($disk)->putFileAs($directory, $file, $filename);
        }

        // Return public URL
        return Storage::disk($disk)->url($path);
    }

    /**
     * Upload a cover image
     *
     * @param UploadedFile $file
     * @param string $disk
     * @param string $directory
     * @param int $maxWidth
     * @return string
     */
    public function uploadCoverImage(
        UploadedFile $file,
        string $disk = 'public',
        string $directory = 'covers',
        int $maxWidth = 1920
    ): string {
        return $this->uploadLogo($file, $disk, $directory, $maxWidth);
    }

    /**
     * Upload a product image
     *
     * @param UploadedFile $file
     * @param string $disk
     * @param string $directory
     * @param int $maxWidth
     * @return string
     */
    public function uploadProductImage(
        UploadedFile $file,
        string $disk = 'public',
        string $directory = 'products',
        int $maxWidth = 800
    ): string {
        return $this->uploadLogo($file, $disk, $directory, $maxWidth);
    }

    /**
     * Delete a media file
     *
     * @param string $url Public URL of the file
     * @param string $disk Storage disk
     * @return bool
     */
    public function deleteMedia(string $url, string $disk = 'public'): bool
    {
        // Extract path from URL
        $path = $this->extractPathFromUrl($url, $disk);
        
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Validate image file
     *
     * @param UploadedFile $file
     * @param int $maxSizeKB Maximum file size in KB
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateImage(UploadedFile $file, int $maxSizeKB = 5120): void
    {
        $validator = validator(['file' => $file], [
            'file' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp,svg',
                "max:{$maxSizeKB}",
            ],
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $unique = Str::random(8);
        
        return "{$name}-{$unique}.{$extension}";
    }

    /**
     * Check if image should be optimized
     *
     * @param UploadedFile $file
     * @return bool
     */
    protected function shouldOptimize(UploadedFile $file): bool
    {
        // Don't optimize SVG files
        return !in_array($file->getClientOriginalExtension(), ['svg']);
    }

    /**
     * Optimize image
     *
     * @param UploadedFile $file
     * @param int $maxWidth
     * @return string Binary image data
     */
    protected function optimizeImage(UploadedFile $file, int $maxWidth): string
    {
        // Use PHP's built-in GD library for image optimization
        $mimeType = $file->getMimeType();
        $sourcePath = $file->getRealPath();
        
        // Create image resource based on type
        $image = match($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (!$image) {
            // If we can't process it, return original
            return file_get_contents($sourcePath);
        }

        // Get original dimensions
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // Calculate new dimensions if needed
        if ($originalWidth > $maxWidth) {
            $ratio = $maxWidth / $originalWidth;
            $newWidth = $maxWidth;
            $newHeight = (int)($originalHeight * $ratio);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        // Create new image with calculated dimensions
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize the image with high quality
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Output to buffer
        ob_start();
        
        switch($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($resized, null, 85); // 85% quality - good balance
                break;
            case 'image/png':
                imagepng($resized, null, 6); // Compression level 6 (0-9)
                break;
            case 'image/gif':
                imagegif($resized);
                break;
            case 'image/webp':
                imagewebp($resized, null, 85);
                break;
        }
        
        $imageData = ob_get_clean();

        // Free memory
        imagedestroy($image);
        imagedestroy($resized);

        return $imageData;
    }

    /**
     * Extract storage path from public URL
     *
     * @param string $url
     * @param string $disk
     * @return string|null
     */
    protected function extractPathFromUrl(string $url, string $disk): ?string
    {
        $storageUrl = Storage::disk($disk)->url('');
        
        if (Str::startsWith($url, $storageUrl)) {
            return Str::after($url, $storageUrl);
        }

        return null;
    }
}
