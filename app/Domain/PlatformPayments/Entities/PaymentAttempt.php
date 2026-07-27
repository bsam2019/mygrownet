<?php

namespace App\Domain\PlatformPayments\Entities;

class PaymentAttempt
{
    private function __construct(
        private readonly ?int $id,
        private int $transactionId,
        private int $attemptNumber,
        private string $status,
        private ?string $providerResponse,
        private ?string $errorMessage,
        private ?\DateTimeImmutable $scheduledAt,
        private \DateTimeImmutable $attemptedAt,
        private ?\DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        int $transactionId,
        int $attemptNumber,
        \DateTimeImmutable $scheduledAt,
    ): self {
        return new self(
            id: null,
            transactionId: $transactionId,
            attemptNumber: $attemptNumber,
            status: 'pending',
            providerResponse: null,
            errorMessage: null,
            scheduledAt: $scheduledAt,
            attemptedAt: new \DateTimeImmutable(),
            createdAt: new \DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int $id,
        int $transactionId,
        int $attemptNumber,
        string $status,
        ?string $providerResponse,
        ?string $errorMessage,
        ?\DateTimeImmutable $scheduledAt,
        \DateTimeImmutable $attemptedAt,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            transactionId: $transactionId,
            attemptNumber: $attemptNumber,
            status: $status,
            providerResponse: $providerResponse,
            errorMessage: $errorMessage,
            scheduledAt: $scheduledAt,
            attemptedAt: $attemptedAt,
            createdAt: $createdAt,
        );
    }

    public function markSuccess(array $response): void
    {
        $this->status = 'success';
        $this->providerResponse = json_encode($response);
    }

    public function markFailed(string $error): void
    {
        $this->status = 'failed';
        $this->errorMessage = $error;
    }

    public function id(): ?int { return $this->id; }
    public function transactionId(): int { return $this->transactionId; }
    public function attemptNumber(): int { return $this->attemptNumber; }
    public function status(): string { return $this->status; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transactionId,
            'attempt_number' => $this->attemptNumber,
            'status' => $this->status,
            'error_message' => $this->errorMessage,
            'attempted_at' => $this->attemptedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
