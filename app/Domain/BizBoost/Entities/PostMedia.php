<?php

namespace App\Domain\BizBoost\Entities;

class PostMedia
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $postId,
        public readonly string $type,
        public readonly string $path,
        public readonly ?string $filename,
        public readonly ?int $fileSize,
        public readonly ?string $mimeType,
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly ?int $duration,
        public readonly ?string $thumbnailPath,
        public readonly ?int $sortOrder,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            postId: (int) $data['post_id'],
            type: $data['type'],
            path: $data['path'],
            filename: $data['filename'] ?? null,
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null,
            mimeType: $data['mime_type'] ?? null,
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            duration: isset($data['duration']) ? (int) $data['duration'] : null,
            thumbnailPath: $data['thumbnail_path'] ?? null,
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->postId,
            'type' => $this->type,
            'path' => $this->path,
            'filename' => $this->filename,
            'file_size' => $this->fileSize,
            'mime_type' => $this->mimeType,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'thumbnail_path' => $this->thumbnailPath,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}