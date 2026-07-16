<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DashboardService;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        return Inertia::render('StockFlow/Settings/Index', [
            'company' => $company?->toArray(),
            'profile' => $user?->only(['id', 'name', 'email']),
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

        // Merge features_enabled into existing company settings
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
}
