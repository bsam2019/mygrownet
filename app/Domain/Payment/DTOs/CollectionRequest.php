<?php

declare(strict_types=1);

namespace App\Domain\Payment\DTOs;

class CollectionRequest
{
    public function __construct(
        public readonly string $phoneNumber,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $provider,
        public readonly ?string $reference = null,
        public readonly ?string $description = null,
        public readonly ?string $customerName = null,
        public readonly ?string $customerEmail = null,
        public readonly array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            phoneNumber: $data['phone_number'],
            amount: (float) $data['amount'],
            currency: $data['currency'] ?? 'ZMW',
            provider: $data['provider'],
            reference: $data['reference'] ?? null,
            description: $data['description'] ?? null,
            customerName: $data['customer_name'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'phone_number' => $this->phoneNumber,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'provider' => $this->provider,
            'reference' => $this->reference,
            'description' => $this->description,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'metadata' => $this->metadata,
        ];
    }
}
