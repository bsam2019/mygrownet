<?php

namespace App\Domain\BizBoost\Entities;

class ProductImage
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $productId,
        public readonly string $path,
        public readonly ?string $filename,
        public readonly ?int $fileSize,
        public readonly bool $isPrimary,
        public readonly ?int $sortOrder,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            productId: (int) $data['product_id'],
            path: $data['path'],
            filename: $data['filename'] ?? null,
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null,
            isPrimary: (bool) ($data['is_primary'] ?? false),
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->productId,
            'path' => $this->path,
            'filename' => $this->filename,
            'file_size' => $this->fileSize,
            'is_primary' => $this->isPrimary,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}