<?php

namespace App\Domain\ProfitSharing\Entities;

use DateTimeImmutable;

class MemberProfitShare
{
    private function __construct(
        private ?int $id,
        private int $quarterlyProfitShareId,
        private int $userId,
        private string $professionalLevel,
        private float $levelMultiplier,
        private ?float $memberBp,
        private float $shareAmount,
        private string $status,
        private ?DateTimeImmutable $paidAt,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $quarterlyProfitShareId,
        int $userId,
        string $professionalLevel,
        float $levelMultiplier,
        ?float $memberBp,
        float $shareAmount
    ): self {
        return new self(
            id: null,
            quarterlyProfitShareId: $quarterlyProfitShareId,
            userId: $userId,
            professionalLevel: $professionalLevel,
            levelMultiplier: $levelMultiplier,
            memberBp: $memberBp,
            shareAmount: $shareAmount,
            status: 'pending',
            paidAt: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function markAsPaid(): void
    {
        if ($this->status === 'paid') {
            throw new \DomainException('Profit share already marked as paid');
        }

        $this->status = 'paid';
        $this->paidAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function id(): ?int { return $this->id; }
    public function quarterlyProfitShareId(): int { return $this->quarterlyProfitShareId; }
    public function userId(): int { return $this->userId; }
    public function professionalLevel(): string { return $this->professionalLevel; }
    public function levelMultiplier(): float { return $this->levelMultiplier; }
    public function memberBp(): ?float { return $this->memberBp; }
    public function shareAmount(): float { return $this->shareAmount; }
    public function status(): string { return $this->status; }
    public function paidAt(): ?DateTimeImmutable { return $this->paidAt; }
}
