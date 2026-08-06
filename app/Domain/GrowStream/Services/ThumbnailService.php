<?php

namespace App\Domain\GrowStream\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;

class ThumbnailService
{
    private ImageManager $imageManager;
    
    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Process an uploaded thumbnail: validate, resize to multiple sizes,
     * generate WebP + JPEG versions, upload to Wasabi.
     *
     * @param UploadedFile $file
     * @param string $videoId Video ID for path organization
     * @return array{thumb: string, medium: string, large: string, thumb_webp: string, medium_webp: string, large_webp: string}
     */
    public function processAndStore(UploadedFile $file, string $videoId): array
    {
        $config = config('growstream.thumbnails');
        $disk = Storage::disk($config['disk']);
        
        // Load original image
        $image = $this->imageManager->read($file->getPathname());
        
        // Auto-crop to 16:9 if aspect ratio is close (within 10% tolerance)
        $image = $this->ensureAspectRatio($image, 16 / 9, tolerance: 0.1);
        
        $urls = [];
        
        // Generate each size in both JPEG and WebP
        foreach ($config['sizes'] as $sizeName => $dimensions) {
            [$width, $height] = $dimensions;
            
            // Resize image
            $resized = clone $image;
            $resized->scale(width: $width, height: $height);
            
            // Generate JPEG version
            $jpegPath = $this->generatePath($videoId, $sizeName, 'jpg');
            $jpegContent = $resized->toJpeg(quality: $config['quality']);
            $disk->put($jpegPath, $jpegContent);
            $urls[$sizeName] = $disk->url($jpegPath);
            
            // Generate WebP version (smaller file size)
            if ($config['generate_webp']) {
                $webpPath = $this->generatePath($videoId, $sizeName, 'webp');
                $webpContent = $resized->toWebp(quality: $config['quality']);
                $disk->put($webpPath, $webpContent);
                $urls["{$sizeName}_webp"] = $disk->url($webpPath);
            }
        }
        
        return $urls;
    }

    /**
     * Delete all thumbnail files for a video from storage.
     *
     * @param array|null $thumbnailSizes The thumbnail_sizes JSON data
     * @param string $disk Storage disk name
     */
    public function deleteThumbnails(?array $thumbnailSizes, string $disk = 'wasabi'): void
    {
        if (!$thumbnailSizes) {
            return;
        }
        
        $storage = Storage::disk($disk);
        
        foreach ($thumbnailSizes as $url) {
            $path = $this->extractPathFromUrl($url);
            if ($path && $storage->exists($path)) {
                $storage->delete($path);
            }
        }
    }

    /**
     * Ensure image has the correct aspect ratio by cropping if needed.
     * Only crops if the current ratio is within tolerance of the target.
     *
     * @param \Intervention\Image\Interfaces\ImageInterface $image
     * @param float $targetRatio e.g., 16/9 = 1.777...
     * @param float $tolerance Allowed deviation (0.1 = 10%)
     */
    private function ensureAspectRatio($image, float $targetRatio, float $tolerance = 0.1)
    {
        $currentRatio = $image->width() / $image->height();
        $ratioDiff = abs($currentRatio - $targetRatio) / $targetRatio;
        
        // If ratio is already close enough, return as-is
        if ($ratioDiff <= $tolerance) {
            return $image;
        }
        
        // Calculate crop dimensions to achieve target ratio
        if ($currentRatio > $targetRatio) {
            // Image is too wide - crop width
            $newWidth = (int)($image->height() * $targetRatio);
            $x = (int)(($image->width() - $newWidth) / 2);
            return $image->crop($newWidth, $image->height(), $x, 0);
        } else {
            // Image is too tall - crop height
            $newHeight = (int)($image->width() / $targetRatio);
            $y = (int)(($image->height() - $newHeight) / 2);
            return $image->crop($image->width(), $newHeight, 0, $y);
        }
    }

    /**
     * Generate storage path for a thumbnail file.
     *
     * @param string $videoId
     * @param string $size thumb|medium|large
     * @param string $extension jpg|webp
     * @return string
     */
    private function generatePath(string $videoId, string $size, string $extension): string
    {
        return sprintf(
            'growstream/thumbnails/%s/%s_%s.%s',
            $videoId,
            $videoId,
            $size,
            $extension
        );
    }

    /**
     * Extract file path from full URL.
     *
     * @param string $url
     * @return string|null
     */
    private function extractPathFromUrl(string $url): ?string
    {
        // Parse URL and extract path after domain
        $parts = parse_url($url);
        $path = $parts['path'] ?? null;
        
        if (!$path) {
            return null;
        }
        
        // Remove leading slash
        return ltrim($path, '/');
    }
}
