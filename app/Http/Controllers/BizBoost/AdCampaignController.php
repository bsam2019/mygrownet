<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\WalletService;
use App\Domain\BizBoost\Services\BillingLedgerService;
use App\Domain\BizBoost\Repositories\AdCampaignRepositoryInterface;
use App\Infrastructure\Services\MetaAdsMarketingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AdCampaignController extends Controller
{
    public function __construct(
        private WalletService $wallet,
        private BillingLedgerService $ledger,
        private MetaAdsMarketingService $meta,
        private AdCampaignRepositoryInterface $campaignRepo,
    ) {}

    public function index()
    {
        $user = auth()->user();
        $campaigns = $this->campaignRepo->findByUser($user->id);
        $all = $this->campaignRepo->getQueryByUser($user->id);

        return Inertia::render('BizBoost/AdCampaigns/Index', [
            'campaigns' => $campaigns,
            'userCurrency' => $user->user_currency ?? 'ZMW',
            'stats' => [
                'total' => $all->count(),
                'active' => $all->where('status', 'active')->count(),
                'paused' => $all->where('status', 'paused')->count(),
                'draft' => $all->where('status', 'draft')->count(),
                'completed' => $all->where('status', 'completed')->count(),
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

        $campaign = $this->campaignRepo->create([
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
                $this->campaignRepo->update($campaign->id, [
                    'meta_campaign_id' => $result['id'],
                    'status' => 'draft',
                ]);

                if (!empty($validated['creatives'])) {
                    $dailyBudget = round($metaBudget / $durationDays, 2);
                    $adSetResult = $this->meta->createAdSet($result['id'], [
                        'name' => $validated['name'] . ' - Ad Set',
                        'daily_budget' => $dailyBudget,
                        'lifetime_budget' => $metaBudget,
                        'start_time' => $validated['start_date'],
                        'end_time' => $validated['end_date'],
                        'targeting' => $validated['targeting'],
                    ]);

                    if ($adSetResult['success'] && isset($adSetResult['id'])) {
                        $this->campaignRepo->update($campaign->id, ['meta_ad_set_id' => $adSetResult['id']]);
                    }
                }
            } else {
                $this->campaignRepo->update($campaign->id, ['status' => 'failed', 'error_message' => $result['error'] ?? 'Meta campaign creation failed']);
                $this->wallet->releaseLockedFunds($user->id, $clientBudget, 'campaign_release_' . $campaign->id);
            }
        } catch (\Exception $e) {
            Log::error("Ad campaign creation failed", ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
            $this->campaignRepo->update($campaign->id, ['status' => 'failed', 'error_message' => $e->getMessage()]);
            $this->wallet->releaseLockedFunds($user->id, $clientBudget, 'campaign_release_' . $campaign->id);
        }

        return redirect()->route('bizboost.ad-campaigns.show', $campaign->id)
            ->with('success', 'Campaign created');
    }

    public function show(int $id)
    {
        $user = auth()->user();
        $campaign = $this->campaignRepo->findByUserAndId($user->id, $id);

        if (!$campaign) {
            abort(404);
        }

        $insights = null;
        if ($campaign->metaCampaignId && $campaign->status === 'active') {
            $raw = $this->meta->getInsights($campaign->metaCampaignId);
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
            'campaign' => $campaign->toArray(),
            'insights' => $insights,
            'userCurrency' => $user->user_currency ?? 'ZMW',
        ]);
    }

    public function launch(int $id)
    {
        $user = auth()->user();
        $campaign = $this->campaignRepo->findByUserAndId($user->id, $id);

        if (!$campaign) {
            abort(404);
        }

        if (!$campaign->metaCampaignId) {
            return redirect()->back()->with('error', 'Campaign not created on Meta yet');
        }

        $this->wallet->releaseLockedFunds($user->id, $campaign->clientBudget, 'campaign_release_' . $campaign->id);
        $this->wallet->withdraw(
            userId: $user->id,
            amount: $campaign->clientBudget,
            reference: 'campaign_' . $campaign->id,
            description: "Ad campaign: {$campaign->name}",
        );

        $result = $this->meta->resumeCampaign($campaign->metaCampaignId);

        if ($result) {
            $this->campaignRepo->update($campaign->id, ['status' => 'active']);
            $this->ledger->recordTransaction(
                userId: $user->id,
                serviceType: 'facebook_ad',
                grossAmountCharged: $campaign->clientBudget,
                netVendorCost: $campaign->metaBudget,
                campaignId: $campaign->id,
                vendor: 'meta_marketing_api',
                deliveryStatus: 'success',
                meta: ['meta_campaign_id' => $campaign->metaCampaignId],
            );
            return redirect()->back()->with('success', 'Campaign launched');
        }

        return redirect()->back()->with('error', 'Failed to launch campaign on Meta');
    }

    public function pause(int $id)
    {
        $user = auth()->user();
        $campaign = $this->campaignRepo->findByUserAndId($user->id, $id);

        if (!$campaign) {
            abort(404);
        }

        if ($campaign->metaCampaignId && $this->meta->pauseCampaign($campaign->metaCampaignId)) {
            $this->campaignRepo->update($campaign->id, ['status' => 'paused']);
            return redirect()->back()->with('success', 'Campaign paused');
        }
        return redirect()->back()->with('error', 'Failed to pause campaign');
    }

    public function resume(int $id)
    {
        $user = auth()->user();
        $campaign = $this->campaignRepo->findByUserAndId($user->id, $id);

        if (!$campaign) {
            abort(404);
        }

        if ($campaign->metaCampaignId && $this->meta->resumeCampaign($campaign->metaCampaignId)) {
            $this->campaignRepo->update($campaign->id, ['status' => 'active']);
            return redirect()->back()->with('success', 'Campaign resumed');
        }
        return redirect()->back()->with('error', 'Failed to resume campaign');
    }

    private function getMarkupPercentage(): int
    {
        return (int) config('modules.bizboost.markup_percentage', 20);
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