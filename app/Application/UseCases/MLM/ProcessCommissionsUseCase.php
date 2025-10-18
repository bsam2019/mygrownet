<?php

namespace App\Application\UseCases\MLM;

use App\Domain\MLM\Entities\Commission;
use App\Domain\MLM\Repositories\CommissionRepository;
use App\Domain\MLM\Repositories\TeamVolumeRepository;
use App\Domain\MLM\Services\MLMCommissionCalculationService;
use App\Domain\MLM\ValueObjects\UserId;
use App\Models\User;
use App\Models\ReferralCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCommissionsUseCase
{
    public function __construct(
        private CommissionRepository $commissionRepository,
        private TeamVolumeRepository $teamVolumeRepository,
        private MLMCommissionCalculationService $calculationService
    ) {}

    public function execute(int $userId, float $purchaseAmount, string $packageType): array
    {
        return DB::transaction(function () use ($userId, $purchaseAmount, $packageType) {
            $user = User::findOrFail($userId);
            $commissions = [];

            // Get the user's upline network (5 levels)
            $uplineNetwork = $this->getUplineNetwork($user, 5);

            foreach ($uplineNetwork as $level => $uplineUser) {
                if (!$uplineUser) continue;

                $commissionRate = $this->getCommissionRateForLevel($level);
                $commissionAmount = $purchaseAmount * ($commissionRate / 100);

                // Create commission record
                $commission = $this->createCommission(
                    $uplineUser->id,
                    $userId,
                    $level,
                    $commissionAmount,
                    'REFERRAL'
                );

                $commissions[] = $commission;

                // Update team volume
                $this->updateTeamVolume($uplineUser->id, $purchaseAmount);

                Log::info("Commission processed", [
                    'earner_id' => $uplineUser->id,
                    'source_id' => $userId,
                    'level' => $level,
                    'amount' => $commissionAmount
                ]);
            }

            return $commissions;
        });
    }

    private function getUplineNetwork(User $user, int $levels): array
    {
        $upline = [];
        $currentUser = $user;

        for ($level = 1; $level <= $levels; $level++) {
            $referrer = $currentUser->referrer;
            $upline[$level] = $referrer;
            
            if (!$referrer) break;
            $currentUser = $referrer;
        }

        return $upline;
    }

    private function getCommissionRateForLevel(int $level): float
    {
        return match ($level) {
            1 => 12.0, // Level 1: 12%
            2 => 6.0,  // Level 2: 6%
            3 => 4.0,  // Level 3: 4%
            4 => 2.0,  // Level 4: 2%
            5 => 1.0,  // Level 5: 1%
            default => 0.0
        };
    }

    private function createCommission(
        int $earnerId,
        int $sourceId,
        int $level,
        float $amount,
        string $type
    ): ReferralCommission {
        return ReferralCommission::create([
            'user_id' => $earnerId,
            'referred_user_id' => $sourceId,
            'level' => $level,
            'amount' => $amount,
            'commission_type' => $type,
            'status' => 'pending',
            'earned_at' => now(),
        ]);
    }

    private function updateTeamVolume(int $userId, float $volume): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->increment('team_volume', $volume);
            $user->increment('monthly_team_volume', $volume);
        }
    }
}