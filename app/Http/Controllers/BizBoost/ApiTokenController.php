<?php

namespace App\Http\Controllers\BizBoost;
use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ApiTokenController extends Controller
{
    private const MODULE_ID = 'bizboost';

    public function __construct(
        private SubscriptionService $subscriptionService,
        private BusinessService $businessService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $hasAccess = $this->subscriptionService->hasFeature($user, 'api_access', self::MODULE_ID);

        if (!$hasAccess) {
            return Inertia::render('BizBoost/FeatureUpgradeRequired', [
                'feature' => 'API Access',
                'description' => 'Integrate BizBoost with your own applications using our REST API.',
                'requiredTier' => 'business',
            ]);
        }

        $business = $this->businessService->getBusinessOrFail($user->id);

        $tokens = DB::table('bizboost_api_tokens')
            ->where('business_id', $business->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($token) {
                $token->abilities = json_decode($token->abilities, true);
                $token->is_expired = $token->expires_at && now()->gt($token->expires_at);
                return $token;
            });

        return Inertia::render('BizBoost/Api/Index', [
            'tokens' => $tokens,
            'hasApiAccess' => true,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$this->subscriptionService->hasFeature($user, 'api_access', self::MODULE_ID)) {
            return back()->withErrors(['access' => 'API access requires Business tier.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'required|array',
            'abilities.*' => 'in:read,write,delete',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $business = $this->businessService->getBusinessOrFail($user->id);
        $token = Str::random(64);

        DB::table('bizboost_api_tokens')->insert([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'token' => hash('sha256', $token),
            'abilities' => json_encode($validated['abilities']),
            'expires_at' => $validated['expires_in_days'] ? now()->addDays($validated['expires_in_days']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(['success' => 'API token created.', 'newToken' => $token]);
    }

    public function destroy(Request $request, $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        DB::table('bizboost_api_tokens')->where('id', $id)->where('business_id', $business->id)->delete();
        return back()->with('success', 'API token revoked.');
    }

    public function documentation()
    {
        return Inertia::render('BizBoost/Api/Documentation');
    }
}
