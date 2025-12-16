<?php

declare(strict_types=1);

namespace App\Domain\Payment\DTOs;

class DisbursementRequest
{
    public function __construct(
        public readonly string $phoneNumber,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $provider,
        public readonly ?string $reference = null,
        public readonly ?string $description = null,
        public readonly ?string $recipientName = null,
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
            recipientName: $data['recipient_name'] ?? null,
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
            'recipient_name' => $this->recipientName,
            'metadata' => $this->metadata,
        ];
    }
}
