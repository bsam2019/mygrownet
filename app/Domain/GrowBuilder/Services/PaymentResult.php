<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Services;

final class PaymentResult
{
    private function __construct(
        public readonly bool $success,
        public readonly string $status,
        public readonly ?string $transactionId,
        public readonly ?string $externalReference,
        public readonly ?string $message,
        public readonly array $rawResponse,
    ) {}

    public static function pending(string $transactionId, ?string $externalReference = null, array $rawResponse = []): self
    {
        return new self(
            success: false,
            status: 'pending',
            transactionId: $transactionId,
            externalReference: $externalReference,
            message: 'Payment initiated, awaiting confirmation',
            rawResponse: $rawResponse,
        );
    }

    public static function processing(string $transactionId, ?string $externalReference = null, array $rawResponse = []): self
    {
        return new self(
            success: false,
            status: 'processing',
            transactionId: $transactionId,
            externalReference: $externalReference,
            message: 'Payment is being processed',
            rawResponse: $rawResponse,
        );
    }

    public static function success(string $transactionId, ?string $externalReference = null, array $rawResponse = []): self
    {
        return new self(
            success: true,
            status: 'completed',
            transactionId: $transactionId,
            externalReference: $externalReference,
            message: 'Payment completed successfully',
            rawResponse: $rawResponse,
        );
    }

    public static function failed(string $message, ?string $transactionId = null, array $rawResponse = []): self
    {
        return new self(
            success: false,
            status: 'failed',
            transactionId: $transactionId,
            externalReference: null,
            message: $message,
            rawResponse: $rawResponse,
        );
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
