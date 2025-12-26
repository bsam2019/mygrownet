<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPaymentSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentSettingsController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    public function index(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $settings = GrowBuilderPaymentSettings::firstOrCreate(
            ['site_id' => $siteId],
            [
                'cod_enabled' => true,
                'whatsapp_enabled' => true,
            ]
        );

        return Inertia::render('GrowBuilder/Settings/Payments', [
            'site' => $this->siteToArray($site),
            'settings' => [
                'momo_enabled' => $settings->momo_enabled,
                'momo_phone' => $settings->momo_phone,
                'momo_sandbox' => $settings->momo_sandbox,
                'airtel_enabled' => $settings->airtel_enabled,
                'airtel_phone' => $settings->airtel_phone,
                'airtel_sandbox' => $settings->airtel_sandbox,
                'cod_enabled' => $settings->cod_enabled,
                'whatsapp_enabled' => $settings->whatsapp_enabled,
                'whatsapp_number' => $settings->whatsapp_number,
                'bank_enabled' => $settings->bank_enabled,
                'bank_name' => $settings->bank_name,
                'bank_account_name' => $settings->bank_account_name,
                'bank_account_number' => $settings->bank_account_number,
                'bank_branch' => $settings->bank_branch,
            ],
        ]);
    }

    public function update(Request $request, int $siteId)
    {
        $site = $this->siteRepository->findById(SiteId::fromInt($siteId));

        if (!$site || $site->getUserId() !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            // MoMo
            'momo_enabled' => 'boolean',
            'momo_phone' => 'nullable|string|max:20',
            'momo_api_user' => 'nullable|string|max:255',
            'momo_api_key' => 'nullable|string|max:255',
            'momo_subscription_key' => 'nullable|string|max:255',
            'momo_sandbox' => 'boolean',
            
            // Airtel
            'airtel_enabled' => 'boolean',
            'airtel_phone' => 'nullable|string|max:20',
            'airtel_client_id' => 'nullable|string|max:255',
            'airtel_client_secret' => 'nullable|string|max:255',
            'airtel_sandbox' => 'boolean',
            
            // Other methods
            'cod_enabled' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'whatsapp_number' => 'nullable|string|max:20',
            'bank_enabled' => 'boolean',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
        ]);

        $settings = GrowBuilderPaymentSettings::firstOrNew(['site_id' => $siteId]);

        // Only update API keys if provided (don't overwrite with null)
        $updateData = collect($validated)->filter(function ($value, $key) {
            if (in_array($key, ['momo_api_key', 'airtel_client_secret'])) {
                return $value !== null;
            }
            return true;
        })->toArray();

        $settings->fill($updateData);
        $settings->save();

        return back()->with('success', 'Payment settings updated');
    }

    private function siteToArray($site): array
    {
        return [
            'id' => $site->getId()->value(),
            'name' => $site->getName(),
            'subdomain' => $site->getSubdomain()->value(),
            'plan' => $site->getPlan()->value(),
        ];
    }
}
