<?php

declare(strict_types=1);

namespace App\Domain\Payment\DTOs;

class CryptoPaymentRequest
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly ?string $reference = null,
        public readonly ?string $description = null,
        public readonly ?string $customerEmail = null,
        public readonly ?string $callbackUrl = null,
        public readonly array $metadata = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            amount: (float) $data['amount'],
            currency: $data['currency'] ?? 'USD',
            reference: $data['reference'] ?? null,
            description: $data['description'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            callbackUrl: $data['callback_url'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'description' => $this->description,
            'customer_email' => $this->customerEmail,
            'callback_url' => $this->callbackUrl,
            'metadata' => $this->metadata,
        ];
    }
}
