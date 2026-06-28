<?php

namespace App\Domain\GrowBuilder\Payment\DTOs;

use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;

class PaymentResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly PaymentStatus $status,
        public readonly string $transactionReference,
        public readonly ?string $externalReference = null,
        public readonly ?string $message = null,
        public readonly ?array $rawResponse = null,
        public readonly ?string $checkoutUrl = null,
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'status' => $this->status->value,
            'transaction_reference' => $this->transactionReference,
            'external_reference' => $this->externalReference,
            'message' => $this->message,
            'raw_response' => $this->rawResponse,
            'checkout_url' => $this->checkoutUrl,
        ];
    }
}
