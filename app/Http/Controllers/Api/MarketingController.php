<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ReferralProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingController extends Controller
{
    public function activeCampaigns()
    {
        $campaigns = Campaign::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        return response()->json([
            'success' => true,
            'campaigns' => $campaigns
        ]);
    }

    public function applyReferralCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|exists:users,referral_code'
        ]);

        $referrer = User::where('referral_code', $request->referral_code)->first();

        if (!$referrer || $referrer->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code'
            ], 400);
        }

        if (Auth::user()->referrer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Referral code already applied'
            ], 400);
        }

        Auth::user()->update([
            'referrer_id' => $referrer->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Referral code applied successfully'
        ]);
    }

    public function getReferralStats()
    {
        $user = Auth::user();
        $referralProgram = ReferralProgram::first();

        return response()->json([
            'success' => true,
            'referral_code' => $user->referral_code,
            'referral_link' => config('app.url') . '/register?ref=' . $user->referral_code,
            'total_referrals' => $user->referrals()->count(),
            'referral_earnings' => $user->referralEarnings()->sum('amount'),
            'pending_referrals' => $user->referrals()
                ->whereNull('first_investment_date')
                ->count(),
            'program_details' => [
                'referral_bonus' => $referralProgram->referral_bonus,
                'referee_bonus' => $referralProgram->referee_bonus,
                'minimum_investment' => $referralProgram->minimum_investment,
                'bonus_type' => $referralProgram->bonus_type,
                'terms_conditions' => $referralProgram->terms_conditions
            ]
        ]);
    }

    public function getReferralHistory()
    {
        $referrals = Auth::user()->referrals()
            ->with('profile')
            ->select(['id', 'name', 'email', 'created_at', 'first_investment_date'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'referrals' => $referrals
        ]);
    }

    public function applyCampaignCode(Request $request)
    {
        $request->validate([
            'campaign_code' => 'required|string|exists:campaigns,code'
        ]);

        $campaign = Campaign::where('code', $request->campaign_code)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired campaign code'
            ], 400);
        }

        if ($campaign->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign code already used'
            ], 400);
        }

        if ($campaign->max_participants &&
            $campaign->participants()->count() >= $campaign->max_participants) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign has reached maximum participants'
            ], 400);
        }

        $campaign->participants()->create([
            'user_id' => Auth::id(),
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign code applied successfully',
            'campaign' => $campaign
        ]);
    }
}
