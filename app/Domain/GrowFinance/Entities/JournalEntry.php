<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class JournalEntry
{
    public readonly ?int $id;
    public readonly int $businessId;
    public readonly ?string $entryNumber;
    public readonly ?DateTimeImmutable $entryDate;
    public readonly ?string $description;
    public readonly ?string $reference;
    public readonly bool $isPosted;
    public readonly ?int $createdBy;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $businessId,
        ?string $entryNumber,
        ?DateTimeImmutable $entryDate,
        ?string $description,
        ?string $reference,
        bool $isPosted,
        ?int $createdBy,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->businessId = $businessId;
        $this->entryNumber = $entryNumber;
        $this->entryDate = $entryDate;
        $this->description = $description;
        $this->reference = $reference;
        $this->isPosted = $isPosted;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: $data['business_id'],
            entryNumber: $data['entry_number'] ?? null,
            entryDate: isset($data['entry_date']) ? new DateTimeImmutable($data['entry_date']) : null,
            description: $data['description'] ?? null,
            reference: $data['reference'] ?? null,
            isPosted: (bool) ($data['is_posted'] ?? false),
            createdBy: $data['created_by'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function isBalanced(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'entry_number' => $this->entryNumber,
            'entry_date' => $this->entryDate?->format('Y-m-d'),
            'description' => $this->description,
            'reference' => $this->reference,
            'is_posted' => $this->isPosted,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
