<?php

namespace App\Application\UseCases\Investor;

use App\Domain\Investor\Services\ShareCertificateService;

class GetInvestorCertificatesUseCase
{
    public function __construct(
        private readonly ShareCertificateService $certificateService
    ) {}

    public function execute(int $investorId): array
    {
        return $this->certificateService->getCertificatesForInvestor($investorId);
    }
}
