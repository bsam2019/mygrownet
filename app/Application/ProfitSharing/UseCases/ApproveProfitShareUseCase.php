<?php

namespace App\Application\ProfitSharing\UseCases;

use App\Domain\ProfitSharing\Repositories\QuarterlyProfitShareRepository;

class ApproveProfitShareUseCase
{
    public function __construct(
        private QuarterlyProfitShareRepository $repository
    ) {}

    public function execute(int $profitShareId, int $approvedBy): void
    {
        $profitShare = $this->repository->findById($profitShareId);
        
        if (!$profitShare) {
            throw new \DomainException('Profit share not found');
        }

        $profitShare->approve($approvedBy);
        $this->repository->save($profitShare);
    }
}
