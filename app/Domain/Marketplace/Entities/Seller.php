<?php

namespace App\Domain\Marketplace\Entities;

use App\Domain\Marketplace\ValueObjects\TrustLevel;
use App\Domain\Marketplace\ValueObjects\KycStatus;

class Seller
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $businessName,
        public readonly string $businessType,
        public readonly string $province,
        public readonly string $district,
        public readonly TrustLevel $trustLevel,
        public readonly KycStatus $kycStatus,
        public readonly array $kycDocuments,
        public readonly int $totalOrders,
        public readonly float $rating,
        public readonly bool $isActive,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $description,
        public readonly ?string $logoPath,
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {}

    public function canAcceptOrders(): bool
    {
        return $this->isActive && $this->kycStatus->isApproved();
    }

    public function canUpgradeTrustLevel(): bool
    {
        return match ($this->trustLevel->value()) {
            'new' => $this->kycStatus->isApproved() && $this->totalOrders >= 5,
            'verified' => $this->totalOrders >= 50 && $this->rating >= 4.5,
            'trusted' => $this->totalOrders >= 200 && $this->rating >= 4.8,
            default => false,
        };
    }

    public function getNextTrustLevel(): ?TrustLevel
    {
        if (!$this->canUpgradeTrustLevel()) {
            return null;
        }

        return match ($this->trustLevel->value()) {
            'new' => TrustLevel::verified(),
            'verified' => TrustLevel::trusted(),
            'trusted' => TrustLevel::top(),
            default => null,
        };
    }
}
