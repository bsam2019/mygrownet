<?php

namespace App\Application\ProfitSharing\UseCases;

use App\Application\ProfitSharing\DTOs\CreateProfitShareDTO;
use App\Domain\ProfitSharing\Entities\QuarterlyProfitShare;
use App\Domain\ProfitSharing\Entities\MemberProfitShare;
use App\Domain\ProfitSharing\Repositories\QuarterlyProfitShareRepository;
use App\Domain\ProfitSharing\Repositories\MemberProfitShareRepository;
use App\Domain\ProfitSharing\Services\ProfitDistributionCalculator;
use App\Domain\ProfitSharing\ValueObjects\Quarter;
use App\Domain\ProfitSharing\ValueObjects\ProfitAmount;
use App\Models\User;

class CreateQuarterlyProfitShareUseCase
{
    public function __construct(
        private QuarterlyProfitShareRepository $profitShareRepository,
        private MemberProfitShareRepository $memberShareRepository,
        private ProfitDistributionCalculator $calculator
    ) {}

    public function execute(CreateProfitShareDTO $dto): QuarterlyProfitShare
    {
        // Check if profit share already exists for this quarter
        $quarter = Quarter::create($dto->year, $dto->quarter);
        $existing = $this->profitShareRepository->findByQuarter($quarter);
        
        if ($existing) {
            throw new \DomainException("Profit share for {$quarter->label()} already exists");
        }

        // Get active members (those with current subscription and recent activity)
        $activeMembers = $this->getActiveMembers($quarter);
        
        if (empty($activeMembers)) {
            throw new \DomainException('No active members found for this quarter');
        }

        // Calculate total BP if using BP-based distribution
        $totalBpPool = null;
        if ($dto->distributionMethod === 'bp_based') {
            $totalBpPool = array_sum(array_column($activeMembers, 'bp'));
        }

        // Create quarterly profit share entity
        $profitShare = QuarterlyProfitShare::create(
            quarter: $quarter,
            totalProjectProfit: ProfitAmount::fromFloat($dto->totalProjectProfit),
            totalActiveMembers: count($activeMembers),
            totalBpPool: $totalBpPool,
            distributionMethod: $dto->distributionMethod,
            createdBy: $dto->createdBy,
            notes: $dto->notes
        );

        // Save the quarterly profit share
        $profitShare = $this->profitShareRepository->save($profitShare);

        // Calculate individual member shares
        $memberShares = $this->calculateMemberShares(
            $profitShare,
            $activeMembers,
            $dto->distributionMethod
        );

        // Save member shares in batch
        $this->memberShareRepository->saveBatch($memberShares);

        // Mark as calculated
        $profitShare->markAsCalculated();
        $this->profitShareRepository->save($profitShare);

        return $profitShare;
    }

    private function getActiveMembers(Quarter $quarter): array
    {
        // Active member criteria:
        // 1. Has active subscription (paid within quarter or still valid)
        // 2. Has logged in within last 30 days of quarter end
        
        $quarterEnd = $quarter->endDate();
        $activityCutoff = $quarterEnd->modify('-30 days');

        return User::query()
            ->where('subscription_status', 'active')
            ->where(function ($query) use ($activityCutoff) {
                $query->where('last_login_at', '>=', $activityCutoff->format('Y-m-d H:i:s'))
                    ->orWhereNull('last_login_at'); // Include new members who haven't logged in yet
            })
            ->get()
            ->map(fn($user) => [
                'user_id' => $user->id,
                'level' => $user->professional_level,
                'bp' => $user->business_points ?? 0,
            ])
            ->toArray();
    }

    private function calculateMemberShares(
        QuarterlyProfitShare $profitShare,
        array $activeMembers,
        string $distributionMethod
    ): array {
        $memberShareAmount = $profitShare->memberShareAmount();
        
        // Calculate distributions based on method
        if ($distributionMethod === 'bp_based') {
            $memberData = array_column($activeMembers, 'bp', 'user_id');
            $distributions = $this->calculator->calculateBPBasedDistribution(
                $memberShareAmount,
                $memberData,
                $profitShare->totalBpPool()
            );
        } else {
            $memberData = array_column($activeMembers, 'level', 'user_id');
            $distributions = $this->calculator->calculateLevelBasedDistribution(
                $memberShareAmount,
                $memberData
            );
        }

        // Create member profit share entities
        $memberShares = [];
        foreach ($activeMembers as $member) {
            $userId = $member['user_id'];
            $shareAmount = $distributions[$userId] ?? 0;

            $memberShares[] = MemberProfitShare::create(
                quarterlyProfitShareId: $profitShare->id(),
                userId: $userId,
                professionalLevel: $member['level'],
                levelMultiplier: $this->calculator->getLevelMultiplier($member['level']),
                memberBp: $member['bp'],
                shareAmount: $shareAmount
            );
        }

        return $memberShares;
    }
}
