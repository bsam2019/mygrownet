<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\ShareCertificate;
use App\Domain\Investor\Repositories\ShareCertificateRepositoryInterface;
use App\Domain\Investor\ValueObjects\CertificateNumber;
use App\Domain\Investor\ValueObjects\ShareQuantity;
use App\Models\InvestorAccount;
use App\Models\VentureInvestment;
use App\Models\VentureProject;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTimeImmutable;
use Illuminate\Support\Facades\Storage;

class ShareCertificateService
{
    public function __construct(
        private readonly ShareCertificateRepositoryInterface $certificateRepository
    ) {}

    /**
     * Generate share certificate for a venture investment
     */
    public function generateCertificateForVenture(VentureInvestment $investment): ShareCertificate
    {
        // Generate unique certificate number
        $certificateNumber = $this->generateVentureCertificateNumber($investment->ventureProject);

        // Create domain entity
        $certificate = ShareCertificate::create(
            0, // ID will be set after save
            $investment->user_id,
            $investment->venture_project_id,
            $certificateNumber,
            ShareQuantity::fromInt($investment->shares),
            new DateTimeImmutable()
        );

        // Save to repository
        $this->certificateRepository->save($certificate);

        // Generate PDF
        $this->generateVenturePdf($certificate);

        return $certificate;
    }

    /**
     * Generate share certificate for an investor account (legacy support)
     */
    public function generateCertificateForInvestor(InvestorAccount $investor): ShareCertificate
    {
        // Check if certificate already exists
        $existing = $this->certificateRepository->findByInvestor($investor->id);
        if (!empty($existing)) {
            return $existing[0];
        }

        // Generate unique certificate number
        $certificateNumber = $this->generateInvestorCertificateNumber($investor);
        
        // Create domain entity
        $certificate = ShareCertificate::create(
            0,
            $investor->id,
            0, // No venture project for legacy investor accounts
            $certificateNumber,
            ShareQuantity::fromInt((int)($investor->equity_percentage * 100)), // Convert percentage to shares
            new DateTimeImmutable($investor->investment_date->format('Y-m-d'))
        );

        // Save to repository
        $this->certificateRepository->save($certificate);

        // Generate PDF
        $this->generateInvestorPdf($certificate, $investor);

        return $certificate;
    }

    /**
     * Generate certificate number for venture projects
     */
    private function generateVentureCertificateNumber(VentureProject $project): CertificateNumber
    {
        $prefix = strtoupper(substr($project->name, 0, 3));
        $sequence = $this->certificateRepository->getNextSequenceNumber($project->id);

        return CertificateNumber::generate($prefix, $sequence);
    }

    /**
     * Generate certificate number for investor accounts (legacy)
     */
    private function generateInvestorCertificateNumber(InvestorAccount $investor): CertificateNumber
    {
        $year = $investor->investment_date->format('Y');
        $sequence = $this->certificateRepository->getNextSequenceNumber(0) + 1;
        
        return CertificateNumber::fromString(sprintf('MGN-%s-%04d', $year, $sequence));
    }

    /**
     * Generate PDF for venture certificate
     */
    private function generateVenturePdf(ShareCertificate $certificate): void
    {
        // Load Eloquent models for PDF generation
        $investorModel = \App\Models\User::find($certificate->getInvestorId());
        $projectModel = \App\Models\VentureProject::find($certificate->getVentureProjectId());

        $pdf = Pdf::loadView('pdfs.share-certificate-venture', [
            'certificateNumber' => $certificate->getCertificateNumber()->value(),
            'shares' => $certificate->getShares()->value(),
            'issueDate' => $certificate->getIssueDate()->format('F j, Y'),
            'investor' => $investorModel,
            'project' => $projectModel,
        ])->setPaper('a4', 'portrait');

        $filename = "certificates/venture/{$certificate->getCertificateNumber()->value()}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        $certificate->generatePdf($filename);
        $this->certificateRepository->save($certificate);
    }

    /**
     * Generate PDF for investor certificate (legacy)
     */
    private function generateInvestorPdf(ShareCertificate $certificate, InvestorAccount $investor): void
    {
        $data = [
            'certificateNumber' => $certificate->getCertificateNumber()->value(),
            'shares' => $certificate->getShares()->value(),
            'issueDate' => $certificate->getIssueDate()->format('F j, Y'),
            'investor' => $investor,
            'company_name' => 'MyGrowNet Limited',
            'company_registration' => config('app.company_registration', 'RC-123456'),
        ];

        $pdf = Pdf::loadView('pdfs.share-certificate-investor', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10);

        $filename = "certificates/investor/{$certificate->getCertificateNumber()->value()}.pdf";
        Storage::disk('private')->put($filename, $pdf->output());

        $certificate->generatePdf($filename);
        $this->certificateRepository->save($certificate);
    }

    /**
     * Verify certificate authenticity
     */
    public function verifyCertificate(string $certificateNumber): ?ShareCertificate
    {
        return $this->certificateRepository->findByCertificateNumber(
            CertificateNumber::fromString($certificateNumber)
        );
    }

    /**
     * Get certificates for investor
     */
    public function getCertificatesForInvestor(int $investorId): array
    {
        return $this->certificateRepository->findByInvestor($investorId);
    }

    /**
     * Get certificates for venture project
     */
    public function getCertificatesForProject(int $projectId): array
    {
        return $this->certificateRepository->findByVentureProject($projectId);
    }
}
