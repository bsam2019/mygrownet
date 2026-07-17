<?php

namespace App\Http\Controllers\StockFlow\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAudit\Extension;
use App\Models\StockAudit\CompanyExtension;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExtensionController extends Controller
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
    ) {}

    public function index()
    {
        $extensions = Extension::withCount('companySubscriptions')->get()->map(fn($e) => [
            'id' => $e->id,
            'code' => $e->code,
            'name' => $e->name,
            'description' => $e->description,
            'version' => $e->version,
            'is_active' => $e->is_active,
            'companies_count' => $e->company_subscriptions_count,
        ]);

        $companies = array_map(fn($c) => [
            'id' => $c->getId(),
            'name' => $c->getName(),
        ], $this->companyRepository->findAll());

        $assignments = CompanyExtension::with('extension')
            ->get()
            ->groupBy('sa_company_id')
            ->map(fn($items) => $items->map(fn($ce) => [
                'extension_id' => $ce->extension_id,
                'extension_code' => $ce->extension->code,
                'status' => $ce->status,
            ]));

        return Inertia::render('StockFlow/Admin/Extensions/Index', [
            'extensions' => $extensions,
            'companies' => $companies,
            'assignments' => $assignments,
        ]);
    }

    public function toggle(Extension $extension)
    {
        $extension->update(['is_active' => !$extension->is_active]);

        return redirect()->back()->with('success', $extension->is_active
            ? "Extension '{$extension->name}' enabled"
            : "Extension '{$extension->name}' disabled");
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'sa_company_id' => 'required|exists:sa_companies,id',
            'extension_id' => 'required|exists:extensions,id',
        ]);

        $extension = Extension::findOrFail($validated['extension_id']);

        CompanyExtension::firstOrCreate(
            [
                'sa_company_id' => $validated['sa_company_id'],
                'extension_id' => $validated['extension_id'],
            ],
            ['status' => 'active', 'subscribed_at' => now()]
        );

        // Sync extension features to company settings
        $this->syncExtensionFeatures($validated['sa_company_id'], $extension, true);

        return redirect()->back()->with('success', 'Extension assigned to company');
    }

    public function revoke(CompanyExtension $companyExtension)
    {
        $extension = $companyExtension->extension;
        $companyId = $companyExtension->sa_company_id;

        // Sync extension features off
        $this->syncExtensionFeatures($companyId, $extension, false);

        $companyExtension->delete();

        return redirect()->back()->with('success', 'Extension removed from company');
    }

    private function syncExtensionFeatures(int $companyId, Extension $extension, bool $enabled): void
    {
        $company = SaCompanyModel::find($companyId);
        if (!$company) return;

        $providerClass = $extension->provider_class;
        if (!class_exists($providerClass)) return;

        $provider = app($providerClass);
        $features = method_exists($provider, 'getFeatures') ? $provider->getFeatures() : [];

        $settings = $company->settings ?? [];
        $featuresEnabled = $settings['features_enabled'] ?? [];

        foreach ($features as $feature) {
            $featuresEnabled[$feature] = $enabled;
        }

        $settings['features_enabled'] = $featuresEnabled;
        $company->settings = $settings;
        $company->save();
    }
}
