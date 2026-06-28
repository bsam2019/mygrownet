<?php

namespace App\Application\UseCases\Tools;

use App\Domain\Tools\Services\ROICalculationService;
use App\Domain\Tools\ValueObjects\ROIMetrics;
use App\Models\User;

class CalculateROIUseCase
{
    public function __construct(
        private readonly ROICalculationService $roiService
    ) {}

    public function execute(User $user): ROIMetrics
    {
        return $this->roiService->calculateUserROI($user);
    }

    public function executeWithProjection(
        User $user,
        int $projectionDays = 90
    ): array {
        $currentMetrics = $this->roiService->calculateUserROI($user);
        
        $projection = $this->roiService->projectFutureROI(
            $currentMetrics->totalInvestment(),
            $currentMetrics->totalEarnings(),
            $currentMetrics->daysActive(),
            $projectionDays
        );

        return [
            'current' => $currentMetrics->toArray(),
            'projection' => $projection,
        ];
    }
}
