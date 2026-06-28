<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\BizBoost\Services\SocialMedia\FacebookService;
use App\Domain\BizBoost\Services\SocialMedia\InstagramService;
use App\Domain\BizBoost\Services\SocialMedia\SocialMediaFactory;
use App\Domain\BizBoost\Services\SocialMedia\TikTokService;
use App\Domain\BizBoost\Services\SocialMedia\WhatsAppService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SocialMediaIntegrationController extends Controller
{
    public function index(Request $request)
    {
        $business = BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();

        $integrations = BizBoostIntegrationModel::where('business_id', $business->id)
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'provider' => $i->provider,
                'status' => $i->status,
                'account_name' => $i->provider_page_name,
                'connected_at' => $i->connected_at?->toISOString(),
                'last_sync_at' => $i->updated_at?->toISOString(),
            ]);

        $supportedProviders = SocialMediaFactory::getSupportedProviders();
        
        // Transform to format expected by Vue component
        $availableProviders = collect($supportedProviders)->map(fn($provider, $id) => [
            'id' => $id,
            'name' => $provider['name'],
            'description' => match($id) {
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

        // Filter out already connected providers
        $connectedProviders = $integrations->pluck('provider')->toArray();
        $availableProviders = array_filter($availableProviders, fn($p) => !in_array($p['id'], $connectedProviders));

        return Inertia::render('BizBoost/Integrations/Index', [
            'integrations' => $integrations->values(),
            'availableProviders' => array_values($availableProviders),
            'canAutoPost' => true, // TODO: Check subscription tier
        ]);
    }

    public function connect(Request $request, string $provider)
    {
        // Provider is validated by route constraint: facebook|instagram|whatsapp|tiktok
        $business = BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();

        $redirectUri = route('bizboost.integrations.callback', ['provider' => $provider]);

        try {
            $service = SocialMediaFactory::make($provider);
            $authUrl = $service->getAuthUrl($redirectUri);

            // Store business ID in session for callback
            session(['bizboost_integration_business_id' => $business->id]);
            session(['bizboost_integration_provider' => $provider]);

            return redirect($authUrl);
        } catch (\Exception $e) {
            return back()->with('error', "Failed to connect to {$provider}: {$e->getMessage()}");
        }
    }

    public function callback(Request $request, string $provider)
    {
        if ($request->has('error')) {
            return redirect()->route('bizboost.integrations.index')
                ->with('error', 'Authorization was denied or cancelled.');
        }

        $code = $request->input('code');
        $businessId = session('bizboost_integration_business_id');

        if (!$code || !$businessId) {
            return redirect()->route('bizboost.integrations.index')
                ->with('error', 'Invalid callback request.');
        }

        try {
            $redirectUri = route('bizboost.integrations.callback', ['provider' => $provider]);
            $service = SocialMediaFactory::make($provider);

            // Exchange code for token
            $tokenData = $service->exchangeCodeForToken($code, $redirectUri);

            // Handle provider-specific setup
            $integrationData = match ($provider) {
                'facebook' => $this->setupFacebook($service, $tokenData, $businessId),
                'instagram' => $this->setupInstagram($service, $tokenData, $businessId),
                'whatsapp' => $this->setupWhatsApp($service, $tokenData, $businessId),
                'tiktok' => $this->setupTikTok($service, $tokenData, $businessId),
            };

            return redirect()->route('bizboost.integrations.index')
                ->with('success', ucfirst($provider) . ' connected successfully!');

        } catch (\Exception $e) {
            Log::error("Integration callback failed for {$provider}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('bizboost.integrations.index')
                ->with('error', "Failed to complete {$provider} integration: {$e->getMessage()}");
        }
    }

    private function setupFacebook(FacebookService $service, array $tokenData, int $businessId): BizBoostIntegrationModel
    {
        $accessToken = $tokenData['access_token'];

        // Get user's pages
        $pages = $service->getUserPages($accessToken);

        if (empty($pages)) {
            throw new \Exception('No Facebook pages found. Please create a page first.');
        }

        // Use the first page (or let user select in future enhancement)
        $page = $pages[0];

        return BizBoostIntegrationModel::updateOrCreate(
            [
                'business_id' => $businessId,
                'provider' => 'facebook',
            ],
            [
                'provider_user_id' => $page['id'],
                'provider_page_id' => $page['id'],
                'provider_page_name' => $page['name'],
                'access_token' => $page['access_token'], // Use page token
                'token_expires_at' => null, // Long-lived tokens don't expire
                'scopes' => $tokenData['scope'] ?? null,
                'status' => 'active',
                'connected_at' => now(),
                'meta' => [
                    'category' => $page['category'] ?? null,
                    'tasks' => $page['tasks'] ?? [],
                ],
            ]
        );
    }

    private function setupInstagram(InstagramService $service, array $tokenData, int $businessId): BizBoostIntegrationModel
    {
        $accessToken = $tokenData['access_token'];

        // Get Facebook pages first
        $facebookService = new FacebookService();
        $pages = $facebookService->getUserPages($accessToken);

        if (empty($pages)) {
            throw new \Exception('No Facebook pages found. Connect a Facebook page with Instagram first.');
        }

        // Find page with Instagram account
        $instagramAccount = null;
        $connectedPage = null;

        foreach ($pages as $page) {
            $igAccount = $service->getInstagramBusinessAccount($page['id'], $page['access_token']);
            if ($igAccount) {
                $instagramAccount = $igAccount;
                $connectedPage = $page;
                break;
            }
        }

        if (!$instagramAccount) {
            throw new \Exception('No Instagram Business account found. Please connect your Instagram account to a Facebook page.');
        }

        return BizBoostIntegrationModel::updateOrCreate(
            [
                'business_id' => $businessId,
                'provider' => 'instagram',
            ],
            [
                'provider_user_id' => $instagramAccount['id'],
                'provider_page_id' => $connectedPage['id'],
                'provider_page_name' => $instagramAccount['username'],
                'access_token' => $connectedPage['access_token'],
                'token_expires_at' => null,
                'scopes' => $tokenData['scope'] ?? null,
                'status' => 'active',
                'connected_at' => now(),
                'meta' => [
                    'username' => $instagramAccount['username'],
                    'profile_picture' => $instagramAccount['profile_picture_url'] ?? null,
                ],
            ]
        );
    }

    private function setupWhatsApp(WhatsAppService $service, array $tokenData, int $businessId): BizBoostIntegrationModel
    {
        $accessToken = $tokenData['access_token'];

        // Get WhatsApp Business accounts
        $wabaAccounts = $service->getWhatsAppBusinessAccounts($accessToken);

        if (empty($wabaAccounts)) {
            throw new \Exception('No WhatsApp Business accounts found.');
        }

        $waba = $wabaAccounts[0];
        $wabaId = $waba['client_whatsapp_business_accounts'][0]['id'] ?? null;

        if (!$wabaId) {
            throw new \Exception('Invalid WhatsApp Business account data.');
        }

        // Get phone numbers
        $phoneNumbers = $service->getPhoneNumbers($wabaId, $accessToken);

        if (empty($phoneNumbers)) {
            throw new \Exception('No phone numbers found for WhatsApp Business account.');
        }

        $phone = $phoneNumbers[0];

        return BizBoostIntegrationModel::updateOrCreate(
            [
                'business_id' => $businessId,
                'provider' => 'whatsapp',
            ],
            [
                'provider_user_id' => $wabaId,
                'provider_page_id' => $phone['id'],
                'provider_page_name' => $phone['display_phone_number'],
                'access_token' => $accessToken,
                'token_expires_at' => isset($tokenData['expires_in'])
                    ? now()->addSeconds($tokenData['expires_in'])
                    : null,
                'scopes' => $tokenData['scope'] ?? null,
                'status' => 'active',
                'connected_at' => now(),
                'meta' => [
                    'verified_name' => $phone['verified_name'] ?? null,
                    'code_verification_status' => $phone['code_verification_status'] ?? null,
                    'quality_rating' => $phone['quality_rating'] ?? null,
                ],
            ]
        );
    }

    private function setupTikTok(TikTokService $service, array $tokenData, int $businessId): BizBoostIntegrationModel
    {
        $accessToken = $tokenData['access_token'];

        // Get user info
        $userInfo = $service->getUserInfo($accessToken);

        return BizBoostIntegrationModel::updateOrCreate(
            [
                'business_id' => $businessId,
                'provider' => 'tiktok',
            ],
            [
                'provider_user_id' => $userInfo['open_id'],
                'provider_page_id' => $userInfo['open_id'],
                'provider_page_name' => $userInfo['display_name'],
                'access_token' => $accessToken,
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => isset($tokenData['expires_in'])
                    ? now()->addSeconds($tokenData['expires_in'])
                    : null,
                'scopes' => $tokenData['scope'] ?? null,
                'status' => 'active',
                'connected_at' => now(),
                'meta' => [
                    'union_id' => $userInfo['union_id'] ?? null,
                    'avatar_url' => $userInfo['avatar_url'] ?? null,
                ],
            ]
        );
    }

    public function disconnect(Request $request, string $provider)
    {
        $business = BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();

        $integration = BizBoostIntegrationModel::where('business_id', $business->id)
            ->where('provider', $provider)
            ->first();

        if ($integration) {
            $integration->delete();
        }

        return back()->with('success', ucfirst($provider) . ' disconnected successfully.');
    }

    public function refresh(Request $request, string $provider)
    {
        $business = BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();

        $integration = BizBoostIntegrationModel::where('business_id', $business->id)
            ->where('provider', $provider)
            ->firstOrFail();

        try {
            $service = SocialMediaFactory::make($provider, $integration);
            $tokenData = $service->refreshToken();

            $integration->update([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? $integration->refresh_token,
                'token_expires_at' => isset($tokenData['expires_in'])
                    ? now()->addSeconds($tokenData['expires_in'])
                    : null,
                'status' => 'active',
            ]);

            return back()->with('success', 'Token refreshed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to refresh token: {$e->getMessage()}");
        }
    }
}
