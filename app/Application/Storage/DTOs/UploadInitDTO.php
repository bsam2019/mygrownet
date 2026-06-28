<?php

namespace App\Application\Storage\DTOs;

class UploadInitDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $folderId,
        public readonly string $filename,
        public readonly int $sizeBytes,
        public readonly string $mimeType
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            $userId,
            $data['folder_id'] ?? null,
            $data['filename'],
            $data['size'],
            $data['mime_type']
        );
    }
}
