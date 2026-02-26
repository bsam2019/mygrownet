<?php

namespace App\Infrastructure\Storage\S3;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3StorageService
{
    public function generatePresignedUploadUrl(
        string $s3Key,
        string $mimeType,
        int $expiresIn = 900
    ): string {
        $client = Storage::disk('s3')->getClient();
        $command = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $s3Key,
            'ContentType' => $mimeType,
        ]);

        $request = $client->createPresignedRequest($command, "+{$expiresIn} seconds");
        $url = (string) $request->getUri();
        
        \Log::info('Generated presigned URL', [
            's3_key' => $s3Key,
            'url' => $url,
            'bucket' => config('filesystems.disks.s3.bucket'),
            'endpoint' => config('filesystems.disks.s3.endpoint'),
        ]);
        
        return $url;
    }

    public function generatePresignedDownloadUrl(
        string $s3Key,
        int $expiresIn = 900
    ): string {
        return Storage::disk('s3')->temporaryUrl($s3Key, now()->addSeconds($expiresIn));
    }

    public function fileExists(string $s3Key): bool
    {
        return Storage::disk('s3')->exists($s3Key);
    }

    public function getFileSize(string $s3Key): int
    {
        return Storage::disk('s3')->size($s3Key);
    }

    public function deleteFile(string $s3Key): bool
    {
        return Storage::disk('s3')->delete($s3Key);
    }

    public function generateS3Key(int $userId, string $filename): string
    {
        $uuid = Str::uuid();
        $sanitized = $this->sanitizeFilename($filename);
        return "storage/{$userId}/{$uuid}_{$sanitized}";
    }

    private function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts and special characters
        $filename = basename($filename);
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }

    public function getBucket(): string
    {
        return config('filesystems.disks.s3.bucket');
    }
}
