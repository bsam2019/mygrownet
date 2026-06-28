<?php

namespace App\Application\DTOs;

use App\Domain\Investor\Entities\ShareCertificate;

class ShareCertificateDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $certificateNumber,
        public readonly int $shares,
        public readonly string $issueDate,
        public readonly ?string $pdfPath,
        public readonly bool $isGenerated,
        public readonly int $investorId,
        public readonly int $ventureProjectId
    ) {}

    public static function fromEntity(ShareCertificate $certificate): self
    {
        return new self(
            id: $certificate->getId(),
            certificateNumber: $certificate->getCertificateNumber()->value(),
            shares: $certificate->getShares()->value(),
            issueDate: $certificate->getIssueDate()->format('Y-m-d'),
            pdfPath: $certificate->getPdfPath(),
            isGenerated: $certificate->isGenerated(),
            investorId: $certificate->getInvestorId(),
            ventureProjectId: $certificate->getVentureProjectId()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'certificate_number' => $this->certificateNumber,
            'shares' => $this->shares,
            'issue_date' => $this->issueDate,
            'pdf_path' => $this->pdfPath,
            'is_generated' => $this->isGenerated,
            'investor_id' => $this->investorId,
            'venture_project_id' => $this->ventureProjectId,
        ];
    }
}
