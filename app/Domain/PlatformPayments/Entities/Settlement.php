<?php

namespace App\Domain\PlatformPayments\Entities;

class Settlement
{
    private function __construct(
        private readonly ?int $id,
        private int $organizationId,
        private string $provider,
        private string $providerSettlementId,
        private float $expectedAmount,
        private float $actualAmount,
        private float $feeAmount,
        private string $currency,
        private string $status,
        private ?\DateTimeImmutable $settlementDate,
        private ?\DateTimeImmutable $reconciledAt,
        private ?string $discrepancyNotes,
        private \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $organizationId,
        string $provider,
        string $providerSettlementId,
        float $expectedAmount,
        float $actualAmount,
        float $feeAmount,
        string $currency,
        \DateTimeImmutable $settlementDate,
    ): self {
        $status = abs($expectedAmount - $actualAmount) < 0.01 ? 'matched' : 'discrepancy';
        return new self(
            id: null,
            organizationId: $organizationId,
            provider: $provider,
            providerSettlementId: $providerSettlementId,
            expectedAmount: $expectedAmount,
            actualAmount: $actualAmount,
            feeAmount: $feeAmount,
            currency: $currency,
            status: $status,
            settlementDate: $settlementDate,
            reconciledAt: null,
            discrepancyNotes: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: null,
        );
    }

    public static function reconstitute(
        int $id,
        int $organizationId,
        string $provider,
        string $providerSettlementId,
        float $expectedAmount,
        float $actualAmount,
        float $feeAmount,
        string $currency,
        string $status,
        ?\DateTimeImmutable $settlementDate,
        ?\DateTimeImmutable $reconciledAt,
        ?string $discrepancyNotes,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            organizationId: $organizationId,
            provider: $provider,
            providerSettlementId: $providerSettlementId,
            expectedAmount: $expectedAmount,
            actualAmount: $actualAmount,
            feeAmount: $feeAmount,
            currency: $currency,
            status: $status,
            settlementDate: $settlementDate,
            reconciledAt: $reconciledAt,
            discrepancyNotes: $discrepancyNotes,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function reconcile(): void
    {
        $this->status = 'reconciled';
        $this->reconciledAt = new \DateTimeImmutable();
    }

    public function flagDiscrepancy(string $notes): void
    {
        $this->status = 'discrepancy';
        $this->discrepancyNotes = $notes;
    }

    public function id(): ?int { return $this->id; }
    public function expectedAmount(): float { return $this->expectedAmount; }
    public function actualAmount(): float { return $this->actualAmount; }
    public function status(): string { return $this->status; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organizationId,
            'provider' => $this->provider,
            'provider_settlement_id' => $this->providerSettlementId,
            'expected_amount' => $this->expectedAmount,
            'actual_amount' => $this->actualAmount,
            'fee_amount' => $this->feeAmount,
            'currency' => $this->currency,
            'status' => $this->status,
            'settlement_date' => $this->settlementDate?->format(\DateTimeInterface::ATOM),
            'reconciled_at' => $this->reconciledAt?->format(\DateTimeInterface::ATOM),
            'discrepancy_notes' => $this->discrepancyNotes,
        ];
    }
}
