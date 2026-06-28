<?php

namespace App\Domain\GrowBuilder\Payment\DTOs;

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
