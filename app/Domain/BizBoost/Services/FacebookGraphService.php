<?php

namespace App\Domain\BizBoost\Services;

use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FacebookGraphService
{
    private const API_VERSION = 'v18.0';
    private const BASE_URL = 'https://graph.facebook.com';

    /**
     * Publish a post to Facebook Page
     */
    public function publishPost(BizBoostPostModel $post, BizBoostIntegrationModel $integration): array
    {
        try {
            $accessToken = Crypt::decryptString($integration->access_token);
            $pageId = $integration->provider_page_id;

            // Check if post has media
            $hasMedia = $post->media->count() > 0;
            $hasVideo = $post->media->where('type', 'video')->count() > 0;

            if ($hasVideo) {
                return $this->publishVideoPost($post, $pageId, $accessToken);
            } elseif ($hasMedia) {
                return $this->publishPhotoPost($post, $pageId, $accessToken);
            } else {
                return $this->publishTextPost($post, $pageId, $accessToken);
            }

        } catch (\Exception $e) {
            Log::error("Facebook publish error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Publish text-only post
     */
    private function publishTextPost(BizBoostPostModel $post, string $pageId, string $accessToken): array
    {
        $response = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/feed", [
            'message' => $post->caption,
            'access_token' => $accessToken,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'post_id' => $response->json('id'),
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('error.message') ?? 'Unknown error',
        ];
    }

    /**
     * Publish post with photos
     */
    private function publishPhotoPost(BizBoostPostModel $post, string $pageId, string $accessToken): array
    {
        $images = $post->media->where('type', 'image');

        if ($images->count() === 1) {
            // Single photo post
            $image = $images->first();
            $imageUrl = url(Storage::url($image->path));

            $response = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/photos", [
                'url' => $imageUrl,
                'caption' => $post->caption,
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'post_id' => $response->json('post_id') ?? $response->json('id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message') ?? 'Unknown error',
            ];
        }

        // Multiple photos - create unpublished photos first
        $photoIds = [];
        foreach ($images as $image) {
            $imageUrl = url(Storage::url($image->path));

            $photoResponse = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/photos", [
                'url' => $imageUrl,
                'published' => false,
                'access_token' => $accessToken,
            ]);

            if ($photoResponse->successful()) {
                $photoIds[] = ['media_fbid' => $photoResponse->json('id')];
            }
        }

        if (empty($photoIds)) {
            return [
                'success' => false,
                'error' => 'Failed to upload photos',
            ];
        }

        // Create multi-photo post
        $response = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/feed", [
            'message' => $post->caption,
            'attached_media' => json_encode($photoIds),
            'access_token' => $accessToken,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'post_id' => $response->json('id'),
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('error.message') ?? 'Unknown error',
        ];
    }

    /**
     * Publish video post
     */
    private function publishVideoPost(BizBoostPostModel $post, string $pageId, string $accessToken): array
    {
        $video = $post->media->where('type', 'video')->first();
        
        if (!$video) {
            return [
                'success' => false,
                'error' => 'No video found',
            ];
        }

        $videoUrl = url(Storage::url($video->path));

        $response = Http::post(self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/videos", [
            'file_url' => $videoUrl,
            'description' => $post->caption,
            'access_token' => $accessToken,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'post_id' => $response->json('id'),
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('error.message') ?? 'Unknown error',
        ];
    }

    /**
     * Get page insights
     */
    public function getPageInsights(BizBoostIntegrationModel $integration, array $metrics = []): array
    {
        try {
            $accessToken = Crypt::decryptString($integration->access_token);
            $pageId = $integration->provider_page_id;

            $defaultMetrics = [
                'page_impressions',
                'page_engaged_users',
                'page_post_engagements',
                'page_fans',
            ];

            $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/insights", [
                'metric' => implode(',', $metrics ?: $defaultMetrics),
                'period' => 'day',
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

    /**
     * Get post insights
     */
    public function getPostInsights(string $postId, BizBoostIntegrationModel $integration): array
    {
        try {
            $accessToken = Crypt::decryptString($integration->access_token);

            $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . "/{$postId}/insights", [
                'metric' => 'post_impressions,post_engaged_users,post_clicks,post_reactions_by_type_total',
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
