<?php

namespace App\Domain\Investor\Entities;

use App\Domain\Investor\ValueObjects\CertificateNumber;
use App\Domain\Investor\ValueObjects\ShareQuantity;
use DateTimeImmutable;

class ShareCertificate
{
    private function __construct(
        private readonly int $id,
        private readonly int $investorId,
        private readonly int $ventureProjectId,
        private readonly CertificateNumber $certificateNumber,
        private readonly ShareQuantity $shares,
        private readonly DateTimeImmutable $issueDate,
        private ?string $pdfPath = null,
        private ?DateTimeImmutable $generatedAt = null
    ) {}

    public static function create(
        int $id,
        int $investorId,
        int $ventureProjectId,
        CertificateNumber $certificateNumber,
        ShareQuantity $shares,
        DateTimeImmutable $issueDate
    ): self {
        return new self(
            $id,
            $investorId,
            $ventureProjectId,
            $certificateNumber,
            $shares,
            $issueDate
        );
    }

    public function generatePdf(string $pdfPath): void
    {
        $this->pdfPath = $pdfPath;
        $this->generatedAt = new DateTimeImmutable();
    }

    public function isGenerated(): bool
    {
        return $this->pdfPath !== null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getInvestorId(): int
    {
        return $this->investorId;
    }

    public function getVentureProjectId(): int
    {
        return $this->ventureProjectId;
    }

    public function getCertificateNumber(): CertificateNumber
    {
        return $this->certificateNumber;
    }

    public function getShares(): ShareQuantity
    {
        return $this->shares;
    }

    public function getIssueDate(): DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function getGeneratedAt(): ?DateTimeImmutable
    {
        return $this->generatedAt;
    }
}
