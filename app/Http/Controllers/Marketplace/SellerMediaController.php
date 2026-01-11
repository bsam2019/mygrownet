<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceSeller;
use App\Models\MarketplaceSellerMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class SellerMediaController extends Controller
{
    /**
     * Get all media for the authenticated seller
     */
    public function index(Request $request)
    {
        $seller = MarketplaceSeller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            return response()->json(['data' => []]);
        }

        $media = MarketplaceSellerMedia::where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        $transformedData = $media->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'url' => $item->url,
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
                'limit' => self::MAX_MEDIA_PER_SELLER,
                'remaining' => max(0, self::MAX_MEDIA_PER_SELLER - $media->total()),
            ],
        ]);
    }

    /**
     * Maximum media files per seller (100 images = ~500MB at 5MB each)
     */
    private const MAX_MEDIA_PER_SELLER = 100;

    /**
     * Upload a new media file
     */
    public function store(Request $request)
    {
        $seller = MarketplaceSeller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller account not found',
            ], 404);
        }

        // Check media limit
        $currentCount = MarketplaceSellerMedia::where('seller_id', $seller->id)->count();
        if ($currentCount >= self::MAX_MEDIA_PER_SELLER) {
            return response()->json([
                'success' => false,
                'message' => 'Media library limit reached (' . self::MAX_MEDIA_PER_SELLER . ' images). Please delete unused images.',
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $file = $request->file('file');
        $directory = "marketplace/sellers/{$seller->id}";
        
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, file_get_contents($file));

        $width = null;
        $height = null;
        $variants = [];

        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                $image = Image::read($file);
                $width = $image->width();
                $height = $image->height();

                // Create thumbnail
                $thumbPath = "{$directory}/thumbs/{$filename}";
                $thumb = $image->scale(width: 300);
                Storage::disk('public')->put($thumbPath, $thumb->toJpeg(80));
                $variants['thumbnail'] = $thumbPath;
            } catch (\Exception $e) {
                \Log::warning('Failed to process image', ['error' => $e->getMessage()]);
            }
        }

        $media = MarketplaceSellerMedia::create([
            'seller_id' => $seller->id,
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

    /**
     * Store a base64 encoded image (for cropped images)
     */
    public function storeBase64(Request $request)
    {
        $seller = MarketplaceSeller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller account not found',
            ], 404);
        }

        $request->validate([
            'image' => 'required|string',
            'filename' => 'nullable|string|max:255',
        ]);

        $imageData = $request->input('image');
        $filename = $request->input('filename', 'cropped-' . time() . '.jpg');
        $directory = "marketplace/sellers/{$seller->id}";

        try {
            // Extract base64 data from data URL
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '.' . $extension;
            }

            $decodedImage = base64_decode($imageData);
            if ($decodedImage === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid base64 image data',
                ], 400);
            }

            $uniqueFilename = Str::uuid() . '-' . $filename;
            $path = "{$directory}/{$uniqueFilename}";

            Storage::disk('public')->put($path, $decodedImage);

            $width = null;
            $height = null;
            $variants = [];

            try {
                $image = Image::read($decodedImage);
                $width = $image->width();
                $height = $image->height();

                $thumbPath = "{$directory}/thumbs/{$uniqueFilename}";
                $thumb = $image->scale(width: 300);
                Storage::disk('public')->put($thumbPath, $thumb->toJpeg(80));
                $variants['thumbnail'] = $thumbPath;
            } catch (\Exception $e) {
                \Log::warning('Failed to process cropped image', ['error' => $e->getMessage()]);
            }

            $media = MarketplaceSellerMedia::create([
                'seller_id' => $seller->id,
                'filename' => $uniqueFilename,
                'original_name' => $filename,
                'path' => $path,
                'disk' => 'public',
                'mime_type' => 'image/jpeg',
                'size' => strlen($decodedImage),
                'width' => $width,
                'height' => $height,
                'variants' => $variants,
                'metadata' => ['source' => 'cropped'],
            ]);

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
                'url' => $media->url,
            ]);

        } catch (\Exception $e) {
            \Log::error('Base64 image upload failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a media file
     */
    public function destroy(Request $request, int $mediaId)
    {
        $seller = MarketplaceSeller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller account not found',
            ], 404);
        }

        $media = MarketplaceSellerMedia::where('seller_id', $seller->id)
            ->where('id', $mediaId)
            ->first();

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found',
            ], 404);
        }

        Storage::disk($media->disk)->delete($media->path);
        
        if (isset($media->variants['thumbnail'])) {
            Storage::disk($media->disk)->delete($media->variants['thumbnail']);
        }

        $media->delete();

        return response()->json(['success' => true]);
    }
}
