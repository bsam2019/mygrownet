<?php

namespace App\Application\BizDocs\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class FileStorageService
{
    private string $disk;

    public function __construct()
    {
        // Use the same S3 disk as GrowStorage (configured for DigitalOcean Spaces)
        // Fall back to public disk if S3 is not configured
        $this->disk = $this->getConfiguredDisk();
    }

    /**
     * Determine which disk to use based on configuration
     */
    private function getConfiguredDisk(): string
    {
        // Check if S3 (DigitalOcean Spaces) is configured
        $s3Key = config('filesystems.disks.s3.key');
        $s3Bucket = config('filesystems.disks.s3.bucket');
        
        if ($s3Key && $s3Bucket) {
            return 's3';
        }

        // Fall back to public disk
        return 'public';
    }

    /**
     * Upload a file to storage
     *
     * @param UploadedFile $file
     * @param int $userId
     * @param string $type (logo, signature, stamp)
     * @return string The storage path
     */
    public function uploadBusinessFile(UploadedFile $file, int $userId, string $type): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = sprintf(
            '%s_%s_%s.%s',
            $type,
            $userId,
            Str::uuid(),
            $extension
        );

        $path = "bizdocs/business_profiles/{$userId}/{$filename}";

        // Store the file
        Storage::disk($this->disk)->putFileAs(
            "bizdocs/business_profiles/{$userId}",
            $file,
            $filename,
            'public'
        );

        return $path;
    }

    /**
     * Delete a file from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }

    /**
     * Get the public URL for a file
     *
     * @param string $path
     * @return string
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Check if a file exists
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get the storage disk being used
     *
     * @return string
     */
    public function getDisk(): string
    {
        return $this->disk;
    }
}

