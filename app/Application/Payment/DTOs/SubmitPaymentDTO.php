<?php

namespace App\Application\Payment\DTOs;

class SubmitPaymentDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly float $amount,
        public readonly string $paymentMethod,
        public readonly string $paymentReference,
        public readonly string $phoneNumber,
        public readonly string $paymentType,
        public readonly ?string $notes = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            amount: (float) $data['amount'],
            paymentMethod: $data['payment_method'],
            paymentReference: $data['payment_reference'],
            phoneNumber: $data['phone_number'],
            paymentType: $data['payment_type'],
            notes: $data['notes'] ?? null
        );
    }
}
