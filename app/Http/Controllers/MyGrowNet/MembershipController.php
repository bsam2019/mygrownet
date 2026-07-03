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
        $user = auth()->user()->load('directReferrals');
        
        // Calculate points from transactions (source of truth)
        $lifetimePoints = \DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->sum('lp_amount');
            
        $businessPoints = \DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('bp_amount');
        
        // Get user's current professional level
        $currentLevel = $user->current_professional_level ?? 'associate';
        
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
            'business_points' => $businessPoints,
            'direct_referrals' => $directReferralsCount,
            'total_network' => $totalNetworkSize,
        ]);
        
        return Inertia::render('GrowNet/MyMembership', [
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
                'lifetime' => (int) $lifetimePoints,
                'business' => (int) $businessPoints,
                'required_business' => $currentLevelData['bpRequired'],
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
        $user = auth()->user();
        
        // Calculate lifetime points from transactions (source of truth)
        $lifetimePoints = \DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->sum('lp_amount');
        
        $currentLevel = $user->current_professional_level ?? 'associate';
        
        $levels = $this->getLevelRequirements();
        
        // Add user's progress to each level
        foreach ($levels as &$level) {
            $level['isCurrentLevel'] = $level['slug'] === $currentLevel;
            $level['isAchieved'] = $lifetimePoints >= $level['lpRequired'];
            $level['progressPercentage'] = $level['lpRequired'] > 0 
                ? min(100, ($lifetimePoints / $level['lpRequired']) * 100)
                : 100; // Associate level (0 LP required) is always 100%
        }
        
        return Inertia::render('GrowNet/ProfessionalLevels', [
            'levels' => $levels,
            'currentLevel' => $currentLevel,
            'lifetimePoints' => (int) $lifetimePoints,
        ]);
    }
    
    /**
     * Get level requirements data from database
     * Based on official MyGrowNet Points System documentation
     */
    private function getLevelRequirements(): array
    {
        // Fetch from database
        $dbLevels = \App\Models\ProfessionalLevel::active()->ordered()->get();
        
        if ($dbLevels->isNotEmpty()) {
            return $dbLevels->map(function ($level) {
                return [
                    'level' => $level->level,
                    'name' => $level->name,
                    'slug' => $level->slug,
                    'networkSize' => $level->network_size,
                    'role' => $level->role,
                    'bpRequired' => $level->bp_required,
                    'lpRequired' => $level->lp_required,
                    'minTime' => $level->min_time,
                    'additionalReqs' => $level->additional_requirements,
                    'milestoneBonus' => $level->milestone_bonus,
                    'profitShareMultiplier' => $level->profit_share_multiplier,
                    'commissionRate' => $level->commission_rate,
                    'color' => $level->color,
                    'benefits' => $level->benefits,
                ];
            })->toArray();
        }
        
        // Fallback to hardcoded values if database is empty
        return [
            [
                'level' => 1,
                'name' => 'Associate',
                'slug' => 'associate',
                'networkSize' => '3',
                'role' => 'New member, learning',
                'bpRequired' => 100,
                'lpRequired' => 0,
                'minTime' => 'Immediate',
                'additionalReqs' => 'Registration complete',
                'milestoneBonus' => null,
                'profitShareMultiplier' => '1.0x',
                'commissionRate' => '15%',
                'color' => 'gray',
                'benefits' => [
                    'Basic educational content',
                    'Peer circle access',
                    '7-level commission structure (15%)',
                    'Monthly qualification: 100 BP',
                    'Profit-sharing: 1.0x base share'
                ]
            ],
            [
                'level' => 2,
                'name' => 'Professional',
                'slug' => 'professional',
                'networkSize' => '9',
                'role' => 'Skilled member, applying',
                'bpRequired' => 200,
                'lpRequired' => 500,
                'minTime' => '1 month active',
                'additionalReqs' => '3 direct referrals',
                'milestoneBonus' => 'K500 + 100 LP',
                'profitShareMultiplier' => '1.2x',
                'commissionRate' => '10%',
                'color' => 'blue',
                'benefits' => [
                    'Advanced educational content',
                    'Group mentorship access',
                    'Level 2 commissions (10%)',
                    'Monthly qualification: 200 BP',
                    'Profit-sharing: 1.2x base share',
                    'Promotion bonus: K500'
                ]
            ],
            [
                'level' => 3,
                'name' => 'Senior',
                'slug' => 'senior',
                'networkSize' => '27',
                'role' => 'Experienced, team building',
                'bpRequired' => 300,
                'lpRequired' => 1500,
                'minTime' => '3 months active',
                'additionalReqs' => '2 active direct referrals, 1 course completed',
                'milestoneBonus' => 'K1,500 + 200 LP',
                'profitShareMultiplier' => '1.5x',
                'commissionRate' => '8%',
                'color' => 'green',
                'benefits' => [
                    'Premium content library',
                    '1-on-1 mentorship sessions',
                    'Level 3 commissions (8%)',
                    'Team building bonuses',
                    'Monthly qualification: 300 BP',
                    'Profit-sharing: 1.5x base share',
                    'Promotion bonus: K1,500'
                ]
            ],
            [
                'level' => 4,
                'name' => 'Manager',
                'slug' => 'manager',
                'networkSize' => '81',
                'role' => 'Team leader',
                'bpRequired' => 400,
                'lpRequired' => 4000,
                'minTime' => '6 months active',
                'additionalReqs' => '1 Professional in downline, 3 courses completed',
                'milestoneBonus' => 'K5,000 + 500 LP',
                'profitShareMultiplier' => '2.0x',
                'commissionRate' => '6%',
                'color' => 'purple',
                'benefits' => [
                    'Leadership training programs',
                    'Level 4 commissions (6%)',
                    'Team performance bonuses',
                    'Booster fund: K5,000',
                    'Monthly qualification: 400 BP',
                    'Profit-sharing: 2.0x base share',
                    'Promotion bonus: K5,000'
                ]
            ],
            [
                'level' => 5,
                'name' => 'Director',
                'slug' => 'director',
                'networkSize' => '243',
                'role' => 'Strategic leader',
                'bpRequired' => 500,
                'lpRequired' => 10000,
                'minTime' => '12 months active',
                'additionalReqs' => '1 Senior in downline, 5 courses completed',
                'milestoneBonus' => 'K15,000 + 1,000 LP',
                'profitShareMultiplier' => '2.5x',
                'commissionRate' => '4%',
                'color' => 'indigo',
                'benefits' => [
                    'Strategic leadership content',
                    'Level 5 commissions (4%)',
                    'Business facilitation services',
                    'Booster fund: K15,000',
                    'Monthly qualification: 500 BP',
                    'Profit-sharing: 2.5x base share',
                    'Promotion bonus: K15,000'
                ]
            ],
            [
                'level' => 6,
                'name' => 'Executive',
                'slug' => 'executive',
                'networkSize' => '729',
                'role' => 'Top performer',
                'bpRequired' => 600,
                'lpRequired' => 25000,
                'minTime' => '18 months active',
                'additionalReqs' => '1 Manager in downline, 10 courses completed',
                'milestoneBonus' => 'K50,000 + 2,500 LP',
                'profitShareMultiplier' => '3.0x',
                'commissionRate' => '3%',
                'color' => 'yellow',
                'benefits' => [
                    'Executive coaching access',
                    'Level 6 commissions (3%)',
                    'Innovation lab participation',
                    'Booster fund: K50,000',
                    'Monthly qualification: 600 BP',
                    'Profit-sharing: 3.0x base share',
                    'Promotion bonus: K50,000'
                ]
            ],
            [
                'level' => 7,
                'name' => 'Ambassador',
                'slug' => 'ambassador',
                'networkSize' => '2,187',
                'role' => 'Brand representative',
                'bpRequired' => 800,
                'lpRequired' => 50000,
                'minTime' => '24 months active',
                'additionalReqs' => '1 Director in downline, 15 courses, 1 project participation',
                'milestoneBonus' => 'K150,000 + 5,000 LP',
                'profitShareMultiplier' => '4.0x',
                'commissionRate' => '2%',
                'color' => 'red',
                'benefits' => [
                    'VIP brand ambassador status',
                    'Level 7 commissions (2%)',
                    'Exclusive events & retreats',
                    'Booster fund: K150,000',
                    'Monthly qualification: 800 BP',
                    'Profit-sharing: 4.0x base share (MAX)',
                    'Promotion bonus: K150,000'
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
