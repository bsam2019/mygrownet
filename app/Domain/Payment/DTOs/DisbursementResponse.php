<?php

declare(strict_types=1);

namespace App\Domain\Payment\DTOs;

use App\Domain\Payment\Enums\TransactionStatus;

class DisbursementResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $transactionId,
        public readonly TransactionStatus $status,
        public readonly ?string $providerReference = null,
        public readonly ?string $message = null,
        public readonly array $rawResponse = [],
    ) {}

    public static function success(
        string $transactionId,
        TransactionStatus $status = TransactionStatus::PENDING,
        ?string $providerReference = null,
        array $rawResponse = []
    ): self {
        return new self(
            success: true,
            transactionId: $transactionId,
            status: $status,
            providerReference: $providerReference,
            rawResponse: $rawResponse,
        );
    }

    public static function failure(string $message, array $rawResponse = []): self
    {
        return new self(
            success: false,
            transactionId: '',
            status: TransactionStatus::FAILED,
            message: $message,
            rawResponse: $rawResponse,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'status' => $this->status->value,
            'provider_reference' => $this->providerReference,
            'message' => $this->message,
        ];
    }
}
