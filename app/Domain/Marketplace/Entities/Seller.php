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
        public readonly int $completedOrders,
        public readonly int $totalSalesAmount,
        public readonly float $disputeRate,
        public readonly float $cancellationRate,
        public readonly float $rating,
        public readonly bool $isActive,
        public readonly ?float $commissionRate,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $description,
        public readonly ?string $logoPath,
        public readonly ?string $coverImagePath,
        public readonly ?string $kycRejectionReason,
        public readonly ?\DateTimeImmutable $tierCalculatedAt,
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

    public function getEffectiveCommissionRate(array $tierRates = []): float
    {
        if ($this->commissionRate !== null && $this->commissionRate > 0) {
            return $this->commissionRate;
        }

        return $tierRates[$this->trustLevel->value()] ?? 10.0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'business_name' => $this->businessName,
            'business_type' => $this->businessType,
            'province' => $this->province,
            'district' => $this->district,
            'trust_level' => $this->trustLevel->value(),
            'kyc_status' => $this->kycStatus->value(),
            'kyc_documents' => $this->kycDocuments,
            'total_orders' => $this->totalOrders,
            'completed_orders' => $this->completedOrders,
            'total_sales_amount' => $this->totalSalesAmount,
            'dispute_rate' => $this->disputeRate,
            'cancellation_rate' => $this->cancellationRate,
            'rating' => $this->rating,
            'is_active' => $this->isActive,
            'commission_rate' => $this->commissionRate,
            'phone' => $this->phone,
            'email' => $this->email,
            'description' => $this->description,
            'logo_path' => $this->logoPath,
            'cover_image_path' => $this->coverImagePath,
            'kyc_rejection_reason' => $this->kycRejectionReason,
            'tier_calculated_at' => $this->tierCalculatedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
