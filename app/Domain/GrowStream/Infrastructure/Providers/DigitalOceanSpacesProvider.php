<?php

namespace App\Domain\GrowStream\Infrastructure\Providers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalOceanSpacesProvider implements VideoProviderInterface
{
    protected string $disk = 'do_spaces';

    public function upload(UploadedFile $file, array $metadata = []): ProviderVideoResponse
    {
        $videoId = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension();
        $filename = "{$videoId}.{$extension}";
        
        $path = config('growstream.storage.videos') . '/' . $filename;
        
        // Upload to DigitalOcean Spaces
        Storage::disk($this->disk)->putFileAs(
            dirname($path),
            $file,
            basename($path),
            'public'
        );
        
        $playbackUrl = Storage::disk($this->disk)->url($path);
        
        // Generate thumbnail (placeholder for now)
        $thumbnailUrl = $this->generateThumbnail($videoId);
        
        return new ProviderVideoResponse(
            providerVideoId: $videoId,
            playbackUrl: $playbackUrl,
            thumbnailUrl: $thumbnailUrl,
            duration: null, // Will be extracted later
            fileSize: $file->getSize(),
            status: 'ready'
        );
    }

    public function getVideo(string $providerVideoId): ProviderVideoResponse
    {
        $extension = 'mp4'; // Default, should be stored in metadata
        $path = config('growstream.storage.videos') . "/{$providerVideoId}.{$extension}";
        
        if (!Storage::disk($this->disk)->exists($path)) {
            throw new \Exception("Video not found: {$providerVideoId}");
        }
        
        $playbackUrl = Storage::disk($this->disk)->url($path);
        $fileSize = Storage::disk($this->disk)->size($path);
        
        return new ProviderVideoResponse(
            providerVideoId: $providerVideoId,
            playbackUrl: $playbackUrl,
            thumbnailUrl: $this->getThumbnailUrl($providerVideoId),
            fileSize: $fileSize,
            status: 'ready'
        );
    }

    public function getPlaybackUrl(string $providerVideoId, bool $signed = true, int $expiresIn = 86400): string
    {
        $extension = 'mp4'; // Default
        $path = config('growstream.storage.videos') . "/{$providerVideoId}.{$extension}";
        
        if ($signed) {
            return Storage::disk($this->disk)->temporaryUrl($path, now()->addSeconds($expiresIn));
        }
        
        return Storage::disk($this->disk)->url($path);
    }

    public function delete(string $providerVideoId): bool
    {
        $extension = 'mp4'; // Default
        $path = config('growstream.storage.videos') . "/{$providerVideoId}.{$extension}";
        
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
        
        // Delete thumbnail
        $thumbnailPath = config('growstream.storage.thumbnails') . "/{$providerVideoId}.jpg";
        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            Storage::disk($this->disk)->delete($thumbnailPath);
        }
        
        return true;
    }

    public function getUploadStatus(string $providerVideoId): string
    {
        // For DigitalOcean Spaces, videos are immediately ready after upload
        $extension = 'mp4';
        $path = config('growstream.storage.videos') . "/{$providerVideoId}.{$extension}";
        
        return Storage::disk($this->disk)->exists($path) ? 'ready' : 'failed';
    }

    public function getDirectUploadUrl(array $metadata = []): array
    {
        // DigitalOcean Spaces doesn't support direct browser uploads like Cloudflare
        // Return server upload endpoint instead
        return [
            'upload_url' => route('api.growstream.videos.upload'),
            'method' => 'POST',
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];
    }

    protected function generateThumbnail(string $videoId): string
    {
        // Placeholder thumbnail generation
        // In production, use FFmpeg or similar to extract frame
        $thumbnailPath = config('growstream.storage.thumbnails') . "/{$videoId}.jpg";
        
        // For now, return a placeholder URL
        // TODO: Implement actual thumbnail generation
        return Storage::disk($this->disk)->url($thumbnailPath);
    }

    protected function getThumbnailUrl(string $videoId): string
    {
        $thumbnailPath = config('growstream.storage.thumbnails') . "/{$videoId}.jpg";
        
        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            return Storage::disk($this->disk)->url($thumbnailPath);
        }
        
        return asset('images/video-placeholder.jpg'); // Fallback
    }
}
