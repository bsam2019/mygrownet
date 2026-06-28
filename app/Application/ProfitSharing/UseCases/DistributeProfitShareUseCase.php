<?php

namespace App\Application\ProfitSharing\UseCases;

use App\Domain\ProfitSharing\Repositories\QuarterlyProfitShareRepository;
use App\Domain\ProfitSharing\Repositories\MemberProfitShareRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DistributeProfitShareUseCase
{
    public function __construct(
        private QuarterlyProfitShareRepository $profitShareRepository,
        private MemberProfitShareRepository $memberShareRepository
    ) {}

    public function execute(int $profitShareId): void
    {
        $profitShare = $this->profitShareRepository->findById($profitShareId);
        
        if (!$profitShare) {
            throw new \DomainException('Profit share not found');
        }

        DB::transaction(function () use ($profitShare) {
            // Get all member shares for this quarter
            $memberShares = $this->memberShareRepository->findByQuarterlyProfitShareId(
                $profitShare->id()
            );

            // Credit each member's wallet
            foreach ($memberShares as $memberShare) {
                $user = User::find($memberShare->userId());
                
                if ($user) {
                    // Add to wallet balance
                    $user->wallet_balance += $memberShare->shareAmount();
                    $user->save();

                    // Mark as paid
                    $memberShare->markAsPaid();
                    $this->memberShareRepository->save($memberShare);
                }
            }

            // Mark profit share as distributed
            $profitShare->markAsDistributed();
            $this->profitShareRepository->save($profitShare);
        });
    }
}
