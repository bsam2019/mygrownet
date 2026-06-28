<?php

declare(strict_types=1);

namespace App\Domain\MLM\Services;

use App\Models\User;
use App\Models\UserNetwork;
use App\Models\ReferralCommission;
use Illuminate\Support\Facades\DB;

class NetworkBuildingService
{
    /**
     * Add a referral to the network and create commission records
     */
    public function addReferral(User $referrer, User $referral, float $packageAmount, string $packageType = 'subscription'): void
    {
        DB::transaction(function () use ($referrer, $referral, $packageAmount, $packageType) {
            // Update referral relationship
            $referral->update(['referrer_id' => $referrer->id]);
            
            // Build network structure
            $this->buildNetworkStructure($referral);
            
            // Calculate and create commission records
            $commissionService = new MLMCommissionCalculationService();
            $commissions = $commissionService->calculateMultilevelCommissions($referral, $packageAmount, $packageType);
            
            foreach ($commissions as $commissionData) {
                ReferralCommission::create($commissionData);
            }
            
            // Update referrer stats
            $referrer->incrementReferralCount();
            
            // Update team volumes
            $this->updateUplineTeamVolumes($referral);
        });
    }

    /**
     * Build network structure for a user
     */
    public function buildNetworkStructure(User $user): void
    {
        // Clear existing network records for this user
        UserNetwork::where('user_id', $user->id)->delete();
        
        if (!$user->referrer_id) {
            return;
        }
        
        // Build upline network structure
        $this->buildUplineNetwork($user);
    }

    /**
     * Get network statistics for a user
     */
    public function getNetworkStats(User $user): array
    {
        $networkMembers = UserNetwork::getNetworkMembers($user->id, 5);
        
        $stats = [
            'total_network_size' => 0,
            'levels' => [],
            'active_members' => 0,
            'total_volume' => 0,
        ];
        
        foreach ($networkMembers as $level => $members) {
            $levelStats = [
                'level' => $level,
                'count' => count($members),
                'active_count' => 0,
                'volume' => 0,
            ];
            
            foreach ($members as $memberData) {
                $member = $memberData['user'] ?? User::find($memberData['user_id']);
                if ($member && $member->hasActiveSubscription()) {
                    $levelStats['active_count']++;
                    $stats['active_members']++;
                }
            }
            
            $stats['levels'][$level] = $levelStats;
            $stats['total_network_size'] += $levelStats['count'];
        }
        
        return $stats;
    }

    /**
     * Calculate network depth for a user
     */
    public function calculateNetworkDepth(User $user): int
    {
        $networkMembers = UserNetwork::getNetworkMembers($user->id, 5);
        $maxDepth = 0;
        
        foreach ($networkMembers as $level => $members) {
            if (!empty($members)) {
                $maxDepth = max($maxDepth, $level);
            }
        }
        
        return $maxDepth;
    }

    /**
     * Get team members at specific level
     */
    public function getTeamMembers(User $user, int $level): array
    {
        $networkMembers = UserNetwork::getNetworkMembers($user->id, $level);
        return $networkMembers[$level] ?? [];
    }

    /**
     * Check if user can be placed under referrer
     */
    public function canPlaceUnderReferrer(User $referrer, User $user): bool
    {
        // Prevent circular references
        if ($this->wouldCreateCircularReference($referrer, $user)) {
            return false;
        }
        
        // Check if referrer is active
        if (!$referrer->hasActiveSubscription()) {
            return false;
        }
        
        return true;
    }

    /**
     * Build upline network structure for a user
     */
    private function buildUplineNetwork(User $user): void
    {
        $currentUser = $user;
        $level = 1;
        
        while ($currentUser->referrer_id && $level <= 5) {
            $referrer = User::find($currentUser->referrer_id);
            
            if (!$referrer) {
                break;
            }
            
            // Create network record
            UserNetwork::create([
                'user_id' => $user->id,
                'referrer_id' => $referrer->id,
                'level' => $level,
                'path' => $this->buildPath($user->id, $referrer->id),
            ]);
            
            $currentUser = $referrer;
            $level++;
        }
    }

    /**
     * Build materialized path for efficient queries
     */
    private function buildPath(int $userId, int $referrerId): string
    {
        $referrerPath = UserNetwork::where('user_id', $referrerId)->value('path');
        
        if ($referrerPath) {
            return $referrerPath . '.' . $userId;
        }
        
        return (string) $userId;
    }

    /**
     * Check if placement would create circular reference
     */
    private function wouldCreateCircularReference(User $referrer, User $user): bool
    {
        // Check if referrer is in user's downline
        $userDownline = UserNetwork::where('referrer_id', $user->id)->pluck('user_id');
        
        return $userDownline->contains($referrer->id);
    }

    /**
     * Update team volumes for all upline members
     */
    private function updateUplineTeamVolumes(User $user): void
    {
        $uplineReferrers = UserNetwork::getUplineReferrers($user->id, 5);
        $commissionService = new MLMCommissionCalculationService();
        
        foreach ($uplineReferrers as $referrerData) {
            $referrer = User::find($referrerData['user_id']);
            if ($referrer) {
                $commissionService->updateUserTeamVolume($referrer);
            }
        }
    }
}