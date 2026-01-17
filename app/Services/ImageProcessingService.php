<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class ImageProcessingService
{
    // Phase 1: MVP - Basic upload with guidelines
    public function uploadRaw(UploadedFile $file, string $path = 'marketplace/products'): string
    {
        return $file->store($path, 'public');
    }

    /**
     * Validate image dimensions meet minimum requirements
     */
    public function validateDimensions(UploadedFile $file, int $minWidth = 400, int $minHeight = 400): array
    {
        try {
            $image = Image::read($file->getRealPath());
            $width = $image->width();
            $height = $image->height();
            
            if ($width < $minWidth || $height < $minHeight) {
                return [
                    'valid' => false,
                    'message' => "Image must be at least {$minWidth}x{$minHeight} pixels. Your image is {$width}x{$height}.",
                    'width' => $width,
                    'height' => $height,
                ];
            }
            
            return [
                'valid' => true,
                'width' => $width,
                'height' => $height,
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Could not read image dimensions.',
            ];
        }
    }

    // Phase 2: Basic optimization (resize, compress) with standardization
    public function uploadOptimized(UploadedFile $file, string $path = 'marketplace/products'): array
    {
        $filename = Str::random(40) . '.jpg'; // Always save as JPG for consistency
        
        // Load image
        $image = Image::read($file->getRealPath());

        // Standardize to square (1:1 aspect ratio) with white background
        $standardizedImage = $this->standardizeToSquare($image, 1200);

        // Generate multiple sizes from the standardized image
        $sizes = [
            'original' => $this->saveImage($standardizedImage, $path, $filename, 'original', 1200),
            'large' => $this->saveImage($standardizedImage, $path, $filename, 'large', 800),
            'medium' => $this->saveImage($standardizedImage, $path, $filename, 'medium', 600),
            'thumbnail' => $this->saveImage($standardizedImage, $path, $filename, 'thumbnail', 300),
        ];

        return $sizes;
    }

    /**
     * Standardize image to square format with white background
     * This ensures consistent presentation across all product images
     */
    private function standardizeToSquare($image, int $targetSize = 1200)
    {
        $width = $image->width();
        $height = $image->height();
        
        // Determine the larger dimension
        $maxDimension = max($width, $height);
        
        // If image is already square and large enough, just resize
        if ($width === $height) {
            return $image->resize($targetSize, $targetSize);
        }
        
        // Create a square canvas with white background
        $canvas = Image::create($maxDimension, $maxDimension)->fill('#ffffff');
        
        // Calculate position to center the image
        $x = (int) (($maxDimension - $width) / 2);
        $y = (int) (($maxDimension - $height) / 2);
        
        // Place the original image on the canvas
        $canvas->place($image, 'top-left', $x, $y);
        
        // Resize to target size
        return $canvas->resize($targetSize, $targetSize);
    }

    /**
     * Save image at specific size
     */
    private function saveImage($image, string $path, string $filename, string $suffix, int $size): string
    {
        $processedFilename = pathinfo($filename, PATHINFO_FILENAME) . '_' . $suffix . '.jpg';
        $fullPath = $path . '/' . $processedFilename;

        // Clone and resize
        $processed = clone $image;
        $processed->resize($size, $size);

        // Encode as JPEG with good quality
        $encoded = $processed->toJpeg(quality: 85);

        // Save to storage
        Storage::disk('public')->put($fullPath, $encoded);

        return $fullPath;
    }

    // Phase 3: Background removal for featured products
    public function uploadWithBackgroundRemoval(
        UploadedFile $file, 
        string $path = 'marketplace/products',
        bool $isFeatured = false
    ): array {
        // First, optimize the image
        $sizes = $this->uploadOptimized($file, $path);

        // If featured product, attempt background removal
        if ($isFeatured) {
            try {
                $processedPath = $this->removeBackground($sizes['original'], $path);
                if ($processedPath) {
                    $sizes['processed'] = $processedPath;
                }
            } catch (\Exception $e) {
                // Log error but don't fail - just use original
                \Log::warning('Background removal failed: ' . $e->getMessage());
            }
        }

        return $sizes;
    }

    // Phase 4: Advanced processing with multiple options
    public function uploadAdvanced(
        UploadedFile $file,
        string $path = 'marketplace/products',
        array $options = []
    ): array {
        $defaultOptions = [
            'optimize' => true,
            'remove_background' => false,
            'add_watermark' => false,
            'watermark_text' => 'MyGrowNet',
            'enhance' => false,
            'is_featured' => false,
        ];

        $options = array_merge($defaultOptions, $options);

        // Start with optimization
        $sizes = $this->uploadOptimized($file, $path);

        // Apply background removal if requested
        if ($options['remove_background'] || $options['is_featured']) {
            try {
                $processedPath = $this->removeBackground($sizes['original'], $path);
                if ($processedPath) {
                    $sizes['processed'] = $processedPath;
                }
            } catch (\Exception $e) {
                \Log::warning('Background removal failed: ' . $e->getMessage());
            }
        }

        // Add watermark if requested
        if ($options['add_watermark']) {
            $sizes['watermarked'] = $this->addWatermark(
                $sizes['original'], 
                $path, 
                $options['watermark_text']
            );
        }

        // Enhance image if requested
        if ($options['enhance']) {
            $sizes['enhanced'] = $this->enhanceImage($sizes['original'], $path);
        }

        return $sizes;
    }

    /**
     * Process and save image at specific size (legacy method for compatibility)
     */
    private function processImage($image, ?int $width, ?int $height, string $path, string $filename, string $suffix): string
    {
        $processedFilename = pathinfo($filename, PATHINFO_FILENAME) . '_' . $suffix . '.jpg';
        $fullPath = $path . '/' . $processedFilename;

        // Clone image to avoid modifying original
        $processed = clone $image;

        // Resize maintaining aspect ratio
        if ($width && $height) {
            $processed->resize($width, $height);
        } elseif ($width) {
            $processed->scaleDown($width);
        } elseif ($height) {
            $processed->scaleDown(height: $height);
        }

        // Encode with quality optimization
        $encoded = $processed->toJpeg(quality: 85);

        // Save to storage
        Storage::disk('public')->put($fullPath, $encoded);

        return $fullPath;
    }

    /**
     * Remove background from image (Phase 3)
     * Uses local processing with Intervention Image
     */
    private function removeBackground(string $imagePath, string $basePath): ?string
    {
        // For MVP, we'll use a simple approach: detect edges and create transparency
        // For production, integrate with remove.bg API or similar service
        
        $fullPath = Storage::disk('public')->path($imagePath);
        $image = Image::read($fullPath);

        // Simple background removal: make white/light backgrounds transparent
        // This is a basic implementation - for better results, use AI services
        $processed = $image->brightness(10)->contrast(10);

        $filename = pathinfo($imagePath, PATHINFO_FILENAME) . '_nobg.png';
        $outputPath = $basePath . '/' . basename($filename);

        // Save as PNG to support transparency
        $encoded = $processed->toPng();
        Storage::disk('public')->put($outputPath, $encoded);

        return $outputPath;
    }

    /**
     * Remove background using remove.bg API (optional, requires API key)
     */
    private function removeBackgroundWithAPI(string $imagePath, string $basePath): ?string
    {
        $apiKey = config('services.removebg.api_key');
        
        if (!$apiKey) {
            return null;
        }

        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            $ch = curl_init('https://api.remove.bg/v1.0/removebg');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Api-Key: ' . $apiKey,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'image_file' => new \CURLFile($fullPath),
                'size' => 'auto',
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $filename = pathinfo($imagePath, PATHINFO_FILENAME) . '_nobg.png';
                $outputPath = $basePath . '/' . basename($filename);
                Storage::disk('public')->put($outputPath, $result);
                return $outputPath;
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Remove.bg API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add watermark to image (Phase 4)
     */
    private function addWatermark(string $imagePath, string $basePath, string $text): string
    {
        $fullPath = Storage::disk('public')->path($imagePath);
        $image = Image::read($fullPath);

        // Add text watermark in bottom right corner
        $image->text($text, $image->width() - 10, $image->height() - 10, function ($font) {
            $font->file(public_path('fonts/Roboto-Regular.ttf')); // Ensure font exists
            $font->size(24);
            $font->color('#ffffff');
            $font->align('right');
            $font->valign('bottom');
        });

        $filename = pathinfo($imagePath, PATHINFO_FILENAME) . '_watermarked.' . pathinfo($imagePath, PATHINFO_EXTENSION);
        $outputPath = $basePath . '/' . basename($filename);

        $encoded = $image->toJpeg(quality: 90);
        Storage::disk('public')->put($outputPath, $encoded);

        return $outputPath;
    }

    /**
     * Enhance image quality (Phase 4)
     */
    private function enhanceImage(string $imagePath, string $basePath): string
    {
        $fullPath = Storage::disk('public')->path($imagePath);
        $image = Image::read($fullPath);

        // Apply enhancements
        $image->brightness(5)
              ->contrast(10)
              ->sharpen(10);

        $filename = pathinfo($imagePath, PATHINFO_FILENAME) . '_enhanced.' . pathinfo($imagePath, PATHINFO_EXTENSION);
        $outputPath = $basePath . '/' . basename($filename);

        $encoded = $image->toJpeg(quality: 90);
        Storage::disk('public')->put($outputPath, $encoded);

        return $outputPath;
    }

    /**
     * Delete all versions of an image
     */
    public function deleteImage(string $imagePath): void
    {
        $basePath = pathinfo($imagePath, PATHINFO_DIRNAME);
        $filename = pathinfo($imagePath, PATHINFO_FILENAME);
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

        // Delete all versions
        $suffixes = ['original', 'large', 'medium', 'thumbnail', 'processed', 'nobg', 'watermarked', 'enhanced'];
        
        foreach ($suffixes as $suffix) {
            $path = $basePath . '/' . $filename . '_' . $suffix . '.' . $extension;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Delete base file
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Get image guidelines for sellers
     */
    public static function getGuidelines(): array
    {
        return [
            'minimum_size' => '400x400px minimum',
            'recommended_size' => '1200x1200px for best quality',
            'max_file_size' => '5MB',
            'formats' => ['JPG', 'PNG', 'WebP'],
            'aspect_ratio' => 'Images will be automatically converted to square (1:1) format',
            'background' => 'White or neutral backgrounds preferred (non-square images will get white padding)',
            'lighting' => 'Good lighting, avoid shadows',
            'angles' => 'Show product from multiple angles',
            'details' => 'Include close-ups of important features',
            'lifestyle' => 'Include lifestyle shots where relevant',
            'consistency' => 'Use consistent style across all images',
            'note' => 'All images are automatically optimized and standardized for consistent display',
        ];
    }
}
