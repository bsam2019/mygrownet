<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderMedia;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\ImageOptimizationService;
use App\Services\GrowBuilder\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class MediaController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private StorageService $storageService,
    ) {}
    
    /**
     * Get the image optimizer service (lazy loaded only when needed)
     */
    private function getImageOptimizer(): ?ImageOptimizationService
    {
        try {
            return app(ImageOptimizationService::class);
        } catch (\Exception $e) {
            \Log::warning('ImageOptimizationService not available: ' . $e->getMessage());
            return null;
        }
    }

    public function index(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $media = GrowBuilderMedia::where('site_id', $siteId)
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        $transformedData = $media->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'url' => $item->url,
                'webpUrl' => $item->webp_url,
                'thumbnailUrl' => $item->thumbnail_url,
                'filename' => $item->filename,
                'originalName' => $item->original_name,
                'size' => $item->human_size,
                'width' => $item->width,
                'height' => $item->height,
            ];
        });

        return response()->json([
            'data' => $transformedData,
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'total' => $media->total(),
            ],
        ]);
    }

    public function store(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg|max:10240',
            'optimize' => 'boolean',
        ]);

        $file = $request->file('file');
        
        // Check storage limit before upload
        $siteModel = GrowBuilderSite::find($siteId);
        if ($siteModel && !$this->storageService->hasAvailableStorage($siteModel, $file->getSize())) {
            return response()->json([
                'success' => false,
                'message' => 'Storage limit exceeded. Please delete some files or upgrade your plan.',
                'storage' => [
                    'used' => $siteModel->storage_used_formatted,
                    'limit' => $siteModel->storage_limit_formatted,
                    'percentage' => $siteModel->storage_percentage,
                ],
            ], 422);
        }
        
        $shouldOptimize = $request->boolean('optimize', true);
        $directory = "growbuilder/{$siteId}";
        
        $isOptimizable = str_starts_with($file->getMimeType(), 'image/') 
            && !in_array($file->getClientOriginalExtension(), ['svg', 'gif']);

        // Try optimized upload if requested and possible
        if ($shouldOptimize && $isOptimizable) {
            $imageOptimizer = $this->getImageOptimizer();
            if ($imageOptimizer) {
                try {
                    $result = $imageOptimizer->optimize($file, $directory);
                    
                    $media = GrowBuilderMedia::create([
                        'site_id' => $siteId,
                        'filename' => basename($result['original']['path']),
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $result['original']['path'],
                        'disk' => 'public',
                        'mime_type' => $file->getMimeType(),
                        'size' => $result['original']['size'],
                        'width' => $result['original']['width'],
                        'height' => $result['original']['height'],
                        'variants' => [
                            'webp' => $result['webp']['path'],
                            'thumbnail' => $result['thumbnail']['path'],
                        ],
                        'metadata' => [
                            'optimized' => true,
                            'original_size' => $result['savings']['original_size'],
                            'saved_percent' => $result['savings']['saved_percent'],
                            'webp_saved_percent' => $result['savings']['webp_saved_percent'],
                        ],
                    ]);

                    // Update storage usage
                    if ($siteModel) {
                        $this->storageService->updateStorageUsage($siteModel);
                    }

                    return response()->json([
                        'success' => true,
                        'media' => [
                            'id' => $media->id,
                            'url' => $media->url,
                            'webpUrl' => $media->webp_url,
                            'thumbnailUrl' => $media->thumbnail_url,
                            'filename' => $media->filename,
                            'originalName' => $media->original_name,
                            'size' => $media->human_size,
                            'width' => $media->width,
                            'height' => $media->height,
                        ],
                        'optimization' => [
                            'original_size' => $this->formatBytes($result['savings']['original_size']),
                            'optimized_size' => $this->formatBytes($result['savings']['optimized_size']),
                            'webp_size' => $this->formatBytes($result['savings']['webp_size']),
                            'saved_percent' => $result['savings']['saved_percent'],
                            'webp_saved_percent' => $result['savings']['webp_saved_percent'],
                        ],
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Image optimization failed, falling back to standard upload', [
                        'error' => $e->getMessage(),
                        'file' => $file->getClientOriginalName(),
                    ]);
                }
            }
        }

        // Standard upload (for SVG, GIF, or when optimization is disabled/fails)
        return $this->standardUpload($file, $siteId, $directory);
    }

    /**
     * Standard upload without optimization
     */
    private function standardUpload($file, int $siteId, string $directory)
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, file_get_contents($file));

        $width = null;
        $height = null;
        $variants = [];

        if (str_starts_with($file->getMimeType(), 'image/') && $file->getClientOriginalExtension() !== 'svg') {
            try {
                $image = Image::read($file);
                $width = $image->width();
                $height = $image->height();

                $thumbPath = "{$directory}/thumbs/{$filename}";
                $thumb = $image->scale(width: 300);
                Storage::disk('public')->put($thumbPath, $thumb->toJpeg(80));
                $variants['thumbnail'] = $thumbPath;
            } catch (\Exception $e) {
                // Ignore image processing errors
            }
        }

        $media = GrowBuilderMedia::create([
            'site_id' => $siteId,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'variants' => $variants,
        ]);

        // Update storage usage
        $siteModel = GrowBuilderSite::find($siteId);
        if ($siteModel) {
            $this->storageService->updateStorageUsage($siteModel);
        }

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'url' => $media->url,
                'thumbnailUrl' => $media->thumbnail_url,
                'filename' => $media->filename,
                'originalName' => $media->original_name,
                'size' => $media->human_size,
                'width' => $media->width,
                'height' => $media->height,
            ],
        ]);
    }

    public function destroy(Request $request, int $siteId, int $mediaId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $media = GrowBuilderMedia::where('site_id', $siteId)
            ->where('id', $mediaId)
            ->firstOrFail();

        Storage::disk($media->disk)->delete($media->path);
        
        if (isset($media->variants['webp'])) {
            Storage::disk($media->disk)->delete($media->variants['webp']);
        }
        if (isset($media->variants['thumbnail'])) {
            Storage::disk($media->disk)->delete($media->variants['thumbnail']);
        }

        $media->delete();

        // Update storage usage after deletion
        $siteModel = GrowBuilderSite::find($siteId);
        if ($siteModel) {
            $this->storageService->updateStorageUsage($siteModel);
        }

        return response()->json(['success' => true]);
    }
    
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Generate a favicon from the site logo
     */
    public function generateFavicon(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $request->validate([
            'logoUrl' => 'required|string|max:500',
        ]);

        $logoUrl = $request->input('logoUrl');
        $directory = "growbuilder/{$siteId}";

        try {
            // Get the logo image content
            $logoContent = null;
            
            // Check if it's a local storage URL
            if (str_contains($logoUrl, '/storage/')) {
                $path = str_replace('/storage/', '', parse_url($logoUrl, PHP_URL_PATH));
                if (Storage::disk('public')->exists($path)) {
                    $logoContent = Storage::disk('public')->get($path);
                }
            }
            
            // If not local, try to fetch from URL
            if (!$logoContent) {
                $logoContent = @file_get_contents($logoUrl);
            }

            if (!$logoContent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not fetch logo image',
                ], 400);
            }

            // Create favicon using Intervention Image
            $image = Image::read($logoContent);
            
            // Generate multiple favicon sizes
            $faviconSizes = [
                ['size' => 16, 'name' => 'favicon-16x16.png'],
                ['size' => 32, 'name' => 'favicon-32x32.png'],
                ['size' => 180, 'name' => 'apple-touch-icon.png'],
            ];

            $faviconPaths = [];
            $mainFaviconUrl = null;

            foreach ($faviconSizes as $config) {
                $resized = clone $image;
                $resized->cover($config['size'], $config['size']);
                
                $faviconPath = "{$directory}/favicons/{$config['name']}";
                Storage::disk('public')->put($faviconPath, $resized->toPng());
                
                $faviconPaths[$config['name']] = Storage::disk('public')->url($faviconPath);
                
                // Use 32x32 as the main favicon
                if ($config['size'] === 32) {
                    $mainFaviconUrl = $faviconPaths[$config['name']];
                }
            }

            // Also create a standard favicon.ico (32x32)
            $favicon32 = clone $image;
            $favicon32->cover(32, 32);
            $icoPath = "{$directory}/favicons/favicon.ico";
            Storage::disk('public')->put($icoPath, $favicon32->toPng());

            // Save favicon variants to site settings
            $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($siteId);
            if ($siteModel) {
                $settings = $siteModel->settings ?? [];
                $settings['favicons'] = $faviconPaths;
                $siteModel->settings = $settings;
                $siteModel->favicon = $mainFaviconUrl;
                $siteModel->save();
            }

            return response()->json([
                'success' => true,
                'faviconUrl' => $mainFaviconUrl,
                'favicons' => $faviconPaths,
            ]);

        } catch (\Exception $e) {
            \Log::error('Favicon generation failed', [
                'error' => $e->getMessage(),
                'logoUrl' => $logoUrl,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate favicon: ' . $e->getMessage(),
            ], 500);
        }
    }
}
