<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareStreamService
{
    protected string $accountId;
    protected string $apiToken;

    public function __construct()
    {
        $this->accountId = (string) (config('growstream.providers.cloudflare.account_id') ?? env('CLOUDFLARE_ACCOUNT_ID', ''));
        $this->apiToken = (string) (config('growstream.providers.cloudflare.api_token') ?? env('CLOUDFLARE_API_TOKEN', ''));
    }

    /**
     * Create a TUS resumable upload session for direct-to-Cloudflare stream chunked uploads
     */
    public function createTusUpload(int $fileSize, string $title): ?array
    {
        if (empty($this->accountId) || empty($this->apiToken)) {
            return null;
        }

        try {
            $expiry = now()->addDay()->toIso8601String();
            $uploadMetadata = 'maxDurationSeconds '.base64_encode('3600')
                .',expiry '.base64_encode($expiry)
                .',name '.base64_encode($title);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiToken,
                'Tus-Resumable' => '1.0.0',
                'Upload-Length' => (string) $fileSize,
                'Upload-Metadata' => $uploadMetadata,
            ])->post("https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream?direct_user=true");

            if ($response->successful()) {
                $uploadUrl = (string) $response->header('Location');
                $urlWithoutQuery = parse_url($uploadUrl, PHP_URL_PATH) ?: $uploadUrl;
                $segments = explode('/', rtrim($urlWithoutQuery, '/'));
                $uid = (string) end($segments);

                return [
                    'upload_url' => $uploadUrl,
                    'stream_uid' => $uid,
                ];
            }

            Log::warning('CloudflareStreamService TUS init response error: HTTP ' . $response->status() . ' - ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('CloudflareStreamService TUS init exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Direct upload video file to Cloudflare Stream with timeout protection
     */
    public function upload(UploadedFile $file, string $title): ?string
    {
        if (empty($this->accountId) || empty($this->apiToken)) {
            return null;
        }

        $stream = fopen($file->getRealPath(), 'rb');
        if (!$stream) {
            return null;
        }

        try {
            $response = Http::timeout(300)
                ->connectTimeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ])->attach(
                    'file',
                    $stream,
                    $file->getClientOriginalName()
                )->post("https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream", [
                    'meta' => json_encode(['name' => $title]),
                    'maxDurationSeconds' => 3600,
                ]);

            if ($response->successful()) {
                $result = $response->json('result') ?? [];
                return $result['uid'] ?? null;
            }

            Log::warning('CloudflareStreamService upload response error: HTTP ' . $response->status() . ' - ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('CloudflareStreamService upload exception: ' . $e->getMessage());
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return null;
    }
}
