<?php

namespace App\Application\UseCases\Investor;

use App\Domain\Investor\Entities\ShareCertificate;
use App\Domain\Investor\Services\ShareCertificateService;
use App\Models\VentureInvestment;

class GenerateShareCertificateUseCase
{
    public function __construct(
        private readonly ShareCertificateService $certificateService
    ) {}

    public function execute(int $investmentId): ShareCertificate
    {
        // Load investment
        $investment = VentureInvestment::findOrFail($investmentId);

        // Generate certificate through domain service
        return $this->certificateService->generateCertificateForVenture($investment);
    }
}
