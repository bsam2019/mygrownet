<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\IntegrationService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    public function __construct(
        private IntegrationService $integrationService,
        private BusinessService $businessService,
        private IntegrationRepositoryInterface $integrationRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Integrations/Index', [
            'integrations' => $this->integrationRepo->findByBusiness($business->id),
            'platforms' => config('modules.bizboost.social_platforms', [
                'facebook' => ['name' => 'Facebook', 'icon' => 'facebook', 'color' => '#1877F2'],
                'instagram' => ['name' => 'Instagram', 'icon' => 'instagram', 'color' => '#E4405F'],
                'twitter' => ['name' => 'Twitter / X', 'icon' => 'twitter', 'color' => '#1DA1F2'],
                'linkedin' => ['name' => 'LinkedIn', 'icon' => 'linkedin', 'color' => '#0A66C2'],
                'whatsapp' => ['name' => 'WhatsApp Business', 'icon' => 'whatsapp', 'color' => '#25D366'],
            ]),
            'whatsapp_number' => $business->whatsapp,
        ]);
    }

    public function connect(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:facebook,instagram,twitter,linkedin,whatsapp',
            'access_token' => 'required_if:platform,facebook,instagram,twitter,linkedin|string',
            'phone_number' => 'required_if:platform,whatsapp|string|max:20',
            'account_name' => 'nullable|string|max:255',
            'account_id' => 'nullable|string|max:255',
            'page_id' => 'nullable|string|max:255',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->integrationService->connectPlatform($business->id, $validated);

        return back()->with('success', 'Connected to ' . ucfirst($validated['platform']) . '.');
    }

    public function disconnect(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->integrationService->disconnectPlatform($business->id, $id);

        return back()->with('success', 'Integration disconnected.');
    }

    public function verify(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $result = $this->integrationService->verifyConnection($business->id, $id);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function webhook(Request $request, string $platform)
    {
        return $this->integrationService->handleWebhook($platform, $request);
    }
}