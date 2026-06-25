<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\WalletService;
use App\Domain\BizBoost\Services\BillingLedgerService;
use App\Infrastructure\Services\MetaAdsMarketingService;
use App\Infrastructure\Persistence\Eloquent\BizBoostAdCampaignModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AdCampaignController extends Controller
{
    public function __construct(
        private WalletService $wallet,
        private BillingLedgerService $ledger,
        private MetaAdsMarketingService $meta,
    ) {}

    private function getMarkupPercentage(): int
    {
        return (int) config('modules.bizboost.markup_percentage', 20);
    }

    public function index()
    {
        $user = auth()->user();
        $campaigns = BizBoostAdCampaignModel::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $all = BizBoostAdCampaignModel::where('user_id', $user->id);

        return Inertia::render('BizBoost/AdCampaigns/Index', [
            'campaigns' => $campaigns,
            'userCurrency' => $user->user_currency ?? 'ZMW',
            'stats' => [
                'total' => (clone $all)->count(),
                'active' => (clone $all)->where('status', 'active')->count(),
                'paused' => (clone $all)->where('status', 'paused')->count(),
                'draft' => (clone $all)->where('status', 'draft')->count(),
                'completed' => (clone $all)->where('status', 'completed')->count(),
            ],
            'wallet' => [
                'available' => $this->wallet->getAvailableBalance($user->id),
                'balance' => $this->wallet->getBalance($user->id),
            ],
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        return Inertia::render('BizBoost/AdCampaigns/Create', [
            'wallet_available' => $this->wallet->getAvailableBalance($user->id),
            'markup_percentage' => $this->getMarkupPercentage(),
            'userCurrency' => $user->user_currency ?? 'ZMW',
            'objectives' => [
                ['value' => 'OUTCOME_TRAFFIC', 'label' => 'Traffic', 'description' => 'Drive visitors to your website or app'],
                ['value' => 'OUTCOME_ENGAGEMENT', 'label' => 'Engagement', 'description' => 'Get more likes, comments, and shares'],
                ['value' => 'OUTCOME_LEADS', 'label' => 'Leads', 'description' => 'Collect inquiries and sign-ups'],
                ['value' => 'OUTCOME_SALES', 'label' => 'Sales', 'description' => 'Drive online sales and conversions'],
                ['value' => 'OUTCOME_AWARENESS', 'label' => 'Brand Awareness', 'description' => 'Increase brand recognition'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'objective' => 'required|string',
            'client_budget' => 'required|numeric|min:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'targeting' => 'nullable|array',
            'creatives' => 'nullable|array',
        ]);

        $startDate = $validated['start_date'] ?? now()->toDateString();
        $endDate = $validated['end_date'] ?? now()->addDays(30)->toDateString();
        $durationDays = now()->diffInDays($endDate) + 1;

        $clientBudget = $validated['client_budget'];
        $metaBudget = $clientBudget / (1 + $this->getMarkupPercentage() / 100);
        $markup = $clientBudget - $metaBudget;

        if (!$this->wallet->hasSufficientFunds($user->id, $clientBudget)) {
            return redirect()->back()->withErrors([
                'budget' => "Insufficient wallet balance. Need {$clientBudget}, have {$this->wallet->getAvailableBalance($user->id)}",
            ]);
        }

        $this->wallet->lockFunds($user->id, $clientBudget, 'campaign_lock_' . str_replace(' ', '_', $validated['name']));

        $campaign = BizBoostAdCampaignModel::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'objective' => $validated['objective'],
            'client_budget' => $clientBudget,
            'meta_budget' => round($metaBudget, 4),
            'platform_markup' => round($markup, 4),
            'status' => 'pending',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
            'targeting' => $validated['targeting'],
            'creatives' => $validated['creatives'],
        ]);

        try {
            $result = $this->meta->createCampaign([
                'name' => $validated['name'],
                'objective' => $this->resolveObjective($validated['objective']),
                'status' => 'PAUSED',
            ]);

            if ($result['success'] && isset($result['id'])) {
                $campaign->update([
                    'meta_campaign_id' => $result['id'],
                    'status' => 'draft',
                ]);

                if (!empty($validated['creatives'])) {
                    $dailyBudget = round($metaBudget / $campaign->duration_days, 2);
                    $adSetResult = $this->meta->createAdSet($result['id'], [
                        'name' => $validated['name'] . ' - Ad Set',
                        'daily_budget' => $dailyBudget,
                        'lifetime_budget' => $metaBudget,
                        'start_time' => $validated['start_date'],
                        'end_time' => $validated['end_date'],
                        'targeting' => $validated['targeting'],
                    ]);

                    if ($adSetResult['success'] && isset($adSetResult['id'])) {
                        $campaign->update(['meta_ad_set_id' => $adSetResult['id']]);
                    }
                }
            } else {
                $campaign->update(['status' => 'failed', 'error_message' => $result['error'] ?? 'Meta campaign creation failed']);
                $this->wallet->releaseLockedFunds($user->id, $clientBudget, 'campaign_release_' . $campaign->id);
            }
        } catch (\Exception $e) {
            Log::error("Ad campaign creation failed", ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
            $campaign->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            $this->wallet->releaseLockedFunds($user->id, $clientBudget, 'campaign_release_' . $campaign->id);
        }

        return redirect()->route('bizboost.ad-campaigns.show', $campaign->id)
            ->with('success', 'Campaign created');
    }

    public function show(int $id)
    {
        $campaign = BizBoostAdCampaignModel::where('user_id', auth()->id())->findOrFail($id);

        $insights = null;
        if ($campaign->meta_campaign_id && $campaign->status === 'active') {
            $raw = $this->meta->getInsights($campaign->meta_campaign_id);
            if ($raw['success'] && isset($raw['data']['data'][0])) {
                $row = $raw['data']['data'][0];
                $insights = [
                    'impressions' => (int) ($row['impressions'] ?? 0),
                    'clicks' => (int) ($row['clicks'] ?? 0),
                    'spend' => (float) ($row['spend'] ?? 0),
                    'ctr' => (float) ($row['ctr'] ?? 0),
                    'cpc' => (float) ($row['cpc'] ?? 0),
                    'reach' => (int) ($row['reach'] ?? 0),
                    'frequency' => (float) ($row['frequency'] ?? 0),
                    'date_start' => $row['date_start'] ?? '',
                    'date_stop' => $row['date_stop'] ?? '',
                ];
            }
        }

        return Inertia::render('BizBoost/AdCampaigns/Show', [
            'campaign' => $campaign,
            'insights' => $insights,
            'userCurrency' => $user->user_currency ?? 'ZMW',
        ]);
    }

    public function launch(int $id)
    {
        $campaign = BizBoostAdCampaignModel::where('user_id', auth()->id())->findOrFail($id);

        if (!$campaign->meta_campaign_id) {
            return redirect()->back()->with('error', 'Campaign not created on Meta yet');
        }

        $user = auth()->user();

        $this->wallet->releaseLockedFunds($user->id, $campaign->client_budget, 'campaign_release_' . $campaign->id);

        $this->wallet->withdraw(
            userId: $user->id,
            amount: $campaign->client_budget,
            reference: 'campaign_' . $campaign->id,
            description: "Ad campaign: {$campaign->name}",
        );

        $result = $this->meta->resumeCampaign($campaign->meta_campaign_id);

        if ($result) {
            $campaign->update(['status' => 'active']);

            $this->ledger->recordTransaction(
                userId: $user->id,
                serviceType: 'facebook_ad',
                grossAmountCharged: $campaign->client_budget,
                netVendorCost: $campaign->meta_budget,
                campaignId: $campaign->id,
                vendor: 'meta_marketing_api',
                deliveryStatus: 'success',
                meta: ['meta_campaign_id' => $campaign->meta_campaign_id],
            );

            return redirect()->back()->with('success', 'Campaign launched');
        }

        return redirect()->back()->with('error', 'Failed to launch campaign on Meta');
    }

    public function pause(int $id)
    {
        $campaign = BizBoostAdCampaignModel::where('user_id', auth()->id())->findOrFail($id);

        if ($campaign->meta_campaign_id && $this->meta->pauseCampaign($campaign->meta_campaign_id)) {
            $campaign->update(['status' => 'paused']);
            return redirect()->back()->with('success', 'Campaign paused');
        }

        return redirect()->back()->with('error', 'Failed to pause campaign');
    }

    public function resume(int $id)
    {
        $campaign = BizBoostAdCampaignModel::where('user_id', auth()->id())->findOrFail($id);

        if ($campaign->meta_campaign_id && $this->meta->resumeCampaign($campaign->meta_campaign_id)) {
            $campaign->update(['status' => 'active']);
            return redirect()->back()->with('success', 'Campaign resumed');
        }

        return redirect()->back()->with('error', 'Failed to resume campaign');
    }

    private function resolveObjective(string $objective): string
    {
        return match ($objective) {
            'traffic' => 'OUTCOME_TRAFFIC',
            'engagement' => 'OUTCOME_ENGAGEMENT',
            'leads' => 'OUTCOME_LEADS',
            'sales' => 'OUTCOME_SALES',
            'awareness' => 'OUTCOME_AWARENESS',
            default => 'OUTCOME_TRAFFIC',
        };
    }
}
