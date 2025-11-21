<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecommendationEngine
{
    public function generateRecommendations(User $user): array
    {
        $recommendations = [];
        
        // Check for upgrade opportunity
        if ($upgrade = $this->getUpgradeRecommendation($user)) {
            $recommendations[] = $upgrade;
        }
        
        // Check for network growth
        if ($growth = $this->getNetworkGrowthRecommendation($user)) {
            $recommendations[] = $growth;
        }
        
        // Check for engagement
        if ($engagement = $this->getEngagementRecommendation($user)) {
            $recommendations[] = $engagement;
        }
        
        // Check for learning opportunities
        if ($learning = $this->getLearningRecommendation($user)) {
            $recommendations[] = $learning;
        }
        
        // Save to database
        foreach ($recommendations as $rec) {
            $this->saveRecommendation($user, $rec);
        }
        
        return $recommendations;
    }
    
    protected function getUpgradeRecommendation(User $user): ?array
    {
        if ($user->starter_kit_tier === 'basic') {
            return [
                'type' => 'upgrade',
                'title' => 'Upgrade to Premium Starter Kit',
                'description' => 'Unlock LGR profit sharing and earn K5,000+ more per year',
                'action_url' => route('mygrownet.starter-kit.upgrade'),
                'action_text' => 'Upgrade Now',
                'priority' => 'high',
                'impact_score' => 85,
            ];
        }
        return null;
    }
    
    protected function getNetworkGrowthRecommendation(User $user): ?array
    {
        $networkSize = $user->referral_count ?? 0;
        $nextLevel = $this->getNextProfessionalLevel($user);
        
        if ($nextLevel) {
            $needed = $nextLevel['required_referrals'] - $networkSize;
            if ($needed > 0 && $needed <= 5) {
                return [
                    'type' => 'network_growth',
                    'title' => "You're {$needed} referrals away from {$nextLevel['name']}",
                    'description' => "Reach {$nextLevel['name']} level and unlock new benefits",
                    'action_url' => route('my-team.index'),
                    'action_text' => 'Invite Friends',
                    'priority' => 'medium',
                    'impact_score' => 70,
                ];
            }
        }
        return null;
    }
    
    protected function getEngagementRecommendation(User $user): ?array
    {
        $inactiveMembers = $user->directReferrals()
            ->where('is_currently_active', false)
            ->count();
        
        if ($inactiveMembers > 0) {
            $totalReferrals = $user->referral_count ?? 1;
            $percentage = round(($inactiveMembers / $totalReferrals) * 100);
            
            if ($percentage > 30) {
                return [
                    'type' => 'engagement',
                    'title' => "{$percentage}% of your network is inactive",
                    'description' => "Re-engage {$inactiveMembers} inactive members to boost your earnings",
                    'action_url' => route('my-team.index'),
                    'action_text' => 'View Team',
                    'priority' => 'medium',
                    'impact_score' => 60,
                ];
            }
        }
        return null;
    }
    
    protected function getLearningRecommendation(User $user): ?array
    {
        // Check if user has accessed starter kit content recently
        $lastAccess = DB::table('analytics_events')
            ->where('user_id', $user->id)
            ->where('event_type', 'starter_kit_access')
            ->where('created_at', '>', now()->subDays(7))
            ->exists();
        
        if (!$lastAccess && $user->starter_kit_tier) {
            return [
                'type' => 'learning',
                'title' => 'Continue Your Learning Journey',
                'description' => 'Access your starter kit resources to improve your skills',
                'action_url' => route('mygrownet.content.index'),
                'action_text' => 'View Resources',
                'priority' => 'low',
                'impact_score' => 50,
            ];
        }
        return null;
    }
    
    protected function getNextProfessionalLevel(User $user): ?array
    {
        $levels = [
            ['name' => 'Professional', 'required_referrals' => 3],
            ['name' => 'Senior', 'required_referrals' => 9],
            ['name' => 'Manager', 'required_referrals' => 27],
            ['name' => 'Director', 'required_referrals' => 81],
            ['name' => 'Executive', 'required_referrals' => 243],
            ['name' => 'Ambassador', 'required_referrals' => 729],
        ];
        
        $currentSize = $user->referral_count ?? 0;
        
        foreach ($levels as $level) {
            if ($currentSize < $level['required_referrals']) {
                return $level;
            }
        }
        
        return null;
    }
    
    protected function saveRecommendation(User $user, array $data): void
    {
        // Check if similar recommendation already exists
        $exists = DB::table('recommendations')
            ->where('user_id', $user->id)
            ->where('recommendation_type', $data['type'])
            ->where('is_dismissed', false)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->exists();
        
        if (!$exists) {
            DB::table('recommendations')->insert([
                'user_id' => $user->id,
                'recommendation_type' => $data['type'],
                'title' => $data['title'],
                'description' => $data['description'],
                'action_url' => $data['action_url'],
                'action_text' => $data['action_text'],
                'priority' => $data['priority'],
                'impact_score' => $data['impact_score'],
                'expires_at' => now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    public function dismissRecommendation(int $recommendationId, User $user): bool
    {
        return DB::table('recommendations')
            ->where('id', $recommendationId)
            ->where('user_id', $user->id)
            ->update([
                'is_dismissed' => true,
                'dismissed_at' => now(),
                'updated_at' => now(),
            ]) > 0;
    }
    
    public function getActiveRecommendations(User $user): array
    {
        $recommendations = DB::table('recommendations')
            ->where('user_id', $user->id)
            ->where('is_dismissed', false)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('impact_score', 'desc')
            ->get();
        
        // Convert to array of arrays for JSON serialization
        return $recommendations->map(function($rec) {
            return (array) $rec;
        })->toArray();
    }
}
