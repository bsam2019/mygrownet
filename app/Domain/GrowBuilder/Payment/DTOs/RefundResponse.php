<?php

namespace App\Domain\GrowBuilder\Payment\DTOs;

class RefundResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $refundReference,
        public readonly ?string $message = null,
        public readonly ?array $rawResponse = null,
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'refund_reference' => $this->refundReference,
            'message' => $this->message,
            'raw_response' => $this->rawResponse,
        ];
    }
}
