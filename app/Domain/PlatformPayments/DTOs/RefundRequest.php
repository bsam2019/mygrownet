<?php

namespace App\Domain\PlatformPayments\DTOs;

class RefundRequest
{
    public function __construct(
        public readonly string $transactionReference,
        public readonly string $amount,
        public readonly string $reason,
        public readonly ?array $metadata = null,
    ) {}

    public function toArray(): array
    {
        return [
            'transaction_reference' => $this->transactionReference,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
        ];
    }
}
