<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ReferralProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class MarketingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Marketing/Index', [
            'campaigns' => Campaign::withCount('participants')->latest()->paginate(10),
            'referralProgram' => ReferralProgram::first(),
            'topReferrers' => User::withCount('referrals')
                ->having('referrals_count', '>', 0)
                ->orderByDesc('referrals_count')
                ->take(5)
                ->get()
        ]);
    }

    public function createCampaign(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:bonus,discount,referral_bonus',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reward_amount' => 'required|numeric|min:0',
            'minimum_investment' => 'required|numeric|min:0',
            'max_participants' => 'nullable|integer|min:1',
            'terms_conditions' => 'required|string'
        ]);

        $campaign = Campaign::create([
            ...$validated,
            'code' => strtoupper(Str::random(8)),
            'status' => 'draft'
        ]);

        return back()->with('success', 'Campaign created successfully');
    }

    public function updateCampaign(Campaign $campaign, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reward_amount' => 'required|numeric|min:0',
            'minimum_investment' => 'required|numeric|min:0',
            'max_participants' => 'nullable|integer|min:1',
            'terms_conditions' => 'required|string',
            'status' => 'required|in:draft,active,paused,ended'
        ]);

        $campaign->update($validated);

        return back()->with('success', 'Campaign updated successfully');
    }

    public function updateReferralProgram(Request $request)
    {
        $validated = $request->validate([
            'referral_bonus' => 'required|numeric|min:0',
            'referee_bonus' => 'required|numeric|min:0',
            'minimum_investment' => 'required|numeric|min:0',
            'bonus_type' => 'required|in:fixed,percentage',
            'max_referrals_per_user' => 'nullable|integer|min:1',
            'terms_conditions' => 'required|string'
        ]);

        ReferralProgram::updateOrCreate(
            ['id' => 1],
            $validated
        );

        return back()->with('success', 'Referral program updated successfully');
    }

    public function showCampaignStats(Campaign $campaign)
    {
        return Inertia::render('Admin/Marketing/CampaignStats', [
            'campaign' => $campaign->load('participants'),
            'stats' => [
                'total_participants' => $campaign->participants()->count(),
                'total_investments' => $campaign->participants()
                    ->sum('investment_amount'),
                'total_rewards_paid' => $campaign->participants()
                    ->sum('reward_amount'),
                'conversion_rate' => $campaign->calculateConversionRate()
            ],
            'participants' => $campaign->participants()
                ->with('user')
                ->latest()
                ->paginate(15)
        ]);
    }

    public function showReferralStats()
    {
        return Inertia::render('Admin/Marketing/ReferralStats', [
            'stats' => [
                'total_referrals' => User::sum('referrals_count'),
                'total_rewards_paid' => User::sum('referral_earnings'),
                'active_referrers' => User::where('referrals_count', '>', 0)->count()
            ],
            'topReferrers' => User::withCount('referrals')
                ->withSum('referralEarnings', 'amount')
                ->having('referrals_count', '>', 0)
                ->orderByDesc('referrals_count')
                ->take(10)
                ->get(),
            'monthlyReferrals' => $this->getMonthlyReferralStats()
        ]);
    }

    private function getMonthlyReferralStats()
    {
        return User::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as referral_count,
                SUM(referral_earnings) as total_earnings
            ')
            ->where('referrer_id', '!=', null)
            ->whereBetween('created_at', [
                now()->subMonths(11)->startOfMonth(),
                now()->endOfMonth()
            ])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
