<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ApiTokenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    public function __construct(
        private ApiTokenService $apiTokenService
    ) {}

    /**
     * Display API tokens
     */
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $canCreate = $this->apiTokenService->canCreateToken($request->user());

        if (!$canCreate['allowed']) {
            return Inertia::render('GrowFinance/FeatureUpgradeRequired', [
                'feature' => 'API Access',
                'requiredTier' => 'business',
                'message' => $canCreate['reason'],
            ]);
        }

        $tokens = $this->apiTokenService->getTokens($businessId);
        $abilities = $this->apiTokenService->getAvailableAbilities();

        return Inertia::render('GrowFinance/Api/Index', [
            'tokens' => $tokens,
            'abilities' => $abilities,
        ]);
    }

    /**
     * Create new API token
     */
    public function store(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $canCreate = $this->apiTokenService->canCreateToken($request->user());
        if (!$canCreate['allowed']) {
            return back()->with('error', $canCreate['reason']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'string',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $result = $this->apiTokenService->createToken(
            $businessId,
            $validated['name'],
            $validated['abilities'],
            $validated['expires_in_days'] ?? null
        );

        // Flash the plain token to show once
        return back()->with([
            'success' => 'API token created!',
            'newToken' => $result['plain_token'],
            'tokenMessage' => $result['message'],
        ]);
    }

    /**
     * Revoke (deactivate) token
     */
    public function revoke(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $this->apiTokenService->revokeToken($businessId, $id);

        return back()->with('success', 'Token revoked.');
    }

    /**
     * Delete token permanently
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $this->apiTokenService->deleteToken($businessId, $id);

        return back()->with('success', 'Token deleted.');
    }

    /**
     * Regenerate token
     */
    public function regenerate(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $result = $this->apiTokenService->regenerateToken($businessId, $id);

        return back()->with([
            'success' => 'Token regenerated!',
            'newToken' => $result['plain_token'],
            'tokenMessage' => $result['message'],
        ]);
    }

    /**
     * API documentation page
     */
    public function documentation(Request $request): Response
    {
        return Inertia::render('GrowFinance/Api/Documentation', [
            'baseUrl' => config('app.url') . '/api/growfinance/v1',
        ]);
    }
}
