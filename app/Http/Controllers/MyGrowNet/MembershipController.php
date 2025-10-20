<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MembershipController extends Controller
{
    /**
     * Show the authenticated user's membership details
     */
    public function show(): Response
    {
        $user = auth()->user()->load('points', 'directReferrals');
        
        // Ensure user has a points record
        if (!$user->points) {
            $user->points()->create([
                'lifetime_points' => 0,
                'monthly_points' => 0,
            ]);
            $user->load('points');
        }
        
        // Get user's current professional level and points
        $currentLevel = $user->current_professional_level ?? 'associate';
        $lifetimePoints = $user->points->lifetime_points ?? 0;
        $monthlyPoints = $user->points->monthly_points ?? 0;
        
        // Define level requirements
        $levels = $this->getLevelRequirements();
        
        // Find current and next level
        $currentLevelData = collect($levels)->firstWhere('slug', $currentLevel);
        $currentLevelIndex = array_search($currentLevel, array_column($levels, 'slug'));
        $nextLevelData = $currentLevelIndex < count($levels) - 1 ? $levels[$currentLevelIndex + 1] : null;
        
        // Calculate progress to next level
        $progressPercentage = 0;
        if ($nextLevelData && $nextLevelData['lpRequired'] > 0) {
            $progressPercentage = min(100, ($lifetimePoints / $nextLevelData['lpRequired']) * 100);
        }
        
        // Get actual counts
        $directReferralsCount = $user->directReferrals()->count();
        $totalNetworkSize = $this->getTotalNetworkSize($user);
        
        // Debug log
        \Log::info('Membership Data', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'lifetime_points' => $lifetimePoints,
            'monthly_points' => $monthlyPoints,
            'direct_referrals' => $directReferralsCount,
            'total_network' => $totalNetworkSize,
        ]);
        
        return Inertia::render('MyGrowNet/MyMembership', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'referral_code' => $user->referral_code,
                'joined_at' => $user->created_at->format('F j, Y'),
            ],
            'currentLevel' => $currentLevelData,
            'nextLevel' => $nextLevelData,
            'points' => [
                'lifetime' => $lifetimePoints,
                'monthly' => $monthlyPoints,
                'required_monthly' => $currentLevelData['mapRequired'],
            ],
            'progress' => [
                'percentage' => round($progressPercentage, 1),
                'points_needed' => $nextLevelData ? max(0, $nextLevelData['lpRequired'] - $lifetimePoints) : 0,
            ],
            'network' => [
                'direct_referrals' => $directReferralsCount,
                'total_network' => $totalNetworkSize,
            ],
        ]);
    }
    
    /**
     * Show all professional levels
     */
    public function levels(): Response
    {
        $user = auth()->user()->load('points');
        
        // Ensure user has a points record
        if (!$user->points) {
            $user->points()->create([
                'lifetime_points' => 0,
                'monthly_points' => 0,
            ]);
            $user->load('points');
        }
        
        $currentLevel = $user->current_professional_level ?? 'associate';
        $lifetimePoints = $user->points->lifetime_points ?? 0;
        
        $levels = $this->getLevelRequirements();
        
        // Add user's progress to each level
        foreach ($levels as &$level) {
            $level['isCurrentLevel'] = $level['slug'] === $currentLevel;
            $level['isAchieved'] = $lifetimePoints >= $level['lpRequired'];
            $level['progressPercentage'] = $level['lpRequired'] > 0 
                ? min(100, ($lifetimePoints / $level['lpRequired']) * 100)
                : 100; // Associate level (0 LP required) is always 100%
        }
        
        return Inertia::render('MyGrowNet/ProfessionalLevels', [
            'levels' => $levels,
            'currentLevel' => $currentLevel,
            'lifetimePoints' => $lifetimePoints,
        ]);
    }
    
    /**
     * Get level requirements data
     */
    private function getLevelRequirements(): array
    {
        return [
            [
                'level' => 1,
                'name' => 'Associate',
                'slug' => 'associate',
                'networkSize' => '3',
                'role' => 'New member, learning',
                'mapRequired' => 100,
                'lpRequired' => 0,
                'minTime' => 'Immediate',
                'additionalReqs' => 'Registration complete',
                'milestoneBonus' => null,
                'profitShareMultiplier' => '1.0x',
                'color' => 'gray',
                'benefits' => [
                    'Basic educational content',
                    'Peer circle access',
                    '7-level commission structure',
                    'Monthly qualification: 100 MAP',
                    'Profit-sharing: 1.0x base share'
                ]
            ],
            [
                'level' => 2,
                'name' => 'Professional',
                'slug' => 'professional',
                'networkSize' => '9',
                'role' => 'Skilled member, applying',
                'mapRequired' => 200,
                'lpRequired' => 500,
                'minTime' => '1 month',
                'additionalReqs' => '3 direct referrals',
                'milestoneBonus' => 'K500',
                'profitShareMultiplier' => '1.2x',
                'color' => 'blue',
                'benefits' => [
                    'Advanced educational content',
                    'Group mentorship access',
                    'Enhanced commission rates',
                    'Monthly qualification: 200 MAP',
                    'Profit-sharing: 1.2x base share'
                ]
            ],
            [
                'level' => 3,
                'name' => 'Senior',
                'slug' => 'senior',
                'networkSize' => '27',
                'role' => 'Experienced, team building',
                'mapRequired' => 300,
                'lpRequired' => 1500,
                'minTime' => '3 months',
                'additionalReqs' => '2 active directs, 1 course',
                'milestoneBonus' => 'K1,500',
                'profitShareMultiplier' => '1.5x',
                'color' => 'green',
                'benefits' => [
                    'Premium content library',
                    '1-on-1 mentorship sessions',
                    'Team building bonuses',
                    'Monthly qualification: 300 MAP',
                    'Profit-sharing: 1.5x base share'
                ]
            ],
            [
                'level' => 4,
                'name' => 'Manager',
                'slug' => 'manager',
                'networkSize' => '81',
                'role' => 'Team leader',
                'mapRequired' => 400,
                'lpRequired' => 4000,
                'minTime' => '6 months',
                'additionalReqs' => '1 Professional in downline, 3 courses',
                'milestoneBonus' => 'K5,000',
                'profitShareMultiplier' => '2.0x',
                'color' => 'purple',
                'benefits' => [
                    'Leadership training programs',
                    'Team performance bonuses',
                    'Booster fund: K5,000',
                    'Monthly qualification: 400 MAP',
                    'Profit-sharing: 2.0x base share'
                ]
            ],
            [
                'level' => 5,
                'name' => 'Director',
                'slug' => 'director',
                'networkSize' => '243',
                'role' => 'Strategic leader',
                'mapRequired' => 500,
                'lpRequired' => 10000,
                'minTime' => '12 months',
                'additionalReqs' => '1 Senior in downline, 5 courses',
                'milestoneBonus' => 'K15,000',
                'profitShareMultiplier' => '2.5x',
                'color' => 'indigo',
                'benefits' => [
                    'Strategic leadership content',
                    'Business facilitation services',
                    'Booster fund: K15,000',
                    'Monthly qualification: 500 MAP',
                    'Profit-sharing: 2.5x base share'
                ]
            ],
            [
                'level' => 6,
                'name' => 'Executive',
                'slug' => 'executive',
                'networkSize' => '729',
                'role' => 'Top performer',
                'mapRequired' => 600,
                'lpRequired' => 25000,
                'minTime' => '18 months',
                'additionalReqs' => '1 Manager in downline, 10 courses',
                'milestoneBonus' => 'K50,000',
                'profitShareMultiplier' => '3.0x',
                'color' => 'yellow',
                'benefits' => [
                    'Executive coaching access',
                    'Innovation lab participation',
                    'Booster fund: K50,000',
                    'Monthly qualification: 600 MAP',
                    'Profit-sharing: 3.0x base share'
                ]
            ],
            [
                'level' => 7,
                'name' => 'Ambassador',
                'slug' => 'ambassador',
                'networkSize' => '2,187',
                'role' => 'Brand representative',
                'mapRequired' => 800,
                'lpRequired' => 50000,
                'minTime' => '24 months',
                'additionalReqs' => '1 Director in downline, 15 courses, 1 project',
                'milestoneBonus' => 'K150,000',
                'profitShareMultiplier' => '4.0x',
                'color' => 'red',
                'benefits' => [
                    'VIP brand ambassador status',
                    'Exclusive events & retreats',
                    'Booster fund: K150,000',
                    'Monthly qualification: 800 MAP',
                    'Profit-sharing: 4.0x base share (MAX)'
                ]
            ]
        ];
    }
    
    /**
     * Calculate total network size
     */
    private function getTotalNetworkSize($user): int
    {
        // This would recursively count all downline members
        // For now, return a simple count
        return $user->directReferrals()->count();
    }
}
