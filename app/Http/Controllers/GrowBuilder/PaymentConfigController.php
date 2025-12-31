<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\GrowBuilder\SitePaymentConfig;
use App\Models\GrowBuilder\SitePaymentTransaction;
use App\Domain\GrowBuilder\Payment\Services\PaymentGatewayFactory;
use App\Domain\GrowBuilder\Payment\Enums\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class PaymentConfigController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show payment configuration page
     */
    public function index(int $site)
    {
        $siteModel = GrowBuilderSite::findOrFail($site);
        
        // Simple ownership check instead of policy
        if ($siteModel->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $config = SitePaymentConfig::where('site_id', $site)
            ->where('is_active', true)
            ->first();
        
        $availableGateways = PaymentGatewayFactory::getAvailableGateways();

        return Inertia::render('GrowBuilder/Settings/PaymentConfig', [
            'site' => $siteModel,
            'config' => $config ? [
                'id' => $config->id,
                'gateway' => $config->gateway,
                'is_active' => $config->is_active,
                'test_mode' => $config->test_mode,
                'settings' => $config->settings,
            ] : null,
            'availableGateways' => $availableGateways,
        ]);
    }

    /**
     * Get gateway configuration fields
     */
    public function getGatewayFields(Request $request)
    {
        $request->validate([
            'gateway' => 'required|string',
        ]);

        $gateway = PaymentGateway::from($request->gateway);
        $fields = PaymentGatewayFactory::getGatewayFields($gateway);

        return response()->json([
            'fields' => $fields,
        ]);
    }

    /**
     * Store or update payment configuration
     */
    public function store(Request $request, int $site)
    {
        $siteModel = GrowBuilderSite::findOrFail($site);
        
        // Simple ownership check
        if ($siteModel->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'gateway' => 'required|string',
            'credentials' => 'required|array',
            'test_mode' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Validate gateway credentials
        $gateway = PaymentGateway::from($validated['gateway']);
        $gatewayInstance = PaymentGatewayFactory::create(
            $gateway,
            $validated['credentials'],
            $validated['test_mode'] ?? false
        );

        $validation = $gatewayInstance->validateConfiguration($validated['credentials']);

        if (!$validation['valid']) {
            return back()->withErrors([
                'credentials' => $validation['errors'],
            ]);
        }

        // Deactivate existing configs
        SitePaymentConfig::where('site_id', $site)->update(['is_active' => false]);

        // Create or update config
        $config = SitePaymentConfig::updateOrCreate(
            [
                'site_id' => $site,
                'gateway' => $validated['gateway']
            ],
            [
                'credentials' => $validated['credentials'],
                'test_mode' => $validated['test_mode'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'webhook_secret' => bin2hex(random_bytes(32)),
            ]
        );

        return back()->with('success', 'Payment configuration saved successfully');
    }

    /**
     * Delete payment configuration
     */
    public function destroy(int $site, SitePaymentConfig $config)
    {
        $siteModel = GrowBuilderSite::findOrFail($site);
        
        // Simple ownership check
        if ($siteModel->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        if ($config->site_id !== $site) {
            abort(403);
        }

        $config->delete();

        return back()->with('success', 'Payment configuration deleted successfully');
    }

    /**
     * Get transactions for a site
     */
    public function transactions(int $site)
    {
        $siteModel = GrowBuilderSite::findOrFail($site);
        
        // Simple ownership check
        if ($siteModel->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $transactions = SitePaymentTransaction::where('site_id', $site)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($transactions);
    }

    /**
     * Test payment configuration
     */
    public function test(Request $request, int $site)
    {
        $siteModel = GrowBuilderSite::findOrFail($site);
        
        // Simple ownership check
        if ($siteModel->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $config = SitePaymentConfig::where('site_id', $site)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'No payment configuration found',
            ], 404);
        }

        try {
            $gateway = PaymentGatewayFactory::create(
                PaymentGateway::from($config->gateway),
                $config->decryptedCredentials(),
                $config->test_mode
            );

            $validation = $gateway->validateConfiguration($config->decryptedCredentials());

            return response()->json([
                'success' => $validation['valid'],
                'message' => $validation['valid'] 
                    ? 'Configuration is valid' 
                    : 'Configuration has errors',
                'errors' => $validation['errors'] ?? [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
