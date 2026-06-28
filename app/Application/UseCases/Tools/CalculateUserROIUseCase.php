<?php

namespace App\Application\UseCases\Tools;

use App\Domain\Tools\Entities\ROICalculation;
use App\Domain\Tools\Services\ROICalculationService;
use App\Models\User;

class CalculateUserROIUseCase
{
    public function __construct(
        private readonly ROICalculationService $roiService
    ) {}

    public function execute(User $user): ROICalculation
    {
        return $this->roiService->calculateForUser($user);
    }

    public function executeWithProjections(User $user, array $projectionPeriods = [30, 90, 180, 365]): array
    {
        $currentROI = $this->roiService->calculateForUser($user);
        
        $projections = [];
        foreach ($projectionPeriods as $days) {
            $projections[$days] = $this->roiService->projectFutureROI(
                $currentROI->totalInvestment(),
                $currentROI->totalEarnings(),
                $currentROI->daysActive(),
                $days
            );
        }

        return [
            'current' => $currentROI->toArray(),
            'projections' => $projections,
        ];
    }
}
