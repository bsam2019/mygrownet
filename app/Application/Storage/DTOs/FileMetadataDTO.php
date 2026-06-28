<?php

namespace App\Application\Storage\DTOs;

class FileMetadataDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $originalName,
        public readonly string $extension,
        public readonly string $mimeType,
        public readonly int $sizeBytes,
        public readonly string $formattedSize,
        public readonly ?string $folderId,
        public readonly \DateTimeInterface $createdAt,
        public readonly \DateTimeInterface $updatedAt
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->originalName,
            'extension' => $this->extension,
            'mime_type' => $this->mimeType,
            'size_bytes' => $this->sizeBytes,
            'formatted_size' => $this->formattedSize,
            'folder_id' => $this->folderId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
