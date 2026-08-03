<?php

namespace App\Domain\GrowStream\Infrastructure\Jobs;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class GenerateThumbnailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $videoId
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $video = Video::find($this->videoId);

        if (! $video) {
            Log::error('GenerateThumbnailsJob: Video not found', ['video_id' => $this->videoId]);

            return;
        }

        try {
            Log::info('GenerateThumbnailsJob: Starting thumbnail generation', ['video_id' => $this->videoId]);

            // Skip if a thumbnail is already available
            if ($video->thumbnail_url) {
                Log::info('GenerateThumbnailsJob: Thumbnail already set', [
                    'video_id' => $this->videoId,
                    'thumbnail_url' => $video->thumbnail_url,
                ]);

                return;
            }

            // 1. Ask the video provider for a generated thumbnail (Cloudflare Stream
            //    generates thumbnails automatically during encoding).
            if ($video->provider_video_id) {
                $providerThumbnail = $this->resolveProviderThumbnail($video);
                if ($providerThumbnail !== null) {
                    $video->update(['thumbnail_url' => $providerThumbnail]);
                    Log::info('GenerateThumbnailsJob: Provider thumbnail applied', [
                        'video_id' => $this->videoId,
                        'thumbnail_url' => $providerThumbnail,
                    ]);

                    return;
                }
            }

            // 2. Try extracting a frame with FFmpeg when available.
            $ffmpegThumbnail = $this->extractWithFfmpeg($video);
            if ($ffmpegThumbnail !== null) {
                $video->update(['thumbnail_url' => $ffmpegThumbnail]);
                Log::info('GenerateThumbnailsJob: FFmpeg thumbnail generated', [
                    'video_id' => $this->videoId,
                    'thumbnail_url' => $ffmpegThumbnail,
                ]);

                return;
            }

            // 3. Fall back to a content-type placeholder.
            $this->applyPlaceholderThumbnail($video);

            Log::info('GenerateThumbnailsJob: Thumbnail generation completed', ['video_id' => $this->videoId]);

        } catch (\Exception $e) {
            Log::error('GenerateThumbnailsJob: Exception occurred', [
                'video_id' => $this->videoId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Resolve a provider-generated thumbnail (e.g. Cloudflare Stream).
     */
    protected function resolveProviderThumbnail(Video $video): ?string
    {
        try {
            $provider = VideoProviderFactory::make($video->video_provider);
            $response = $provider->getVideo($video->provider_video_id);

            return $response->thumbnailUrl;
        } catch (\Throwable $e) {
            Log::warning('GenerateThumbnailsJob: Provider thumbnail lookup failed', [
                'video_id' => $this->videoId,
                'provider' => $video->video_provider,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Extract a thumbnail frame using FFmpeg when the binary is available and
     * the source video is locally accessible.
     */
    protected function extractWithFfmpeg(Video $video): ?string
    {
        if (! function_exists('exec') || ! $this->ffmpegAvailable()) {
            return null;
        }

        $source = $this->resolveSourcePath($video);
        if ($source === null) {
            return null;
        }

        $disk = config('growstream.storage.thumbnails', 'growstream/thumbnails');
        $thumbnailPath = $disk.'/'.$video->id.'.jpg';

        $process = new Process([
            'ffmpeg',
            '-y',
            '-ss', '00:00:05',
            '-i', $source,
            '-vframes', '1',
            '-vf', 'scale=1280:-2',
            '-q:v', '3',
            $this->absoluteThumbnailPath($thumbnailPath),
        ]);

        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::warning('GenerateThumbnailsJob: FFmpeg extraction failed', [
                'video_id' => $this->videoId,
                'error' => $process->getErrorOutput(),
            ]);

            return null;
        }

        return $this->thumbnailPublicUrl($thumbnailPath);
    }

    protected function ffmpegAvailable(): bool
    {
        try {
            $process = new Process(['ffmpeg', '-version']);
            $process->setTimeout(5);
            $process->run();

            return $process->isSuccessful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Determine a local source path for FFmpeg. Returns null for remote-only
     * sources (e.g. Cloudflare Stream manifests) that FFmpeg cannot download.
     */
    protected function resolveSourcePath(Video $video): ?string
    {
        if ($video->video_provider === 'local' && $video->video_url && str_starts_with($video->video_url, '/')) {
            return $video->video_url;
        }

        // If the video URL points at our own local storage, map it back.
        if (str_contains((string) $video->video_url, (string) config('app.url'))) {
            $relative = str_replace(rtrim((string) config('app.url'), '/').'/', '', (string) $video->video_url);
            $fullPath = public_path($relative);
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

    protected function absoluteThumbnailPath(string $thumbnailPath): string
    {
        return public_path($thumbnailPath);
    }

    protected function thumbnailPublicUrl(string $thumbnailPath): string
    {
        return asset($thumbnailPath);
    }

    /**
     * Apply a content-type placeholder thumbnail (temporary solution).
     */
    protected function applyPlaceholderThumbnail(Video $video): void
    {
        if ($video->thumbnail_url) {
            return;
        }

        $placeholderMap = [
            'movie' => 'https://via.placeholder.com/1280x720/2563eb/ffffff?text=Movie',
            'series' => 'https://via.placeholder.com/1280x720/7c3aed/ffffff?text=Series',
            'episode' => 'https://via.placeholder.com/1280x720/7c3aed/ffffff?text=Episode',
            'lesson' => 'https://via.placeholder.com/1280x720/059669/ffffff?text=Lesson',
            'short' => 'https://via.placeholder.com/720x1280/d97706/ffffff?text=Short',
            'workshop' => 'https://via.placeholder.com/1280x720/4f46e5/ffffff?text=Workshop',
            'webinar' => 'https://via.placeholder.com/1280x720/4f46e5/ffffff?text=Webinar',
        ];

        $thumbnailUrl = $placeholderMap[$video->content_type] ?? $placeholderMap['movie'];

        $video->update([
            'thumbnail_url' => $thumbnailUrl,
            'poster_url' => $thumbnailUrl,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateThumbnailsJob: Job failed', [
            'video_id' => $this->videoId,
            'error' => $exception->getMessage(),
        ]);
    }
}
