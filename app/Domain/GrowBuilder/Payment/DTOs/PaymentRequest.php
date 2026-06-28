<?php

namespace App\Domain\GrowBuilder\Payment\DTOs;

class PaymentRequest
{
    public function __construct(
        public readonly string $amount,
        public readonly string $currency,
        public readonly string $phoneNumber,
        public readonly string $reference,
        public readonly string $description,
        public readonly ?string $customerName = null,
        public readonly ?string $customerEmail = null,
        public readonly ?array $metadata = null,
        public readonly ?string $callbackUrl = null,
        public readonly ?string $returnUrl = null,
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'phone_number' => $this->phoneNumber,
            'reference' => $this->reference,
            'description' => $this->description,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'metadata' => $this->metadata,
            'callback_url' => $this->callbackUrl,
            'return_url' => $this->returnUrl,
        ];
    }
}
