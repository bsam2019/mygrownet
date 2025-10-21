<?php

namespace App\Domain\Payment\Entities;

use App\Domain\Payment\ValueObjects\PaymentAmount;
use App\Domain\Payment\ValueObjects\PaymentMethod;
use App\Domain\Payment\ValueObjects\PaymentStatus;
use App\Domain\Payment\ValueObjects\PaymentType;
use DateTimeImmutable;

class MemberPayment
{
    public function __construct(
        private readonly int $id,
        private readonly int $userId,
        private PaymentAmount $amount,
        private PaymentMethod $paymentMethod,
        private readonly string $paymentReference,
        private readonly string $phoneNumber,
        private readonly string $accountName,
        private PaymentType $paymentType,
        private ?string $notes,
        private PaymentStatus $status,
        private ?string $adminNotes,
        private ?int $verifiedBy,
        private ?DateTimeImmutable $verifiedAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $userId,
        PaymentAmount $amount,
        PaymentMethod $paymentMethod,
        string $paymentReference,
        string $phoneNumber,
        PaymentType $paymentType,
        ?string $notes = null
    ): self {
        return new self(
            id: 0, // Will be set by repository
            userId: $userId,
            amount: $amount,
            paymentMethod: $paymentMethod,
            paymentReference: $paymentReference,
            phoneNumber: $phoneNumber,
            accountName: '', // Not needed - can be verified from mobile money message
            paymentType: $paymentType,
            notes: $notes,
            status: PaymentStatus::PENDING,
            adminNotes: null,
            verifiedBy: null,
            verifiedAt: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function verify(int $adminId, ?string $adminNotes = null): void
    {
        if ($this->status->isVerified()) {
            throw new \DomainException('Payment is already verified');
        }

        $this->status = PaymentStatus::VERIFIED;
        $this->verifiedBy = $adminId;
        $this->verifiedAt = new DateTimeImmutable();
        $this->adminNotes = $adminNotes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reject(int $adminId, string $reason): void
    {
        if ($this->status->isVerified()) {
            throw new \DomainException('Cannot reject a verified payment');
        }

        $this->status = PaymentStatus::REJECTED;
        $this->verifiedBy = $adminId;
        $this->adminNotes = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function resetToPending(int $adminId, string $reason): void
    {
        $this->status = PaymentStatus::PENDING;
        $this->verifiedBy = null;
        $this->verifiedAt = null;
        $this->adminNotes = "Reset by admin: " . $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function canBeModified(): bool
    {
        return $this->status->isPending();
    }

    // Getters
    public function id(): int { return $this->id; }
    public function userId(): int { return $this->userId; }
    public function amount(): PaymentAmount { return $this->amount; }
    public function paymentMethod(): PaymentMethod { return $this->paymentMethod; }
    public function paymentReference(): string { return $this->paymentReference; }
    public function phoneNumber(): string { return $this->phoneNumber; }
    public function accountName(): string { return $this->accountName; }
    public function paymentType(): PaymentType { return $this->paymentType; }
    public function notes(): ?string { return $this->notes; }
    public function status(): PaymentStatus { return $this->status; }
    public function adminNotes(): ?string { return $this->adminNotes; }
    public function verifiedBy(): ?int { return $this->verifiedBy; }
    public function verifiedAt(): ?DateTimeImmutable { return $this->verifiedAt; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
}
