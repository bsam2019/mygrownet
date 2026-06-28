<?php

namespace App\Domain\Storage\ValueObjects;

use Illuminate\Support\Str;

class S3Path
{
    private function __construct(
        private string $bucket,
        private string $key
    ) {
        $this->validate();
    }

    public static function create(string $bucket, string $key): self
    {
        return new self($bucket, $key);
    }

    public static function forUser(int $userId, string $filename, string $bucket, ?string $folderPath = null): self
    {
        $uuid = Str::uuid();
        $sanitized = self::sanitizeFilename($filename);
        
        // Use date-based organization for better searchability
        $year = date('Y');
        $month = date('m');
        
        // Build path: users/user-{id}/{year}/{month}/{folder}/{filename}
        $basePath = "users/user-{$userId}/{$year}/{$month}";
        
        if ($folderPath) {
            $key = "{$basePath}/{$folderPath}/{$sanitized}";
        } else {
            $key = "{$basePath}/{$sanitized}";
        }
        
        // Store UUID in metadata instead of filename for deduplication
        // The UUID is still tracked in database for uniqueness

        return new self($bucket, $key);
    }

    private function validate(): void
    {
        if (empty($this->bucket)) {
            throw new \InvalidArgumentException('Bucket cannot be empty');
        }

        if (empty($this->key)) {
            throw new \InvalidArgumentException('Key cannot be empty');
        }

        // Prevent path traversal
        if (str_contains($this->key, '..')) {
            throw new \InvalidArgumentException('Invalid key: path traversal detected');
        }
    }

    private static function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getFullPath(): string
    {
        return "{$this->bucket}/{$this->key}";
    }

    public function __toString(): string
    {
        return $this->getFullPath();
    }
}
