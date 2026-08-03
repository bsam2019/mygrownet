<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Infrastructure\Providers;

use App\Domain\GrowStream\Exceptions\UploadFailedException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class CloudflareStreamProvider implements VideoProviderInterface
{
    protected string $accountId;

    protected string $apiToken;

    protected ?string $customerSubdomain;

    protected ?string $signingKeyId;

    protected ?string $signingKey;

    public function __construct()
    {
        $config = config('growstream.providers.cloudflare');

        $this->accountId = (string) ($config['account_id'] ?? '');
        $this->apiToken = (string) ($config['api_token'] ?? '');
        $this->customerSubdomain = $config['customer_subdomain'] ?? null;
        $this->signingKeyId = $config['signing_key_id'] ?? null;
        $this->signingKey = $config['signing_key'] ?? null;
    }

    protected function baseUrl(): string
    {
        return "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream";
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->apiToken,
            'Accept' => 'application/json',
        ];
    }

    public function upload(UploadedFile $file, array $metadata = []): ProviderVideoResponse
    {
        $direct = $this->createDirectUpload($metadata);
        $uid = $direct['uid'];
        $uploadUrl = $direct['upload_url'];

        $stream = fopen($file->getRealPath() ?: '', 'rb');
        if ($stream === false) {
            throw UploadFailedException::withMessage('Cloudflare Stream upload failed: could not open file for streaming');
        }

        try {
            $response = Http::withBody($stream, $file->getMimeType() ?? 'application/octet-stream')
                ->put($uploadUrl);
        } finally {
            fclose($stream);
        }

        if (! $response->successful()) {
            $this->delete($uid);
            throw UploadFailedException::withMessage(
                'Cloudflare Stream upload failed: HTTP '.$response->status()
            );
        }

        return new ProviderVideoResponse(
            providerVideoId: $uid,
            playbackUrl: $this->getPlaybackUrl($uid),
            thumbnailUrl: $this->getThumbnailUrl($uid),
            duration: null,
            fileSize: $file->getSize(),
            status: 'processing',
            metadata: [
                'provider' => 'cloudflare',
                'upload_url' => $uploadUrl,
            ],
        );
    }

    public function getVideo(string $providerVideoId): ProviderVideoResponse
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl().'/'.$providerVideoId);

        if (! $response->successful()) {
            throw UploadFailedException::withMessage(
                "Cloudflare Stream video not found: {$providerVideoId} (HTTP {$response->status()})"
            );
        }

        $video = $response->json('result') ?? [];
        $state = (string) ($video['status']['state'] ?? 'queued');

        $status = match ($state) {
            'ready' => 'ready',
            'error' => 'failed',
            default => 'processing',
        };

        return new ProviderVideoResponse(
            providerVideoId: $providerVideoId,
            playbackUrl: $this->getPlaybackUrl($providerVideoId),
            thumbnailUrl: $this->getThumbnailUrl($providerVideoId),
            duration: isset($video['duration']) ? (int) round((float) $video['duration']) : null,
            fileSize: $video['size'] ?? null,
            resolution: null,
            status: $status,
            metadata: [
                'state' => $state,
                'ready_to_stream' => (bool) ($video['readyToStream'] ?? false),
            ],
        );
    }

    public function getPlaybackUrl(string $providerVideoId, bool $signed = true, int $expiresIn = 86400): string
    {
        $manifest = $this->getManifestUrl($providerVideoId);

        if (! $signed || ! $this->signingKey || ! $this->signingKeyId) {
            return $manifest;
        }

        return $this->signManifestUrl($manifest, $expiresIn);
    }

    public function delete(string $providerVideoId): bool
    {
        $response = Http::withHeaders($this->headers())
            ->delete($this->baseUrl().'/'.$providerVideoId);

        return $response->successful() || $response->status() === 404;
    }

    public function getUploadStatus(string $providerVideoId): string
    {
        try {
            return $this->getVideo($providerVideoId)->status;
        } catch (\Throwable $e) {
            return 'failed';
        }
    }

    public function getDirectUploadUrl(array $metadata = []): array
    {
        $direct = $this->createDirectUpload($metadata);

        return [
            'upload_url' => $direct['upload_url'],
            'provider_video_id' => $direct['uid'],
            'method' => 'PUT',
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ],
            'expires_at' => $direct['expires_at'] ?? null,
        ];
    }

    protected function getManifestUrl(string $providerVideoId): string
    {
        $host = $this->normalizedCustomerSubdomain();

        if ($host !== null) {
            return "https://{$host}/{$providerVideoId}/manifest/video.m3u8";
        }

        return "https://customer-{$this->accountId}.cloudflarestream.com/{$providerVideoId}/manifest/video.m3u8";
    }

    protected function getThumbnailUrl(string $providerVideoId): string
    {
        $host = $this->normalizedCustomerSubdomain();

        return "https://{$host}/{$providerVideoId}/thumbnails/thumbnail.jpg";
    }

    protected function normalizedCustomerSubdomain(): ?string
    {
        if (! $this->customerSubdomain) {
            return null;
        }

        if (str_contains($this->customerSubdomain, '.')) {
            return $this->customerSubdomain;
        }

        return $this->customerSubdomain.'.cloudflarestream.com';
    }

    protected function signManifestUrl(string $url, int $expiresIn): string
    {
        $expiry = now()->addSeconds($expiresIn)->getTimestamp();
        $signature = $this->generateSignature($expiry);

        return "{$url}?token={$expiry}-{$signature}";
    }

    protected function generateSignature(int $expiry): string
    {
        $raw = hash_hmac('sha256', (string) $expiry, (string) $this->signingKey, true);

        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    /**
     * Create a Cloudflare Stream direct upload, returning the one-time upload
     * URL and the video uid.
     *
     * @param  array<string, mixed>  $metadata
     * @return array{uid: string, upload_url: string, expires_at: ?string}
     */
    /**
     * Create a Cloudflare Stream tus upload session for a file of the given
     * size, returning the one-time resumable upload URL (from the Location
     * header) and the Cloudflare video uid.
     *
     * tus is required by Cloudflare for files over 200MB and recommended
     * otherwise for unreliable connections. The returned URL is then fed
     * directly to a tus client (chunked PATCH requests).
     *
     * @param  int  $fileSize  bytes
     * @param  array<string, mixed>  $metadata
     * @return array{uid: string, upload_url: string}
     */
    public function createTusUpload(int $fileSize, array $metadata = []): array
    {
        $expiry = now()->addDay()->toIso8601String();
        $maxDuration = (string) ($metadata['max_duration_seconds'] ?? 3600);

        $uploadMetadata = 'maxDurationSeconds '.base64_encode($maxDuration)
            .',expiry '.base64_encode($expiry);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiToken,
            'Tus-Resumable' => '1.0.0',
            'Upload-Length' => (string) $fileSize,
            'Upload-Metadata' => $uploadMetadata,
        ])->post($this->baseUrl().'?direct_user=true');

        if (! $response->successful()) {
            $error = $response->json('errors.0.message') ?? $response->body();
            throw UploadFailedException::withMessage("Unable to create Cloudflare Stream tus upload: {$error}");
        }

        $uploadUrl = (string) $response->header('Location');
        if ($uploadUrl === '') {
            throw UploadFailedException::withMessage('Cloudflare Stream tus upload returned no Location header');
        }

        // uid is the last path segment of the tus endpoint (e.g. .../upload/{uid})
        $segments = explode('/', rtrim($uploadUrl, '/'));
        $uid = (string) end($segments);

        return [
            'uid' => $uid,
            'upload_url' => $uploadUrl,
        ];
    }

    protected function createDirectUpload(array $metadata): array
    {
        $response = Http::withHeaders($this->headers())
            ->withBody(json_encode($this->directUploadFormData($metadata)), 'application/json')
            ->post($this->baseUrl().'/direct_upload');

        $this->assertSuccess($response, 'Unable to create Cloudflare Stream direct upload');

        $result = $response->json('result') ?? [];

        return [
            'uid' => (string) ($result['uid'] ?? ''),
            'upload_url' => (string) ($result['uploadURL'] ?? ''),
            'expires_at' => $result['expiry'] ?? null,
        ];
    }

    protected function directUploadFormData(array $metadata): array
    {
        return [
            'maxDurationSeconds' => (int) ($metadata['max_duration_seconds'] ?? 3600),
            'expiry' => now()->addHour()->toIso8601String(),
        ];
    }

    protected function assertSuccess(Response $response, string $message): void
    {
        if (! $response->successful()) {
            $error = $response->json('errors.0.message') ?? $response->body();
            throw UploadFailedException::withMessage("{$message}: {$error}");
        }
    }
}
