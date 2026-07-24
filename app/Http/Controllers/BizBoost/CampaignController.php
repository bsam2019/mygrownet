<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\MarketingService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\CampaignRepositoryInterface;
use App\Domain\BizBoost\Repositories\PostRepositoryInterface;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function __construct(
        private MarketingService $marketingService,
        private BusinessService $businessService,
        private CampaignRepositoryInterface $campaignRepo,
        private PostRepositoryInterface $postRepo,
        private ProductRepositoryInterface $productRepo,
        private CustomerRepositoryInterface $customerRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $campaigns = $this->campaignRepo->findByBusiness($business->id);

        return Inertia::render('BizBoost/Campaigns/Index', [
            'campaigns' => $campaigns,
            'stats' => $this->marketingService->getCampaignStats($business->id),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Campaigns/Create', [
            'products' => $this->productRepo->findActiveByBusiness($business->id),
            'posts' => $this->postRepo->findByBusiness($business->id, ['status' => 'published']),
            'customers' => $this->customerRepo->findByBusiness($business->id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:promotion,seasonal,launch,event,referral,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'target_audience' => 'nullable|string|max:500',
            'goal' => 'nullable|string|max:500',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:biz_boost_products,id',
            'post_ids' => 'nullable|array',
            'post_ids.*' => 'integer',
            'customer_segment' => 'nullable|string|max:100',
            'status' => 'required|in:draft,active,paused,completed,cancelled',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->createCampaign($business->id, $validated);

        return redirect()->route('bizboost.campaigns.index')
            ->with('success', 'Campaign created successfully.');
    }

    public function show(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $campaign = $this->campaignRepo->findById($business->id, $id);

        if (!$campaign) {
            abort(404);
        }

        $analytics = $this->marketingService->getCampaignAnalytics($business->id, $id);

        return Inertia::render('BizBoost/Campaigns/Show', [
            'campaign' => $campaign->toArray(),
            'analytics' => $analytics,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:promotion,seasonal,launch,event,referral,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'target_audience' => 'nullable|string|max:500',
            'goal' => 'nullable|string|max:500',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:biz_boost_products,id',
            'post_ids' => 'nullable|array',
            'post_ids.*' => 'integer',
            'customer_segment' => 'nullable|string|max:100',
            'status' => 'required|in:draft,active,paused,completed,cancelled',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->updateCampaign($business->id, $id, $validated);

        return redirect()->route('bizboost.campaigns.index')
            ->with('success', 'Campaign updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->deleteCampaign($business->id, $id);

        return redirect()->route('bizboost.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    public function pause(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->updateCampaign($business->id, $id, ['status' => 'paused']);

        return back()->with('success', 'Campaign paused.');
    }

    public function resume(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->updateCampaign($business->id, $id, ['status' => 'active']);

        return back()->with('success', 'Campaign resumed.');
    }
}