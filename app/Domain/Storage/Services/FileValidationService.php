<?php

namespace App\Domain\Storage\Services;

use App\Domain\Storage\ValueObjects\FileSize;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;

class FileValidationService
{
    public function validateUpload(
        string $filename,
        FileSize $fileSize,
        string $mimeType,
        StoragePlan $plan
    ): array {
        $errors = [];

        // Check file size
        $maxSize = FileSize::fromBytes($plan->max_file_size_bytes);
        if ($fileSize->isGreaterThan($maxSize)) {
            $errors[] = "File exceeds maximum size of {$maxSize->format()}";
        }

        // Check MIME type if restricted
        if ($plan->allowed_mime_types) {
            $allowed = json_decode($plan->allowed_mime_types, true);
            if (!$this->isMimeTypeAllowed($mimeType, $allowed)) {
                $errors[] = "File type not allowed";
            }
        }

        // Check filename
        if (!$this->isValidFilename($filename)) {
            $errors[] = "Invalid filename";
        }

        return $errors;
    }

    private function isMimeTypeAllowed(string $mimeType, array $allowed): bool
    {
        foreach ($allowed as $pattern) {
            if (fnmatch($pattern, $mimeType)) {
                return true;
            }
        }
        return false;
    }

    private function isValidFilename(string $filename): bool
    {
        // Reject path traversal attempts
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return false;
        }

        // Reject empty filename
        if (empty(trim($filename))) {
            return false;
        }

        return true;
    }
}
