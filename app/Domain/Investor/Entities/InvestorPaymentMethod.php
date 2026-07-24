<?php

namespace App\Domain\Investor\Entities;

use DateTimeImmutable;

class InvestorPaymentMethod
{
    private function __construct(
        private readonly int $id,
        private int $investorAccountId,
        private string $methodType,
        private ?string $bankName,
        private ?string $accountNumber,
        private ?string $accountName,
        private ?string $branchCode,
        private ?string $mobileProvider,
        private ?string $mobileNumber,
        private bool $isPrimary,
        private bool $isVerified,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $investorAccountId,
        string $methodType,
        ?string $bankName = null,
        ?string $accountNumber = null,
        ?string $accountName = null,
        ?string $branchCode = null,
        ?string $mobileProvider = null,
        ?string $mobileNumber = null,
        bool $isPrimary = true
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: 0,
            investorAccountId: $investorAccountId,
            methodType: $methodType,
            bankName: $bankName,
            accountNumber: $accountNumber,
            accountName: $accountName,
            branchCode: $branchCode,
            mobileProvider: $mobileProvider,
            mobileNumber: $mobileNumber,
            isPrimary: $isPrimary,
            isVerified: false,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        int $investorAccountId,
        string $methodType,
        ?string $bankName,
        ?string $accountNumber,
        ?string $accountName,
        ?string $branchCode,
        ?string $mobileProvider,
        ?string $mobileNumber,
        bool $isPrimary,
        bool $isVerified,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            investorAccountId: $investorAccountId,
            methodType: $methodType,
            bankName: $bankName,
            accountNumber: $accountNumber,
            accountName: $accountName,
            branchCode: $branchCode,
            mobileProvider: $mobileProvider,
            mobileNumber: $mobileNumber,
            isPrimary: $isPrimary,
            isVerified: $isVerified,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    public function verify(): void
    {
        $this->isVerified = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getInvestorAccountId(): int { return $this->investorAccountId; }
    public function getMethodType(): string { return $this->methodType; }
    public function getBankName(): ?string { return $this->bankName; }
    public function getAccountNumber(): ?string { return $this->accountNumber; }
    public function getAccountName(): ?string { return $this->accountName; }
    public function getBranchCode(): ?string { return $this->branchCode; }
    public function getMobileProvider(): ?string { return $this->mobileProvider; }
    public function getMobileNumber(): ?string { return $this->mobileNumber; }
    public function isPrimary(): bool { return $this->isPrimary; }
    public function isVerified(): bool { return $this->isVerified; }
}
