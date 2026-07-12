<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\DocumentId;
use App\Domain\PrimeEdge\ValueObjects\DocumentType;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use DateTimeImmutable;

class Document
{
    private function __construct(
        private readonly DocumentId $id,
        private readonly ClientId $clientId,
        private string $name,
        private DocumentType $type,
        private string $filePath,
        private string $mimeType,
        private int $fileSize,
        private ?string $engagementId,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        DocumentId $id,
        ClientId $clientId,
        string $name,
        DocumentType $type,
        string $filePath,
        string $mimeType,
        int $fileSize,
        ?string $engagementId = null,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            name: $name,
            type: $type,
            filePath: $filePath,
            mimeType: $mimeType,
            fileSize: $fileSize,
            engagementId: $engagementId,
            createdAt: new DateTimeImmutable(),
            updatedAt: null,
        );
    }

    public static function reconstitute(
        DocumentId $id,
        ClientId $clientId,
        string $name,
        DocumentType $type,
        string $filePath,
        string $mimeType,
        int $fileSize,
        ?string $engagementId,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            name: $name,
            type: $type,
            filePath: $filePath,
            mimeType: $mimeType,
            fileSize: $fileSize,
            engagementId: $engagementId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function rename(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): DocumentId { return $this->id; }
    public function clientId(): ClientId { return $this->clientId; }
    public function name(): string { return $this->name; }
    public function type(): DocumentType { return $this->type; }
    public function filePath(): string { return $this->filePath; }
    public function mimeType(): string { return $this->mimeType; }
    public function fileSize(): int { return $this->fileSize; }
    public function engagementId(): ?string { return $this->engagementId; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
