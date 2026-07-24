<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class SellerMediaController extends Controller
{
    private const MAX_MEDIA_PER_SELLER = 100;

    public function index(Request $request)
    {
        $seller = DB::table('marketplace_sellers')
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$seller) {
            return response()->json(['data' => []]);
        }

        $media = DB::table('marketplace_seller_media')
            ->where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        $transformedData = $media->getCollection()->map(function ($item) {
            $variants = is_string($item->variants) ? json_decode($item->variants, true) : (array) ($item->variants ?? []);
            return [
                'id' => $item->id,
                'url' => $this->getMediaUrl($item->disk, $item->path),
                'thumbnailUrl' => $this->getThumbnailUrl($item->disk, $item->path, $variants),
                'filename' => $item->filename,
                'originalName' => $item->original_name,
                'size' => $this->getHumanSize((int) $item->size),
                'width' => $item->width,
                'height' => $item->height,
            ];
        });

        $media->setCollection(collect($transformedData));

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

    public function store(Request $request)
    {
        $seller = DB::table('marketplace_sellers')
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller account not found',
            ], 404);
        }

        $currentCount = DB::table('marketplace_seller_media')
            ->where('seller_id', $seller->id)
            ->count();

        if ($currentCount >= self::MAX_MEDIA_PER_SELLER) {
            return response()->json([
                'success' => false,
                'message' => 'Media library limit reached (' . self::MAX_MEDIA_PER_SELLER . ' images). Please delete unused images.',
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120|dimensions:min_width=500,min_height=500',
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

                $thumbPath = "{$directory}/thumbs/{$filename}";
                $thumb = $image->scale(width: 300);
                Storage::disk('public')->put($thumbPath, $thumb->toJpeg(80));
                $variants['thumbnail'] = $thumbPath;
            } catch (\Exception $e) {
                \Log::warning('Failed to process image', ['error' => $e->getMessage()]);
            }
        }

        $mediaId = DB::table('marketplace_seller_media')->insertGetId([
            'seller_id' => $seller->id,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'variants' => json_encode($variants),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $savedMedia = DB::table('marketplace_seller_media')->find($mediaId);
        $savedVariants = is_string($savedMedia->variants) ? json_decode($savedMedia->variants, true) : [];

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $savedMedia->id,
                'url' => $this->getMediaUrl($savedMedia->disk, $savedMedia->path),
                'thumbnailUrl' => $this->getThumbnailUrl($savedMedia->disk, $savedMedia->path, $savedVariants),
                'filename' => $savedMedia->filename,
                'originalName' => $savedMedia->original_name,
                'size' => $this->getHumanSize((int) $savedMedia->size),
                'width' => $savedMedia->width,
                'height' => $savedMedia->height,
            ],
        ]);
    }

    public function storeBase64(Request $request)
    {
        $seller = DB::table('marketplace_sellers')
            ->where('user_id', $request->user()->id)
            ->first();

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

            $mediaId = DB::table('marketplace_seller_media')->insertGetId([
                'seller_id' => $seller->id,
                'filename' => $uniqueFilename,
                'original_name' => $filename,
                'path' => $path,
                'disk' => 'public',
                'mime_type' => 'image/jpeg',
                'size' => strlen($decodedImage),
                'width' => $width,
                'height' => $height,
                'variants' => json_encode($variants),
                'metadata' => json_encode(['source' => 'cropped']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $savedMedia = DB::table('marketplace_seller_media')->find($mediaId);
            $savedVariants = is_string($savedMedia->variants) ? json_decode($savedMedia->variants, true) : [];

            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $savedMedia->id,
                    'url' => $this->getMediaUrl($savedMedia->disk, $savedMedia->path),
                    'thumbnailUrl' => $this->getThumbnailUrl($savedMedia->disk, $savedMedia->path, $savedVariants),
                    'filename' => $savedMedia->filename,
                    'originalName' => $savedMedia->original_name,
                    'size' => $this->getHumanSize((int) $savedMedia->size),
                    'width' => $savedMedia->width,
                    'height' => $savedMedia->height,
                ],
                'url' => $this->getMediaUrl($savedMedia->disk, $savedMedia->path),
            ]);

        } catch (\Exception $e) {
            \Log::error('Base64 image upload failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, int $mediaId)
    {
        $seller = DB::table('marketplace_sellers')
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller account not found',
            ], 404);
        }

        $media = DB::table('marketplace_seller_media')
            ->where('seller_id', $seller->id)
            ->where('id', $mediaId)
            ->first();

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found',
            ], 404);
        }

        Storage::disk($media->disk)->delete($media->path);

        $variants = is_string($media->variants) ? json_decode($media->variants, true) : (array) ($media->variants ?? []);
        if (isset($variants['thumbnail'])) {
            Storage::disk($media->disk)->delete($variants['thumbnail']);
        }

        DB::table('marketplace_seller_media')
            ->where('id', $media->id)
            ->delete();

        return response()->json(['success' => true]);
    }

    private function getMediaUrl(string $disk, string $path): string
    {
        return Storage::disk($disk)->url($path);
    }

    private function getThumbnailUrl(string $disk, string $path, array $variants): ?string
    {
        if (isset($variants['thumbnail'])) {
            return Storage::disk($disk)->url($variants['thumbnail']);
        }
        return Storage::disk($disk)->url($path);
    }

    private function getHumanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
