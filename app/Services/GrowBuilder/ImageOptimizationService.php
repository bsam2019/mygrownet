<?php

namespace App\Services\GrowBuilder;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Image Optimization Service
 * Handles auto-compression, resizing, and WebP conversion for uploaded images
 */
class ImageOptimizationService
{
    private ImageManager $manager;
    
    // Max dimensions for different use cases
    private const MAX_WIDTH = 1920;
    private const MAX_HEIGHT = 1080;
    private const THUMBNAIL_WIDTH = 400;
    private const THUMBNAIL_HEIGHT = 300;
    
    // Quality settings
    private const JPEG_QUALITY = 85;
    private const WEBP_QUALITY = 80;
    private const PNG_COMPRESSION = 6;
    
    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }
    
    /**
     * Optimize an uploaded image
     * Returns paths to optimized image and WebP version
     */
    public function optimize(UploadedFile $file, string $directory = 'growbuilder/media'): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = time();
        
        // Generate unique filename
        $baseName = "{$originalName}-{$timestamp}";
        
        // Read the image
        $image = $this->manager->read($file->getPathname());
        
        // Get original dimensions
        $originalWidth = $image->width();
        $originalHeight = $image->height();
        
        // Resize if too large (maintain aspect ratio)
        if ($originalWidth > self::MAX_WIDTH || $originalHeight > self::MAX_HEIGHT) {
            $image->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT);
        }
        
        // Determine output format and save optimized version
        $optimizedPath = $this->saveOptimized($image, $directory, $baseName, $extension);
        
        // Create WebP version
        $webpPath = $this->saveWebP($image, $directory, $baseName);
        
        // Create thumbnail
        $thumbnailPath = $this->saveThumbnail($file, $directory, $baseName);
        
        // Get file sizes for comparison
        $originalSize = $file->getSize();
        $optimizedSize = Storage::disk('public')->size($optimizedPath);
        $webpSize = Storage::disk('public')->size($webpPath);
        
        return [
            'original' => [
                'path' => $optimizedPath,
                'url' => Storage::disk('public')->url($optimizedPath),
                'size' => $optimizedSize,
                'width' => $image->width(),
                'height' => $image->height(),
            ],
            'webp' => [
                'path' => $webpPath,
                'url' => Storage::disk('public')->url($webpPath),
                'size' => $webpSize,
            ],
            'thumbnail' => [
                'path' => $thumbnailPath,
                'url' => Storage::disk('public')->url($thumbnailPath),
            ],
            'savings' => [
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'webp_size' => $webpSize,
                'saved_bytes' => $originalSize - $optimizedSize,
                'saved_percent' => round((1 - $optimizedSize / $originalSize) * 100, 1),
                'webp_saved_percent' => round((1 - $webpSize / $originalSize) * 100, 1),
            ],
        ];
    }
    
    /**
     * Save optimized version in original format
     */
    private function saveOptimized($image, string $directory, string $baseName, string $extension): string
    {
        $path = "{$directory}/{$baseName}.{$extension}";
        
        // Encode based on format
        if ($extension === 'png') {
            $encoded = $image->toPng();
        } elseif ($extension === 'gif') {
            $encoded = $image->toGif();
        } else {
            // Default to JPEG for jpg, jpeg, and unknown formats
            $encoded = $image->toJpeg(self::JPEG_QUALITY);
            if (!in_array($extension, ['jpg', 'jpeg'])) {
                $path = "{$directory}/{$baseName}.jpg";
            }
        }
        
        Storage::disk('public')->put($path, (string) $encoded);
        
        return $path;
    }
    
    /**
     * Save WebP version
     */
    private function saveWebP($image, string $directory, string $baseName): string
    {
        $path = "{$directory}/{$baseName}.webp";
        $encoded = $image->toWebp(self::WEBP_QUALITY);
        Storage::disk('public')->put($path, (string) $encoded);
        
        return $path;
    }
    
    /**
     * Create and save thumbnail
     */
    private function saveThumbnail(UploadedFile $file, string $directory, string $baseName): string
    {
        $path = "{$directory}/thumbnails/{$baseName}-thumb.webp";
        
        $thumbnail = $this->manager->read($file->getPathname());
        $thumbnail->cover(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
        
        $encoded = $thumbnail->toWebp(self::WEBP_QUALITY);
        Storage::disk('public')->put($path, (string) $encoded);
        
        return $path;
    }
    
    /**
     * Optimize an existing image from URL or path
     */
    public function optimizeFromPath(string $path, string $directory = 'growbuilder/media'): array
    {
        $fullPath = Storage::disk('public')->path($path);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Image not found: {$path}");
        }
        
        $originalName = pathinfo($path, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $timestamp = time();
        $baseName = "{$originalName}-optimized-{$timestamp}";
        
        $image = $this->manager->read($fullPath);
        $originalSize = filesize($fullPath);
        
        // Resize if needed
        if ($image->width() > self::MAX_WIDTH || $image->height() > self::MAX_HEIGHT) {
            $image->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT);
        }
        
        // Save optimized and WebP versions
        $optimizedPath = $this->saveOptimized($image, $directory, $baseName, $extension);
        $webpPath = $this->saveWebP($image, $directory, $baseName);
        
        $optimizedSize = Storage::disk('public')->size($optimizedPath);
        $webpSize = Storage::disk('public')->size($webpPath);
        
        return [
            'original' => [
                'path' => $optimizedPath,
                'url' => Storage::disk('public')->url($optimizedPath),
                'size' => $optimizedSize,
            ],
            'webp' => [
                'path' => $webpPath,
                'url' => Storage::disk('public')->url($webpPath),
                'size' => $webpSize,
            ],
            'savings' => [
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'webp_size' => $webpSize,
                'saved_percent' => round((1 - $optimizedSize / $originalSize) * 100, 1),
                'webp_saved_percent' => round((1 - $webpSize / $originalSize) * 100, 1),
            ],
        ];
    }
    
    /**
     * Get responsive image srcset
     */
    public function generateSrcSet(string $imagePath, array $widths = [320, 640, 960, 1280, 1920]): string
    {
        $srcset = [];
        $directory = dirname($imagePath);
        $baseName = pathinfo($imagePath, PATHINFO_FILENAME);
        
        $image = $this->manager->read(Storage::disk('public')->path($imagePath));
        $originalWidth = $image->width();
        
        foreach ($widths as $width) {
            if ($width <= $originalWidth) {
                $resizedPath = "{$directory}/{$baseName}-{$width}w.webp";
                
                // Create resized version if it doesn't exist
                if (!Storage::disk('public')->exists($resizedPath)) {
                    $resized = $this->manager->read(Storage::disk('public')->path($imagePath));
                    $resized->scale($width);
                    Storage::disk('public')->put($resizedPath, (string) $resized->toWebp(self::WEBP_QUALITY));
                }
                
                $url = Storage::disk('public')->url($resizedPath);
                $srcset[] = "{$url} {$width}w";
            }
        }
        
        return implode(', ', $srcset);
    }
}
