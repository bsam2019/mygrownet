<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Investor\Entities\ShareCertificate;
use App\Domain\Investor\Repositories\ShareCertificateRepositoryInterface;
use App\Domain\Investor\ValueObjects\CertificateNumber;
use App\Domain\Investor\ValueObjects\ShareQuantity;
use App\Models\InvestorShareCertificate;
use DateTimeImmutable;

class EloquentShareCertificateRepository implements ShareCertificateRepositoryInterface
{
    public function findById(int $id): ?ShareCertificate
    {
        $model = InvestorShareCertificate::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCertificateNumber(CertificateNumber $certificateNumber): ?ShareCertificate
    {
        $model = InvestorShareCertificate::where('certificate_number', $certificateNumber->value())->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByInvestor(int $investorId): array
    {
        return InvestorShareCertificate::where('investor_id', $investorId)
            ->orderBy('issue_date', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findByVentureProject(int $ventureProjectId): array
    {
        return InvestorShareCertificate::where('venture_project_id', $ventureProjectId)
            ->orderBy('issue_date', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function save(ShareCertificate $certificate): void
    {
        $model = InvestorShareCertificate::findOrNew($certificate->getId());
        
        $model->investor_id = $certificate->getInvestorId();
        $model->venture_project_id = $certificate->getVentureProjectId();
        $model->certificate_number = $certificate->getCertificateNumber()->value();
        $model->shares = $certificate->getShares()->value();
        $model->issue_date = $certificate->getIssueDate()->format('Y-m-d');
        $model->pdf_path = $certificate->getPdfPath();
        $model->generated_at = $certificate->getGeneratedAt()?->format('Y-m-d H:i:s');
        
        $model->save();
    }

    public function getNextSequenceNumber(int $ventureProjectId): int
    {
        return InvestorShareCertificate::where('venture_project_id', $ventureProjectId)->count() + 1;
    }

    private function toDomainEntity(InvestorShareCertificate $model): ShareCertificate
    {
        $certificate = ShareCertificate::create(
            $model->id,
            $model->investor_id,
            $model->venture_project_id,
            CertificateNumber::fromString($model->certificate_number),
            ShareQuantity::fromInt($model->shares),
            new DateTimeImmutable($model->issue_date)
        );

        if ($model->pdf_path) {
            $certificate->generatePdf($model->pdf_path);
        }

        return $certificate;
    }
}
