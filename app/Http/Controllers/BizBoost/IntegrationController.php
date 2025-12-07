<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class IntegrationController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): Response
    {
        $business = $this->getBusiness($request);
        
        $integrations = $business->integrations()
            ->get()
            ->map(function ($integration) {
                return [
                    'id' => $integration->id,
                    'provider' => $integration->provider,
                    'provider_page_name' => $integration->provider_page_name,
                    'status' => $integration->status,
                    'connected_at' => $integration->connected_at,
                    'last_used_at' => $integration->last_used_at,
                    'is_expired' => $integration->token_expires_at && $integration->token_expires_at < now(),
                ];
            });

        $canAutoPost = $this->subscriptionService->hasFeature(
            $request->user(), 'auto_posting', 'bizboost'
        );

        $availableProviders = [
            [
                'id' => 'facebook',
                'name' => 'Facebook',
                'description' => 'Post to your Facebook Page automatically',
                'icon' => 'facebook',
            ],
            [
                'id' => 'instagram',
                'name' => 'Instagram',
                'description' => 'Share content to Instagram Business account',
                'icon' => 'instagram',
            ],
            [
                'id' => 'whatsapp',
                'name' => 'WhatsApp Business',
                'description' => 'Send messages via WhatsApp Business API',
                'icon' => 'whatsapp',
            ],
        ];

        return Inertia::render('BizBoost/Integrations/Index', [
            'integrations' => $integrations,
            'canAutoPost' => $canAutoPost,
            'facebookAppId' => config('services.facebook.client_id'),
            'availableProviders' => $availableProviders,
        ]);
    }

    public function connectFacebook(Request $request)
    {
        // Check if user has auto_posting feature
        if (!$this->subscriptionService->hasFeature($request->user(), 'auto_posting', 'bizboost')) {
            return redirect()->route('bizboost.upgrade')
                ->with('error', 'Auto-posting requires Professional plan or higher.');
        }

        $appId = config('services.facebook.client_id');
        $redirectUri = route('bizboost.integrations.facebook.callback');
        $scopes = 'pages_manage_posts,pages_read_engagement,pages_read_user_content,instagram_basic,instagram_content_publish';
        
        $state = Crypt::encryptString(json_encode([
            'user_id' => $request->user()->id,
            'timestamp' => now()->timestamp,
        ]));

        $url = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'scope' => $scopes,
            'state' => $state,
            'response_type' => 'code',
        ]);

        return redirect($url);
    }

    public function facebookCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('bizboost.integrations.index')
                ->with('error', 'Facebook connection was cancelled.');
        }

        try {
            $state = json_decode(Crypt::decryptString($request->state), true);
            
            // Verify state
            if ($state['user_id'] !== $request->user()->id) {
                throw new \Exception('Invalid state');
            }

            // Exchange code for access token
            $response = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'redirect_uri' => route('bizboost.integrations.facebook.callback'),
                'code' => $request->code,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to get access token');
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'];

            // Get long-lived token
            $longLivedResponse = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'fb_exchange_token' => $accessToken,
            ]);

            if ($longLivedResponse->successful()) {
                $longLivedData = $longLivedResponse->json();
                $accessToken = $longLivedData['access_token'];
                $expiresIn = $longLivedData['expires_in'] ?? 5184000; // 60 days default
            }

            // Get user's pages
            $pagesResponse = Http::get('https://graph.facebook.com/v18.0/me/accounts', [
                'access_token' => $accessToken,
                'fields' => 'id,name,access_token,instagram_business_account',
            ]);

            if (!$pagesResponse->successful()) {
                throw new \Exception('Failed to get pages');
            }

            $pages = $pagesResponse->json()['data'] ?? [];

            if (empty($pages)) {
                return redirect()->route('bizboost.integrations.index')
                    ->with('error', 'No Facebook Pages found. Please create a Facebook Page first.');
            }

            // Store in session for page selection
            session(['facebook_pages' => $pages, 'facebook_user_token' => $accessToken]);

            return redirect()->route('bizboost.integrations.facebook.select');

        } catch (\Exception $e) {
            return redirect()->route('bizboost.integrations.index')
                ->with('error', 'Failed to connect Facebook: ' . $e->getMessage());
        }
    }

    public function selectFacebookPage(Request $request): Response
    {
        $pages = session('facebook_pages', []);

        if (empty($pages)) {
            return redirect()->route('bizboost.integrations.index')
                ->with('error', 'No pages found. Please try connecting again.');
        }

        return Inertia::render('BizBoost/Integrations/SelectPage', [
            'pages' => $pages,
            'provider' => 'facebook',
        ]);
    }

    public function storeFacebookPage(Request $request)
    {
        $validated = $request->validate([
            'page_id' => 'required|string',
        ]);

        $pages = session('facebook_pages', []);
        $selectedPage = collect($pages)->firstWhere('id', $validated['page_id']);

        if (!$selectedPage) {
            return back()->with('error', 'Invalid page selected.');
        }

        $business = $this->getBusiness($request);

        // Store Facebook Page integration
        BizBoostIntegrationModel::updateOrCreate(
            [
                'business_id' => $business->id,
                'provider' => 'facebook',
                'provider_page_id' => $selectedPage['id'],
            ],
            [
                'provider_page_name' => $selectedPage['name'],
                'access_token' => Crypt::encryptString($selectedPage['access_token']),
                'token_expires_at' => now()->addDays(60),
                'status' => 'active',
                'connected_at' => now(),
                'meta' => [
                    'has_instagram' => isset($selectedPage['instagram_business_account']),
                ],
            ]
        );

        // If page has Instagram Business Account, store that too
        if (isset($selectedPage['instagram_business_account'])) {
            BizBoostIntegrationModel::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'provider' => 'instagram',
                    'provider_page_id' => $selectedPage['instagram_business_account']['id'],
                ],
                [
                    'provider_page_name' => $selectedPage['name'] . ' (Instagram)',
                    'access_token' => Crypt::encryptString($selectedPage['access_token']),
                    'token_expires_at' => now()->addDays(60),
                    'status' => 'active',
                    'connected_at' => now(),
                    'meta' => [
                        'facebook_page_id' => $selectedPage['id'],
                    ],
                ]
            );
        }

        // Clear session
        session()->forget(['facebook_pages', 'facebook_user_token']);

        return redirect()->route('bizboost.integrations.index')
            ->with('success', 'Facebook Page connected successfully!');
    }

    public function disconnect(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $integration = $business->integrations()->findOrFail($id);

        $integration->update([
            'status' => 'revoked',
            'access_token' => null,
            'refresh_token' => null,
        ]);

        return back()->with('success', ucfirst($integration->provider) . ' disconnected.');
    }

    public function refresh(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $integration = $business->integrations()->findOrFail($id);

        if ($integration->provider !== 'facebook') {
            return back()->with('error', 'Token refresh not supported for this provider.');
        }

        // Redirect to reconnect
        return redirect()->route('bizboost.integrations.facebook.connect');
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
