<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DashboardService;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaBackupConfigModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCustomDomainModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaEmailSettingModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaApiKeyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $company = null;
        if ($companyId) {
            $repo = app(\App\Domain\StockFlow\Repositories\CompanyRepositoryInterface::class);
            $company = $repo->findById(CompanyId::fromInt($companyId));
        }

        $user = Auth::guard('stockflow')->user();

        $backup = $companyId ? SaBackupConfigModel::where('sa_company_id', $companyId)->first() : null;
        $domains = $companyId ? SaCustomDomainModel::where('sa_company_id', $companyId)->get()->toArray() : [];
        $emailSettings = $companyId ? SaEmailSettingModel::where('sa_company_id', $companyId)->first() : null;
        $apiKeys = $companyId ? SaApiKeyModel::where('sa_company_id', $companyId)->get()->toArray() : [];

        return Inertia::render('StockFlow/Settings/Index', [
            'company' => $company?->toArray(),
            'profile' => $user?->only(['id', 'name', 'email']),
            'backup' => $backup ? $backup->toArray() : null,
            'domains' => $domains,
            'emailSettings' => $emailSettings ? $emailSettings->toArray() : null,
            'apiKeys' => $apiKeys,
        ]);
    }

    public function updateCompany(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        if (!$companyId) {
            return back()->withErrors(['company' => 'No company selected.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'brand_color' => 'nullable|string|max:7',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'features_enabled' => 'nullable|array',
            'features_enabled.*' => 'boolean',
        ]);

        if ($request->has('features_enabled')) {
            $repo = app(\App\Domain\StockFlow\Repositories\CompanyRepositoryInterface::class);
            $company = $repo->findById(\App\Domain\StockFlow\ValueObjects\CompanyId::fromInt($companyId));
            $existingSettings = $company?->getSettings() ?? [];
            $existingSettings['features_enabled'] = $request->input('features_enabled');
            $validated['settings'] = $existingSettings;
        }

        $this->dashboardService->updateCompany($companyId, $validated);

        return redirect()->sfRoute('stockflow.settings.index')
            ->with('success', 'Company settings updated.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('stockflow')->user();
        if (!$user) {
            return back()->withErrors(['profile' => 'Not authenticated.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('sa_users', 'email')->ignore($user->id)],
            'current_password' => 'required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        }

        $model = SaUserModel::find($user->id);
        if (!$model) {
            return back()->withErrors(['profile' => 'User not found.']);
        }

        $model->name = $validated['name'];
        $model->email = $validated['email'];
        if (!empty($validated['password'])) {
            $model->password = Hash::make($validated['password']);
        }
        $model->save();

        return redirect()->sfRoute('stockflow.settings.index')
            ->with('success', 'Profile updated.');
    }

    public function updateBackup(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        if (!$companyId) {
            return back()->withErrors(['backup' => 'No company selected.']);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'enabled' => 'boolean',
            'frequency' => 'nullable|string|in:daily,weekly,monthly',
            'include_files' => 'nullable|string',
        ]);

        SaBackupConfigModel::updateOrCreate(
            ['sa_company_id' => $companyId],
            $validated
        );

        return back()->with('success', 'Backup settings saved.');
    }

    public function addDomain(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        if (!$companyId) {
            return back()->withErrors(['domain' => 'No company selected.']);
        }

        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:sa_custom_domains,domain',
        ]);

        $validated['sa_company_id'] = $companyId;
        $validated['status'] = 'pending';

        SaCustomDomainModel::create($validated);

        return back()->with('success', 'Domain added. Update your DNS records to point to the server.');
    }

    public function deleteDomain(Request $request, int $domainId)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $domain = SaCustomDomainModel::where('id', $domainId)->where('sa_company_id', $companyId)->first();
        if (!$domain) {
            return back()->withErrors(['domain' => 'Domain not found.']);
        }
        $domain->delete();

        return back()->with('success', 'Domain removed.');
    }

    public function updateEmailSettings(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        if (!$companyId) {
            return back()->withErrors(['email_settings' => 'No company selected.']);
        }

        $validated = $request->validate([
            'provider' => 'nullable|string|max:50',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl,null',
            'from_address' => 'nullable|email',
            'from_name' => 'nullable|string|max:255',
        ]);

        SaEmailSettingModel::updateOrCreate(
            ['sa_company_id' => $companyId],
            $validated
        );

        return back()->with('success', 'Email settings saved.');
    }

    public function generateApiKey(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        if (!$companyId) {
            return back()->withErrors(['api_key' => 'No company selected.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
        ]);

        $validated['sa_company_id'] = $companyId;
        $validated['key'] = Str::random(64);

        SaApiKeyModel::create($validated);

        return back()->with('success', 'API key generated. Copy the key now — it won\'t be shown again.');
    }

    public function revokeApiKey(Request $request, int $keyId)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $key = SaApiKeyModel::where('id', $keyId)->where('sa_company_id', $companyId)->first();
        if (!$key) {
            return back()->withErrors(['api_key' => 'API key not found.']);
        }
        $key->update(['active' => false]);

        return back()->with('success', 'API key revoked.');
    }
}
