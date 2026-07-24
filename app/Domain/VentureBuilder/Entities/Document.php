<?php

namespace App\Domain\VentureBuilder\Entities;

use DateTimeImmutable;

class Document
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $ventureId,
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $type = null,
        public readonly string $filePath,
        public readonly ?string $fileName = null,
        public readonly ?string $fileType = null,
        public readonly ?int $fileSize = null,
        public readonly string $visibility,
        public readonly ?bool $isConfidential = null,
        public readonly ?int $downloadCount = null,
        public readonly int $uploadedBy,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function canBeAccessedBy(string $userRole): bool
    {
        return match ($this->visibility) {
            'public' => true,
            'investors_only' => in_array($userRole, ['investor', 'shareholder', 'admin'], true),
            'shareholders_only' => in_array($userRole, ['shareholder', 'admin'], true),
            'admin_only' => $userRole === 'admin',
            default => false,
        };
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->fileSize ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            ventureId: (int) $data['venture_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            type: $data['type'] ?? null,
            filePath: $data['file_path'],
            fileName: $data['file_name'] ?? null,
            fileType: $data['file_type'] ?? null,
            fileSize: array_key_exists('file_size', $data) ? (int) $data['file_size'] : null,
            visibility: $data['visibility'],
            isConfidential: isset($data['is_confidential']) ? (bool) $data['is_confidential'] : null,
            downloadCount: array_key_exists('download_count', $data) ? (int) $data['download_count'] : null,
            uploadedBy: (int) $data['uploaded_by'],
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'venture_id' => $this->ventureId,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'file_path' => $this->filePath,
            'file_name' => $this->fileName,
            'file_type' => $this->fileType,
            'file_size' => $this->fileSize,
            'visibility' => $this->visibility,
            'is_confidential' => $this->isConfidential,
            'download_count' => $this->downloadCount,
            'uploaded_by' => $this->uploadedBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
