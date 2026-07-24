<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class Profile
{
    public readonly ?int $id;
    public readonly int $userId;
    public readonly ?string $businessName;
    public readonly ?string $accountNumber;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $userId,
        ?string $businessName,
        ?string $accountNumber,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->businessName = $businessName;
        $this->accountNumber = $accountNumber;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            businessName: $data['business_name'] ?? null,
            accountNumber: $data['account_number'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'business_name' => $this->businessName,
            'account_number' => $this->accountNumber,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
