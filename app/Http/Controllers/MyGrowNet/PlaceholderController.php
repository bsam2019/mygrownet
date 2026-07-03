<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaceholderController extends Controller
{
    /**
     * Show a "Coming Soon" page for features under development
     */
    public function comingSoon(string $feature): Response
    {
        $featureTitles = [
            'my-membership' => 'My Membership',
            'professional-levels' => 'Professional Levels',
            'network' => 'Network Management',
            'commissions' => 'Commission Tracking',
            'membership-upgrade' => 'Membership Upgrade',
            'projects' => 'Community Projects',
            'assets' => 'Asset Management',
            'rewards' => 'Rewards & Bonuses',
            'referrals' => 'Referral Management',
            'learning' => 'Learning Hub',
            'wallet' => 'My Wallet (MyGrow Save)',
            'profit-sharing' => 'Quarterly Profit-Sharing',
        ];

        return Inertia::render('GrowNet/ComingSoon', [
            'feature' => $feature,
            'title' => $featureTitles[$feature] ?? 'Feature',
        ]);
    }
}
