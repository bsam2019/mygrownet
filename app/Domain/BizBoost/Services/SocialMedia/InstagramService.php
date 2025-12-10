<?php

namespace App\Domain\BizBoost\Services\SocialMedia;

use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService implements SocialMediaServiceInterface
{
    private const API_VERSION = 'v18.0';
    private const BASE_URL = 'https://graph.facebook.com';

    public function __construct(
        private ?BizBoostIntegrationModel $integration = null
    ) {}

    public function getAuthUrl(string $redirectUri, array $scopes = []): string
    {
        // Instagram uses Facebook OAuth
        $defaultScopes = [
            'instagram_basic',
            'instagram_content_publish',
            'pages_show_list',
            'pages_read_engagement',
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

        return $response->json();
    }

    public function getInstagramBusinessAccount(string $pageId, string $accessToken): ?array
    {
        $response = Http::get(
            self::BASE_URL . '/' . self::API_VERSION . "/{$pageId}",
            [
                'fields' => 'instagram_business_account',
                'access_token' => $accessToken,
            ]
        );

        if ($response->failed() || !isset($response->json()['instagram_business_account'])) {
            return null;
        }

        $igAccountId = $response->json()['instagram_business_account']['id'];

        // Get account details
        $accountResponse = Http::get(
            self::BASE_URL . '/' . self::API_VERSION . "/{$igAccountId}",
            [
                'fields' => 'id,username,profile_picture_url',
                'access_token' => $accessToken,
            ]
        );

        return $accountResponse->successful() ? $accountResponse->json() : null;
    }

    public function publishPost(BizBoostPostModel $post): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $igAccountId = $this->integration->provider_user_id;
        $accessToken = $this->integration->access_token;

        $media = $post->media()->get();

        if ($media->isEmpty()) {
            throw new \Exception('Instagram posts require at least one image or video');
        }

        if ($media->count() === 1) {
            return $this->publishSingleMedia($igAccountId, $post, $accessToken);
        } else {
            return $this->publishCarousel($igAccountId, $post, $accessToken);
        }
    }

    private function publishSingleMedia(string $igAccountId, BizBoostPostModel $post, string $accessToken): array
    {
        $mediaItem = $post->media()->first();

        // Step 1: Create media container
        $containerData = [
            'caption' => $post->caption,
            'access_token' => $accessToken,
        ];

        if ($mediaItem->media_type === 'video') {
            $containerData['media_type'] = 'VIDEO';
            $containerData['video_url'] = $mediaItem->media_url;
        } else {
            $containerData['image_url'] = $mediaItem->media_url;
        }

        $containerResponse = Http::post(
            self::BASE_URL . '/' . self::API_VERSION . "/{$igAccountId}/media",
            $containerData
        );

        if ($containerResponse->failed()) {
            throw new \Exception('Failed to create media container: ' . $containerResponse->body());
        }

        $containerId = $containerResponse->json()['id'];

        // Step 2: Wait for video processing if needed
        if ($mediaItem->media_type === 'video') {
            $this->waitForVideoProcessing($containerId, $accessToken);
        }

        // Step 3: Publish the container
        $publishResponse = Http::post(
            self::BASE_URL . '/' . self::API_VERSION . "/{$igAccountId}/media_publish",
            [
                'creation_id' => $containerId,
                'access_token' => $accessToken,
            ]
        );

        if ($publishResponse->failed()) {
            throw new \Exception('Failed to publish media: ' . $publishResponse->body());
        }

        return $publishResponse->json();
    }

    private function publishCarousel(string $igAccountId, BizBoostPostModel $post, string $accessToken): array
    {
        // Step 1: Create containers for each media item
        $childrenIds = [];

        foreach ($post->media as $media) {
            $containerData = [
                'is_carousel_item' => true,
                'access_token' => $accessToken,
            ];

            if ($media->media_type === 'video') {
                $containerData['media_type'] = 'VIDEO';
                $containerData['video_url'] = $media->media_url;
            } else {
                $containerData['image_url'] = $media->media_url;
            }

            $response = Http::post(
                self::BASE_URL . '/' . self::API_VERSION . "/{$igAccountId}/media",
                $containerData
            );

            if ($response->successful()) {
                $childrenIds[] = $response->json()['id'];
            }
        }

        // Step 2: Create carousel container
        $carouselResponse = Http::post(
            self::BASE_URL . '/' . self::API_VERSION . "/{$igAccountId}/media",
            [
                'media_type' => 'CAROUSEL',
                'caption' => $post->caption,
                'children' => implode(',', $childrenIds),
                'access_token' => $accessToken,
            ]
        );

        if ($carouselResponse->failed()) {
            throw new \Exception('Failed to create carousel: ' . $carouselResponse->body());
        }

        $carouselId = $carouselResponse->json()['id'];

        // Step 3: Publish the carousel
        $publishResponse = Http::post(
            self::BASE_URL . '/' . self::API_VERSION . "/{$igAccountId}/media_publish",
            [
                'creation_id' => $carouselId,
                'access_token' => $accessToken,
            ]
        );

        if ($publishResponse->failed()) {
            throw new \Exception('Failed to publish carousel: ' . $publishResponse->body());
        }

        return $publishResponse->json();
    }

    private function waitForVideoProcessing(string $containerId, string $accessToken, int $maxAttempts = 30): void
    {
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $response = Http::get(
                self::BASE_URL . '/' . self::API_VERSION . "/{$containerId}",
                [
                    'fields' => 'status_code',
                    'access_token' => $accessToken,
                ]
            );

            if ($response->successful()) {
                $statusCode = $response->json()['status_code'] ?? '';

                if ($statusCode === 'FINISHED') {
                    return;
                } elseif ($statusCode === 'ERROR') {
                    throw new \Exception('Video processing failed');
                }
            }

            sleep(2);
            $attempts++;
        }

        throw new \Exception('Video processing timeout');
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
            self::BASE_URL . '/' . self::API_VERSION . "/{$postId}/insights",
            [
                'metric' => 'engagement,impressions,reach,saved',
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

        // Instagram uses Facebook's token refresh
        $response = Http::get(self::BASE_URL . '/' . self::API_VERSION . '/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'fb_exchange_token' => $this->integration->access_token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }

        return $response->json();
    }

    public function validateToken(): bool
    {
        if (!$this->integration) {
            return false;
        }

        $response = Http::get(
            self::BASE_URL . '/' . self::API_VERSION . '/' . $this->integration->provider_user_id,
            [
                'fields' => 'id',
                'access_token' => $this->integration->access_token,
            ]
        );

        return $response->successful();
    }
}
