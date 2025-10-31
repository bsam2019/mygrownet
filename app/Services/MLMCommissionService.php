<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\UserNetwork;
use App\Models\TeamVolume;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MLMCommissionService
{
    /**
     * Process seven-level MLM commissions for a package purchase
     */
    public function processMLMCommissions(
        User $purchaser, 
        float $packageAmount, 
        string $packageType = 'subscription'
    ): array {
        $commissions = [];
        
        Log::info("Starting MLM commission processing", [
            'purchaser_id' => $purchaser->id,
            'purchaser_name' => $purchaser->name,
            'package_amount' => $packageAmount,
            'package_type' => $packageType,
            'referrer_id' => $purchaser->referrer_id
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get upline referrers up to 7 levels (MyGrowNet professional progression)
            $uplineReferrers = $this->getUplineReferrers($purchaser, ReferralCommission::MAX_COMMISSION_LEVELS);
            
            Log::info("Upline referrers found", [
                'count' => count($uplineReferrers),
                'referrers' => $uplineReferrers
            ]);
            
            foreach ($uplineReferrers as $referrerData) {
                $referrer = User::find($referrerData['user_id']);
                $level = $referrerData['level'];
                
                Log::info("Processing referrer", [
                    'referrer_id' => $referrerData['user_id'],
                    'level' => $level,
                    'referrer_found' => $referrer ? 'yes' : 'no'
                ]);
                
                if (!$referrer) {
                    Log::warning("Referrer not found", ['referrer_id' => $referrerData['user_id']]);
                    continue;
                }
                
                // For registration payments, only check if user is active
                // For subscription payments, check if referrer has active subscription
                if ($packageType === 'registration' || $packageType === 'wallet_topup') {
                    // Registration commissions: referrer just needs to be active
                    if ($referrer->status !== 'active') {
                        Log::info("Referrer not active, skipping", [
                            'referrer_id' => $referrer->id,
                            'status' => $referrer->status
                        ]);
                        continue;
                    }
                } else {
                    // Subscription/other commissions: referrer needs active subscription
                    if (!$referrer->hasActiveSubscription()) {
                        Log::info("Referrer doesn't have active subscription, skipping", [
                            'referrer_id' => $referrer->id
                        ]);
                        continue;
                    }
                }
                
                // Calculate commission amount for this level
                // IMPORTANT: Only award commission if referrer has purchased starter kit
                if (!$referrer->has_starter_kit) {
                    Log::info("Skipping commission - referrer has no starter kit", [
                        'referrer_id' => $referrer->id,
                        'referrer_name' => $referrer->name,
                        'level' => $level,
                    ]);
                    continue;
                }
                
                $commissionRate = ReferralCommission::getCommissionRate($level);
                $commissionAmount = $packageAmount * ($commissionRate / 100);
                
                Log::info("Creating commission", [
                    'referrer_id' => $referrer->id,
                    'level' => $level,
                    'rate' => $commissionRate,
                    'amount' => $commissionAmount
                ]);
                
                // Create commission record
                $commission = ReferralCommission::create([
                    'referrer_id' => $referrer->id,
                    'referred_id' => $purchaser->id,
                    'level' => $level,
                    'amount' => $commissionAmount,
                    'percentage' => $commissionRate,
                    'status' => 'pending',
                    'commission_type' => 'REFERRAL',
                    'package_type' => $packageType,
                    'package_amount' => $packageAmount,
                    'team_volume' => $referrer->current_team_volume ?? 0,
                    'personal_volume' => $referrer->current_personal_volume ?? 0,
                ]);
                
                $commissions[] = $commission;
                
                // Process payment immediately (24-hour requirement)
                $commission->processPayment();
                
                // Send notification
                try {
                    app(SendNotificationUseCase::class)->execute(
                        userId: $referrer->id,
                        type: 'commission.earned',
                        data: [
                            'title' => 'Commission Earned',
                            'message' => 'You earned a Level ' . $level . ' commission',
                            'amount' => 'K' . number_format($commissionAmount, 2),
                            'level' => (string)$level,
                            'from_user' => $purchaser->name,
                            'action_url' => route('mygrownet.earnings.index'),
                            'action_text' => 'View Earnings'
                        ]
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send commission notification', [
                        'referrer_id' => $referrer->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Update team volumes
            $this->updateTeamVolumes($purchaser, $packageAmount);
            
            DB::commit();
            
            Log::info("MLM commissions processed", [
                'purchaser_id' => $purchaser->id,
                'package_amount' => $packageAmount,
                'commissions_count' => count($commissions),
                'total_commission_amount' => collect($commissions)->sum('amount')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("MLM commission processing failed", [
                'purchaser_id' => $purchaser->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        return $commissions;
    }
    
    /**
     * Get upline referrers efficiently using network path or referrer_id chain
     */
    protected function getUplineReferrers(User $user, int $maxLevels = 7): array
    {
        $upline = [];
        $currentUser = $user;
        $level = 1;
        
        // Walk up the referrer chain
        while ($currentUser->referrer_id && $level <= $maxLevels) {
            $upline[] = [
                'user_id' => $currentUser->referrer_id,
                'level' => $level
            ];
            
            // Get the next referrer
            $currentUser = User::find($currentUser->referrer_id);
            if (!$currentUser) {
                break;
            }
            
            $level++;
        }
        
        Log::info("Built upline chain", [
            'user_id' => $user->id,
            'upline_count' => count($upline),
            'upline' => $upline
        ]);
        
        return $upline;
    }
    
    /**
     * Update team volumes for all upline members
     */
    protected function updateTeamVolumes(User $purchaser, float $packageAmount): void
    {
        $uplineReferrers = $this->getUplineReferrers($purchaser, ReferralCommission::MAX_COMMISSION_LEVELS);
        
        foreach ($uplineReferrers as $referrerData) {
            $referrer = User::find($referrerData['user_id']);
            if (!$referrer) continue;
            
            // Update or create current month team volume
            $currentPeriod = now()->startOfMonth();
            $endPeriod = now()->endOfMonth();
            
            $teamVolume = TeamVolume::firstOrCreate([
                'user_id' => $referrer->id,
                'period_start' => $currentPeriod,
                'period_end' => $endPeriod,
            ], [
                'personal_volume' => 0,
                'team_volume' => 0,
                'team_depth' => 0,
                'active_referrals_count' => 0,
            ]);
            
            // Add to team volume
            $teamVolume->increment('team_volume', $packageAmount);
            
            // Update personal volume if this is direct referral
            if ($referrerData['level'] === 1) {
                $teamVolume->increment('personal_volume', $packageAmount);
                $teamVolume->increment('active_referrals_count');
            }
            
            // Update team depth
            $maxDepth = $this->calculateTeamDepth($referrer);
            $teamVolume->update(['team_depth' => $maxDepth]);
            
            // Update user's current volume fields
            $referrer->updateCurrentTeamVolume();
        }
    }
    
    /**
     * Calculate team depth for a user
     */
    protected function calculateTeamDepth(User $user): int
    {
        if (!$user->network_path) {
            return 0;
        }
        
        $maxLevel = User::where('network_path', 'LIKE', $user->network_path . '.%')
                        ->max('network_level');
                        
        return $maxLevel ? $maxLevel - $user->network_level : 0;
    }
    
    /**
     * Process performance bonuses based on team volume
     */
    public function processPerformanceBonuses(User $user): ?ReferralCommission
    {
        $currentVolume = $user->getCurrentTeamVolume();
        if (!$currentVolume) {
            return null;
        }
        
        $bonusAmount = $currentVolume->calculatePerformanceBonus();
        if ($bonusAmount <= 0) {
            return null;
        }
        
        $commission = ReferralCommission::create([
            'referrer_id' => $user->id,
            'referred_id' => $user->id, // Self-referencing for performance bonus
            'level' => 0, // Special level for performance bonuses
            'amount' => $bonusAmount,
            'percentage' => 0,
            'status' => 'pending',
            'commission_type' => 'PERFORMANCE',
            'package_type' => 'team_volume_bonus',
            'package_amount' => $currentVolume->team_volume,
            'team_volume' => $currentVolume->team_volume,
            'personal_volume' => $currentVolume->personal_volume,
        ]);
        
        // Process payment immediately
        $commission->processPayment();
        
        return $commission;
    }
    
    /**
     * Build or rebuild network paths for all users
     */
    public function rebuildNetworkPaths(): void
    {
        Log::info("Starting network path rebuild");
        
        // Start with root users (no referrer)
        $rootUsers = User::whereNull('referrer_id')->get();
        
        foreach ($rootUsers as $user) {
            $this->buildNetworkPathRecursive($user, '', 0);
        }
        
        Log::info("Network path rebuild completed");
    }
    
    /**
     * Recursively build network paths
     */
    protected function buildNetworkPathRecursive(User $user, string $parentPath, int $level): void
    {
        $path = $parentPath ? $parentPath . '.' . $user->id : (string) $user->id;
        
        $user->update([
            'network_path' => $path,
            'network_level' => $level
        ]);
        
        // Process children
        $children = User::where('referrer_id', $user->id)->get();
        foreach ($children as $child) {
            $this->buildNetworkPathRecursive($child, $path, $level + 1);
        }
    }
}