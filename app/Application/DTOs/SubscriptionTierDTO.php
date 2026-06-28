<?php

namespace App\Application\DTOs;

class SubscriptionTierDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $monthlyPrice,
        public readonly float $annualPrice,
        public readonly string $currency,
        public readonly array $features,
        public readonly ?string $description = null,
        public readonly bool $isPopular = false
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            monthlyPrice: $data['monthly_price'],
            annualPrice: $data['annual_price'],
            currency: $data['currency'] ?? 'ZMW',
            features: $data['features'] ?? [],
            description: $data['description'] ?? null,
            isPopular: $data['is_popular'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'monthly_price' => $this->monthlyPrice,
            'annual_price' => $this->annualPrice,
            'currency' => $this->currency,
            'features' => $this->features,
            'description' => $this->description,
            'is_popular' => $this->isPopular,
        ];
    }
}
