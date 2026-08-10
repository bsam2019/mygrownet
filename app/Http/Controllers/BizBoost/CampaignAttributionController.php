<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Services\BizBoost\OmnichannelService;
use App\Services\BizBoost\RevenueAttributionService;
use App\Domain\BizBoost\Services\BusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CampaignAttributionController extends Controller
{
    public function __construct(
        protected OmnichannelService $omnichannelService,
        protected RevenueAttributionService $attributionService,
        protected BusinessService $businessService
    ) {}

    public function index(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $business = $this->businessService->getBusinessByUser($user->id);

        if (!$business) {
            return redirect()->route('bizboost.setup');
        }

        $campaigns = DB::table('bizboost_campaigns')
            ->where('business_id', $business->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $links = DB::table('bizboost_trackable_links')
            ->where('business_id', $business->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('BizBoost/Campaigns/Index', [
            'business' => ['id' => $business->id, 'name' => $business->name, 'slug' => $business->slug],
            'campaigns' => $campaigns,
            'trackableLinks' => $links,
        ]);
    }

    public function createLink(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_url' => 'required|string',
            'destination_type' => 'nullable|string',
        ]);

        $user = $request->user();
        $business = $this->businessService->getBusinessByUser($user->id);

        $shortUrl = $this->omnichannelService->createTrackableLink($business->id, $request->all());

        return redirect()->back()->with('success', "Trackable link created: {$shortUrl}");
    }
}
