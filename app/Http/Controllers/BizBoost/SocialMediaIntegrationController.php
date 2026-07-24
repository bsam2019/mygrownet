<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\BizBoost\Services\SocialMedia\SocialMediaFactory;
use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SocialMediaIntegrationController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private IntegrationRepositoryInterface $integrationRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        $integrations = $this->integrationRepo->findByBusiness($business->id);

        $supportedProviders = SocialMediaFactory::getSupportedProviders();

        $availableProviders = collect($supportedProviders)->map(fn($provider, $id) => [
            'id' => $id,
            'name' => $provider['name'],
            'description' => match ($id) {
                'facebook' => 'Post to your Facebook Page',
                'instagram' => 'Share photos and videos on Instagram',
                'whatsapp' => 'Send messages via WhatsApp Business',
                'tiktok' => 'Share videos on TikTok',
                default => 'Connect your account',
            },
            'icon' => $provider['icon'],
            'color' => $provider['color'],
            'supports' => $provider['supports'],
        ])->values()->all();

        $whatsappNumber = $business->whatsapp;

        return Inertia::render('BizBoost/SocialMedia/Index', [
            'integrations' => $integrations,
            'availableProviders' => $availableProviders,
            'whatsappNumber' => $whatsappNumber,
        ]);
    }

    public function connect(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:facebook,instagram,twitter,linkedin,whatsapp,tiktok',
            'access_token' => 'required_if:provider,facebook,instagram,twitter,linkedin,tiktok|string',
            'phone_number' => 'required_if:provider,whatsapp|string|max:20',
            'account_name' => 'nullable|string|max:255',
            'account_id' => 'nullable|string|max:255',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        $this->integrationRepo->save(new \App\Domain\BizBoost\Entities\Integration(
            id: null,
            businessId: $business->id,
            platform: $validated['provider'],
            accessToken: $validated['access_token'] ?? $validated['phone_number'],
            accountName: $validated['account_name'] ?? null,
            accountId: $validated['account_id'] ?? null,
            status: 'connected',
            connectedAt: now()->toDateTimeString(),
            lastSyncAt: null,
            settings: null,
            createdAt: null,
            updatedAt: null,
        ));

        return back()->with('success', 'Connected to ' . ucfirst($validated['provider']) . '.');
    }

    public function disconnect(Request $request, $id)
    {
        $this->integrationRepo->delete($id);
        return back()->with('success', 'Integration disconnected.');
    }

    public function callback(Request $request, string $provider)
    {
        $business = $this->businessService->getBusinessByUser($request->user()->id);
        if (!$business) {
            return redirect()->route('bizboost.setup');
        }

        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('bizboost.social-media.index')
                ->with('error', 'Authorization failed. No authorization code received.');
        }

        $this->integrationRepo->save(new \App\Domain\BizBoost\Entities\Integration(
            id: null,
            businessId: $business->id,
            platform: $provider,
            accessToken: $code,
            accountName: $request->query('account_name'),
            accountId: $request->query('account_id'),
            status: 'connected',
            connectedAt: now()->toDateTimeString(),
            lastSyncAt: null,
            settings: null,
            createdAt: null,
            updatedAt: null,
        ));

        return redirect()->route('bizboost.social-media.index')
            ->with('success', 'Connected to ' . ucfirst($provider) . '!');
    }
}