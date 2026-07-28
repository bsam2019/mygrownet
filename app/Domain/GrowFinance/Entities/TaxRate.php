<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\TaxType;
use DateTimeImmutable;

class TaxRate
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly TaxType $taxType,
        public readonly float $rate,
        public readonly DateTimeImmutable $effectiveFrom,
        public readonly ?DateTimeImmutable $effectiveTo = null,
        public readonly string $jurisdiction = 'ZM',
        public readonly ?string $accountCode = null,
        public readonly ?string $glCode = null,
        public readonly bool $isDefault = false,
        public readonly ?string $notes = null,
        public readonly bool $isActive = true,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isEffectiveFor(DateTimeImmutable $date): bool
    {
        if ($date < $this->effectiveFrom) return false;
        if ($this->effectiveTo && $date > $this->effectiveTo) return false;
        return true;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            taxType: TaxType::from($data['tax_type']),
            rate: (float) ($data['rate'] ?? 0),
            effectiveFrom: new DateTimeImmutable($data['effective_from']),
            effectiveTo: isset($data['effective_to']) ? new DateTimeImmutable($data['effective_to']) : null,
            jurisdiction: $data['jurisdiction'] ?? 'ZM',
            accountCode: $data['account_code'] ?? null,
            glCode: $data['gl_code'] ?? null,
            isDefault: (bool) ($data['is_default'] ?? false),
            notes: $data['notes'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'tax_type' => $this->taxType->value,
            'rate' => $this->rate,
            'effective_from' => $this->effectiveFrom->format('Y-m-d'),
            'effective_to' => $this->effectiveTo?->format('Y-m-d'),
            'jurisdiction' => $this->jurisdiction,
            'account_code' => $this->accountCode,
            'gl_code' => $this->glCode,
            'is_default' => $this->isDefault,
            'notes' => $this->notes,
            'is_active' => $this->isActive,
        ];
    }
}
