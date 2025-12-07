<?php

namespace App\Domain\BizBoost\Services;

use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InstagramGraphService
{
    private const API_VERSION = 'v18.0';
    private const BASE_URL = 'https://graph.facebook.com';

    /**
     * Publish a post to Instagram Business Account
     */
    public function publishPost(BizBoostPostModel $post, BizBoostIntegrationModel $integration): array
    {
        try {
            $accessToken = Crypt::decryptString($integration->access_token);
            $igUserId = $integration->provider_page_id;

            // Check post type and media
            $hasMedia = $post->media->count() > 0;
            $hasVideo = $post->media->where('type', 'video')->count() > 0;
            $imageCount = $post->media->where('type', 'image')->count();

            if (!$hasMedia) {
                return [
                    'success' => false,
                    'error' => 'Instagram requires at least one image or video',
                ];
            }

            // Determine post type
            if ($post->post_type === 'reel' || $hasVideo) {
                return $this->publishReel($post, $igUserId, $accessToken);
            } elseif ($post->post_type === 'story') {
                return $this->publishStory($post, $igUserId, $accessToken);
            } elseif ($imageCount > 1) {
                return $this->publishCarousel($post, $igUserId, $accessToken);
            } else {
                return $this->publishSingleImage($post, $igUserId, $accessToken);
            }

        } catch (\Exception $e) {
            Log::error("Instagram publish error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Publish single image post
     */
    private function publishSingleImage(BizBoostPostModel $post, string $igUserId, string $accessToken): array
    {
        $image = $post->media->where('type', 'image')->first();
        $imageUrl = url(Storage::url($image->path));

        // Step 1: Create media container
        $containerResponse = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$igUserId}/media", [
            'image_url' => $imageUrl,
            'caption' => $post->caption,
            'access_token' => $accessToken,
        ]);

        if (!$containerResponse->successful()) {
            return [
                'success' => false,
                'error' => $containerResponse->json('error.message') ?? 'Failed to create media container',
            ];
        }

        $containerId = $containerResponse->json('id');

        // Step 2: Publish the container
        return $this->publishContainer($igUserId, $containerId, $accessToken);
    }

    /**
     * Publish carousel (multiple images)
     */
    private function publishCarousel(BizBoostPostModel $post, string $igUserId, string $accessToken): array
    {
        $images = $post->media->where('type', 'image')->take(10); // Instagram max 10 items
        $childContainerIds = [];

        // Step 1: Create child containers for each image
        foreach ($images as $image) {
            $imageUrl = url(Storage::url($image->path));

            $childResponse = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$igUserId}/media", [
                'image_url' => $imageUrl,
                'is_carousel_item' => true,
                'access_token' => $accessToken,
            ]);

            if ($childResponse->successful()) {
                $childContainerIds[] = $childResponse->json('id');
            }
        }

        if (empty($childContainerIds)) {
            return [
                'success' => false,
                'error' => 'Failed to create carousel items',
            ];
        }

        // Step 2: Create carousel container
        $carouselResponse = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$igUserId}/media", [
            'media_type' => 'CAROUSEL',
            'children' => implode(',', $childContainerIds),
            'caption' => $post->caption,
            'access_token' => $accessToken,
        ]);

        if (!$carouselResponse->successful()) {
            return [
                'success' => false,
                'error' => $carouselResponse->json('error.message') ?? 'Failed to create carousel',
            ];
        }

        $containerId = $carouselResponse->json('id');

        // Step 3: Publish the carousel
        return $this->publishContainer($igUserId, $containerId, $accessToken);
    }

    /**
     * Publish Reel (video)
     */
    private function publishReel(BizBoostPostModel $post, string $igUserId, string $accessToken): array
    {
        $video = $post->media->where('type', 'video')->first();
        
        if (!$video) {
            return [
                'success' => false,
                'error' => 'No video found for Reel',
            ];
        }

        $videoUrl = url(Storage::url($video->path));
        $thumbnailUrl = $video->thumbnail_path 
            ? url(Storage::url($video->thumbnail_path))
            : null;

        // Step 1: Create reel container
        $params = [
            'media_type' => 'REELS',
            'video_url' => $videoUrl,
            'caption' => $post->caption,
            'access_token' => $accessToken,
        ];

        if ($thumbnailUrl) {
            $params['cover_url'] = $thumbnailUrl;
        }

        $containerResponse = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$igUserId}/media", $params);

        if (!$containerResponse->successful()) {
            return [
                'success' => false,
                'error' => $containerResponse->json('error.message') ?? 'Failed to create reel container',
            ];
        }

        $containerId = $containerResponse->json('id');

        // Step 2: Wait for video processing and publish
        return $this->waitAndPublish($igUserId, $containerId, $accessToken);
    }

    /**
     * Publish Story
     */
    private function publishStory(BizBoostPostModel $post, string $igUserId, string $accessToken): array
    {
        $media = $post->media->first();
        $isVideo = $media->type === 'video';
        $mediaUrl = url(Storage::url($media->path));

        $params = [
            'media_type' => 'STORIES',
            'access_token' => $accessToken,
        ];

        if ($isVideo) {
            $params['video_url'] = $mediaUrl;
        } else {
            $params['image_url'] = $mediaUrl;
        }

        $containerResponse = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$igUserId}/media", $params);

        if (!$containerResponse->successful()) {
            return [
                'success' => false,
                'error' => $containerResponse->json('error.message') ?? 'Failed to create story container',
            ];
        }

        $containerId = $containerResponse->json('id');

        if ($isVideo) {
            return $this->waitAndPublish($igUserId, $containerId, $accessToken);
        }

        return $this->publishContainer($igUserId, $containerId, $accessToken);
    }

    /**
     * Publish a media container
     */
    private function publishContainer(string $igUserId, string $containerId, string $accessToken): array
    {
        $publishResponse = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$igUserId}/media_publish", [
            'creation_id' => $containerId,
            'access_token' => $accessToken,
        ]);

        if ($publishResponse->successful()) {
            return [
                'success' => true,
                'post_id' => $publishResponse->json('id'),
            ];
        }

        return [
            'success' => false,
            'error' => $publishResponse->json('error.message') ?? 'Failed to publish',
        ];
    }

    /**
     * Wait for video processing and then publish
     */
    private function waitAndPublish(string $igUserId, string $containerId, string $accessToken, int $maxAttempts = 30): array
    {
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            sleep(2); // Wait 2 seconds between checks

            $statusResponse = Http::get(self::BASE_URL . '/' . self::API_VERSION . "/{$containerId}", [
                'fields' => 'status_code',
                'access_token' => $accessToken,
            ]);

            if (!$statusResponse->successful()) {
                $attempts++;
                continue;
            }

            $status = $statusResponse->json('status_code');

            if ($status === 'FINISHED') {
                return $this->publishContainer($igUserId, $containerId, $accessToken);
            } elseif ($status === 'ERROR') {
                return [
                    'success' => false,
                    'error' => 'Video processing failed',
                ];
            }

            $attempts++;
        }

        return [
            'success' => false,
            'error' => 'Video processing timeout',
        ];
    }

    /**
     * Get media insights
     */
    public function getMediaInsights(string $mediaId, BizBoostIntegrationModel $integration): array
    {
        try {
            $accessToken = Crypt::decryptString($integration->access_token);

            $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . "/{$mediaId}/insights", [
                'metric' => 'impressions,reach,engagement,saved',
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message') ?? 'Unknown error',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
