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
    protected function createDirectUpload(array $metadata): array
    {
        $response = Http::withHeaders($this->headers())
            ->asMultipart()
            ->post($this->baseUrl().'/direct_upload', $this->directUploadFormData($metadata));

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
            'maxDurationSeconds' => (string) ($metadata['max_duration_seconds'] ?? 3600),
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
