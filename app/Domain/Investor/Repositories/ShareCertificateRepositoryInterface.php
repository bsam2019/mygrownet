<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\ShareCertificate;
use App\Domain\Investor\ValueObjects\CertificateNumber;

interface ShareCertificateRepositoryInterface
{
    public function findById(int $id): ?ShareCertificate;
    
    public function findByCertificateNumber(CertificateNumber $certificateNumber): ?ShareCertificate;
    
    public function findByInvestor(int $investorId): array;
    
    public function findByVentureProject(int $ventureProjectId): array;
    
    public function save(ShareCertificate $certificate): void;
    
    public function getNextSequenceNumber(int $ventureProjectId): int;
}
