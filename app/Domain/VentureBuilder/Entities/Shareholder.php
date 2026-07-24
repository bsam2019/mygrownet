<?php

namespace App\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\ValueObjects\ShareholderStatus;
use DateTimeImmutable;

class Shareholder
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $ventureId,
        public readonly int $userId,
        public readonly ?int $investmentId = null,
        public readonly ?float $totalInvestment = null,
        public readonly ?float $sharesOwned = null,
        public readonly ?float $equityPercentage = null,
        public readonly ?string $certificateNumber = null,
        public readonly ?DateTimeImmutable $registrationDate = null,
        public readonly ?string $shareholderAgreementPath = null,
        public readonly ?bool $agreementSigned = null,
        public readonly ?DateTimeImmutable $agreementSignedAt = null,
        public readonly ShareholderStatus $status,
        public readonly ?float $totalDividendsReceived = null,
        public readonly ?DateTimeImmutable $lastDividendDate = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function hasSignedAgreement(): bool
    {
        return $this->agreementSigned ?? false;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            ventureId: (int) $data['venture_id'],
            userId: (int) $data['user_id'],
            investmentId: isset($data['investment_id']) ? (int) $data['investment_id'] : null,
            totalInvestment: array_key_exists('total_investment', $data) ? (float) $data['total_investment'] : null,
            sharesOwned: array_key_exists('shares_owned', $data) ? (float) $data['shares_owned'] : null,
            equityPercentage: array_key_exists('equity_percentage', $data) ? (float) $data['equity_percentage'] : null,
            certificateNumber: $data['certificate_number'] ?? null,
            registrationDate: isset($data['registration_date']) ? new \DateTimeImmutable($data['registration_date']) : null,
            shareholderAgreementPath: $data['shareholder_agreement_path'] ?? null,
            agreementSigned: isset($data['agreement_signed']) ? (bool) $data['agreement_signed'] : null,
            agreementSignedAt: isset($data['agreement_signed_at']) ? new \DateTimeImmutable($data['agreement_signed_at']) : null,
            status: ShareholderStatus::fromString($data['status'] ?? 'active'),
            totalDividendsReceived: array_key_exists('total_dividends_received', $data) ? (float) $data['total_dividends_received'] : null,
            lastDividendDate: isset($data['last_dividend_date']) ? new \DateTimeImmutable($data['last_dividend_date']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'venture_id' => $this->ventureId,
            'user_id' => $this->userId,
            'investment_id' => $this->investmentId,
            'total_investment' => $this->totalInvestment,
            'shares_owned' => $this->sharesOwned,
            'equity_percentage' => $this->equityPercentage,
            'certificate_number' => $this->certificateNumber,
            'registration_date' => $this->registrationDate?->format('Y-m-d H:i:s'),
            'shareholder_agreement_path' => $this->shareholderAgreementPath,
            'agreement_signed' => $this->agreementSigned,
            'agreement_signed_at' => $this->agreementSignedAt?->format('Y-m-d H:i:s'),
            'status' => $this->status->value(),
            'total_dividends_received' => $this->totalDividendsReceived,
            'last_dividend_date' => $this->lastDividendDate?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
