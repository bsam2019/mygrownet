<?php

namespace App\Domain\BizBoost\Services\SocialMedia;

use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokService implements SocialMediaServiceInterface
{
    private const BASE_URL = 'https://open.tiktokapis.com';
    private const AUTH_URL = 'https://www.tiktok.com/v2/auth/authorize';

    public function __construct(
        private ?BizBoostIntegrationModel $integration = null
    ) {}

    public function getAuthUrl(string $redirectUri, array $scopes = []): string
    {
        $defaultScopes = [
            'user.info.basic',
            'video.list',
            'video.upload',
            'video.publish',
        ];

        $scopes = array_merge($defaultScopes, $scopes);

        $params = http_build_query([
            'client_key' => config('services.tiktok.client_key'),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(',', $scopes),
            'state' => csrf_token(),
        ]);

        return self::AUTH_URL . "?{$params}";
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $response = Http::asForm()->post(self::BASE_URL . '/v2/oauth/token/', [
            'client_key' => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to exchange code for token: ' . $response->body());
        }

        return $response->json()['data'] ?? [];
    }

    public function getUserInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(self::BASE_URL . '/v2/user/info/', [
                'fields' => 'open_id,union_id,avatar_url,display_name',
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get user info: ' . $response->body());
        }

        return $response->json()['data']['user'] ?? [];
    }

    public function publishPost(BizBoostPostModel $post): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $media = $post->media()->first();

        if (!$media || $media->media_type !== 'video') {
            throw new \Exception('TikTok only supports video posts');
        }

        // Step 1: Initialize upload
        $initResponse = $this->initializeUpload($post);

        if (!isset($initResponse['data']['publish_id'])) {
            throw new \Exception('Failed to initialize upload');
        }

        $publishId = $initResponse['data']['publish_id'];
        $uploadUrl = $initResponse['data']['upload_url'];

        // Step 2: Upload video
        $this->uploadVideo($uploadUrl, $media->media_url);

        // Step 3: Publish video
        return $this->publishVideo($publishId, $post);
    }

    private function initializeUpload(BizBoostPostModel $post): array
    {
        $response = Http::withToken($this->integration->access_token)
            ->post(self::BASE_URL . '/v2/post/publish/video/init/', [
                'post_info' => [
                    'title' => $post->title ?? substr($post->caption, 0, 150),
                    'description' => $post->caption,
                    'privacy_level' => 'PUBLIC_TO_EVERYONE',
                    'disable_duet' => false,
                    'disable_comment' => false,
                    'disable_stitch' => false,
                    'video_cover_timestamp_ms' => 1000,
                ],
                'source_info' => [
                    'source' => 'FILE_UPLOAD',
                    'video_size' => 0, // Will be determined during upload
                    'chunk_size' => 10485760, // 10MB chunks
                    'total_chunk_count' => 1,
                ],
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to initialize upload: ' . $response->body());
        }

        return $response->json();
    }

    private function uploadVideo(string $uploadUrl, string $videoUrl): void
    {
        // Download video content
        $videoContent = Http::get($videoUrl)->body();

        // Upload to TikTok
        $response = Http::withBody($videoContent, 'video/mp4')
            ->put($uploadUrl);

        if ($response->failed()) {
            throw new \Exception('Failed to upload video: ' . $response->body());
        }
    }

    private function publishVideo(string $publishId, BizBoostPostModel $post): array
    {
        $response = Http::withToken($this->integration->access_token)
            ->post(self::BASE_URL . '/v2/post/publish/status/fetch/', [
                'publish_id' => $publishId,
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to publish video: ' . $response->body());
        }

        $data = $response->json()['data'] ?? [];

        // Poll for completion
        $maxAttempts = 30;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $statusResponse = Http::withToken($this->integration->access_token)
                ->post(self::BASE_URL . '/v2/post/publish/status/fetch/', [
                    'publish_id' => $publishId,
                ]);

            if ($statusResponse->successful()) {
                $status = $statusResponse->json()['data']['status'] ?? '';

                if ($status === 'PUBLISH_COMPLETE') {
                    return $statusResponse->json()['data'];
                } elseif ($status === 'FAILED') {
                    throw new \Exception('Video publishing failed');
                }
            }

            sleep(2);
            $attempts++;
        }

        throw new \Exception('Video publishing timeout');
    }

    public function deletePost(string $postId): bool
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $response = Http::withToken($this->integration->access_token)
            ->delete(self::BASE_URL . '/v2/post/publish/video/delete/', [
                'video_id' => $postId,
            ]);

        return $response->successful();
    }

    public function getPostAnalytics(string $postId): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $response = Http::withToken($this->integration->access_token)
            ->get(self::BASE_URL . '/v2/video/query/', [
                'filters' => [
                    'video_ids' => [$postId],
                ],
                'fields' => [
                    'id',
                    'create_time',
                    'cover_image_url',
                    'share_url',
                    'video_description',
                    'duration',
                    'height',
                    'width',
                    'title',
                    'embed_html',
                    'embed_link',
                    'like_count',
                    'comment_count',
                    'share_count',
                    'view_count',
                ],
            ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json()['data']['videos'][0] ?? [];
    }

    public function getUserVideos(int $maxCount = 20): array
    {
        if (!$this->integration) {
            throw new \Exception('No integration configured');
        }

        $response = Http::withToken($this->integration->access_token)
            ->post(self::BASE_URL . '/v2/video/list/', [
                'max_count' => $maxCount,
                'fields' => [
                    'id',
                    'create_time',
                    'cover_image_url',
                    'share_url',
                    'video_description',
                    'like_count',
                    'comment_count',
                    'share_count',
                    'view_count',
                ],
            ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json()['data']['videos'] ?? [];
    }

    public function refreshToken(): array
    {
        if (!$this->integration || !$this->integration->refresh_token) {
            throw new \Exception('No refresh token available');
        }

        $response = Http::asForm()->post(self::BASE_URL . '/v2/oauth/token/', [
            'client_key' => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->integration->refresh_token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh token: ' . $response->body());
        }

        return $response->json()['data'] ?? [];
    }

    public function validateToken(): bool
    {
        if (!$this->integration) {
            return false;
        }

        try {
            $this->getUserInfo($this->integration->access_token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function revokeToken(): bool
    {
        if (!$this->integration) {
            return false;
        }

        $response = Http::asForm()->post(self::BASE_URL . '/v2/oauth/revoke/', [
            'client_key' => config('services.tiktok.client_key'),
            'client_secret' => config('services.tiktok.client_secret'),
            'token' => $this->integration->access_token,
        ]);

        return $response->successful();
    }
}
