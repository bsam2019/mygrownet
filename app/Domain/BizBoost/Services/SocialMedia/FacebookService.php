<?php

namespace App\Domain\BizBoost\Services\SocialMedia;

use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService implements SocialMediaServiceInterface
{
    private const API_VERSION = 'v18.0';
    private const BASE_URL = 'https://graph.facebook.com';

    public function __construct(
        private ?BizBoostIntegrationModel $integration = null
    ) {}

    public function getAuthUrl(string $redirectUri, array $scopes = []): string
    {
        $defaultScopes = [
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_posts',
            'pages_manage_engagement',
            'instagram_basic',
            'instagram_content_publish',
        ];

        $scopes = array_merge($defaultScopes, $scopes);

        $params = http_build_query([
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => $redirectUri,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
            'state' => csrf_token(),
        ]);

        return "https://www.facebook.com/" . self::API_VERSION . "/dialog/oauth?{$params}";
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . '/oauth/access_token', [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to exchange code for token: ' . $response->body());
        }

        $data = $response->json();

        // Exchange short-lived token for long-lived token
        return $this->getLongLivedToken($data['access_token']);
    }

    public function getLongLivedToken(string $shortLivedToken): array
    {
        $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . '/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get long-lived token: ' . $response->body());
        }

        return $response->json();
    }

    public function getUserPages(string $accessToken): array
    {
        $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . '/me/accounts', [
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get user pages: ' . $response->body());
        }

        return $response->json()['data'] ?? [];
    }

    public function publishPost(BizBoostPostModel $post): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $pageId = $this->integration->provider_page_id;
        $accessToken = $this->integration->access_token;

        $data = [
            'message' => $post->caption,
            'access_token' => $accessToken,
        ];

        // Add media if present
        $media = $post->media()->get();
        if ($media->isNotEmpty()) {
            if ($media->count() === 1) {
                // Single image/video
                $mediaItem = $media->first();
                if ($mediaItem->media_type === 'video') {
                    return $this->publishVideo($pageId, $post, $accessToken);
                } else {
                    $data['url'] = $mediaItem->media_url;
                }
            } else {
                // Multiple images (carousel)
                return $this->publishCarousel($pageId, $post, $accessToken);
            }
        }

        $response = Http::post(
            self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/feed",
            $data
        );

        if ($response->failed()) {
            Log::error('Facebook post failed', [
                'response' => $response->body(),
                'post_id' => $post->id,
            ]);
            throw new \Exception('Failed to publish post: ' . $response->body());
        }

        return $response->json();
    }

    private function publishVideo(string $pageId, BizBoostPostModel $post, string $accessToken): array
    {
        $video = $post->media()->first();

        $response = Http::post(
            self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/videos",
            [
                'description' => $post->caption,
                'file_url' => $video->media_url,
                'access_token' => $accessToken,
            ]
        );

        if ($response->failed()) {
            throw new \Exception('Failed to publish video: ' . $response->body());
        }

        return $response->json();
    }

    private function publishCarousel(string $pageId, BizBoostPostModel $post, string $accessToken): array
    {
        // First, upload all images
        $attachedMedia = [];
        foreach ($post->media as $media) {
            $photoResponse = Http::post(
                self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/photos",
                [
                    'url' => $media->media_url,
                    'published' => false,
                    'access_token' => $accessToken,
                ]
            );

            if ($photoResponse->successful()) {
                $attachedMedia[] = ['media_fbid' => $photoResponse->json()['id']];
            }
        }

        // Then create the post with attached media
        $response = Http::post(
            self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}/feed",
            [
                'message' => $post->caption,
                'attached_media' => json_encode($attachedMedia),
                'access_token' => $accessToken,
            ]
        );

        if ($response->failed()) {
            throw new \Exception('Failed to publish carousel: ' . $response->body());
        }

        return $response->json();
    }

    public function deletePost(string $postId): bool
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $response = Http::delete(
            self::BASE_URL . '/' . self::API_VERSION . "/{$postId}",
            [
                'access_token' => $this->integration->access_token,
            ]
        );

        return $response->successful();
    }

    public function getPostAnalytics(string $postId): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $response = Http::get(
            self::BASE_URL . '/' . self::API_VERSION . "/{$postId}",
            [
                'fields' => 'insights.metric(post_impressions,post_engaged_users,post_clicks,post_reactions_by_type_total)',
                'access_token' => $this->integration->access_token,
            ]
        );

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }

    public function refreshToken(): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        return $this->getLongLivedToken($this->integration->access_token);
    }

    public function validateToken(): bool
    {
        if (!$this->integration) {
            return false;
        }

        $response = Http::get(
            self::BASE_URL . '/' . self::API_VERSION . '/me',
            [
                'access_token' => $this->integration->access_token,
            ]
        );

        return $response->successful();
    }
}
