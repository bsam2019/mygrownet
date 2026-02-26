<?php

namespace App\Domain\Storage\Entities;

use App\Domain\Storage\ValueObjects\FileSize;
use App\Domain\Storage\ValueObjects\MimeType;
use App\Domain\Storage\ValueObjects\S3Path;

class StorageFile
{
    private function __construct(
        private string $id,
        private int $userId,
        private ?string $folderId,
        private string $originalName,
        private string $extension,
        private MimeType $mimeType,
        private FileSize $size,
        private S3Path $s3Path,
        private ?string $checksum = null
    ) {}

    public static function create(
        string $id,
        int $userId,
        ?string $folderId,
        string $originalName,
        string $mimeType,
        FileSize $size,
        S3Path $s3Path
    ): self {
        return new self(
            $id,
            $userId,
            $folderId,
            $originalName,
            pathinfo($originalName, PATHINFO_EXTENSION),
            MimeType::fromString($mimeType),
            $size,
            $s3Path
        );
    }

    public function rename(string $newName): void
    {
        if (empty($newName)) {
            throw new \DomainException('File name cannot be empty');
        }

        $this->originalName = $newName;
        $this->extension = pathinfo($newName, PATHINFO_EXTENSION);
    }

    public function moveTo(?string $folderId): void
    {
        $this->folderId = $folderId;
    }

    public function setChecksum(string $checksum): void
    {
        $this->checksum = $checksum;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->userId === $userId;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getFolderId(): ?string
    {
        return $this->folderId;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getMimeType(): MimeType
    {
        return $this->mimeType;
    }

    public function getSize(): FileSize
    {
        return $this->size;
    }

    public function getS3Path(): S3Path
    {
        return $this->s3Path;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }
}
