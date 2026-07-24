<?php

namespace App\Domain\ZamStay\Entities;

class Booking
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $propertyId,
        public readonly ?int $agentId,
        public readonly int $userId,
        public readonly \DateTimeImmutable $checkIn,
        public readonly \DateTimeImmutable $checkOut,
        public readonly float $totalPrice,
        public readonly string $status,
        public readonly int $guests,
        public readonly ?string $specialRequests,
        public readonly ?string $paymentMethod,
        public readonly ?string $paymentReference,
        public readonly ?string $paymentPhone,
        public readonly ?string $transactionId,
        public readonly ?\DateTimeImmutable $paidAt,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            propertyId: (int) $data['property_id'],
            agentId: isset($data['agent_id']) ? (int) $data['agent_id'] : null,
            userId: (int) $data['user_id'],
            checkIn: new \DateTimeImmutable($data['check_in']),
            checkOut: new \DateTimeImmutable($data['check_out']),
            totalPrice: (float) $data['total_price'],
            status: $data['status'],
            guests: (int) ($data['guests'] ?? 1),
            specialRequests: $data['special_requests'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            paymentReference: $data['payment_reference'] ?? null,
            paymentPhone: $data['payment_phone'] ?? null,
            transactionId: $data['transaction_id'] ?? null,
            paidAt: isset($data['paid_at']) ? new \DateTimeImmutable($data['paid_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->propertyId,
            'agent_id' => $this->agentId,
            'user_id' => $this->userId,
            'check_in' => $this->checkIn->format('Y-m-d'),
            'check_out' => $this->checkOut->format('Y-m-d'),
            'total_price' => $this->totalPrice,
            'status' => $this->status,
            'guests' => $this->guests,
            'special_requests' => $this->specialRequests,
            'payment_method' => $this->paymentMethod,
            'payment_reference' => $this->paymentReference,
            'payment_phone' => $this->paymentPhone,
            'transaction_id' => $this->transactionId,
            'paid_at' => $this->paidAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
