<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CmsCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = CompanyModel::query()->with('users');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('business_registration_number', 'like', "%{$search}%");
            });
        }

        // Filter by subscription type
        if ($request->filled('subscription_type')) {
            $query->where('subscription_type', $request->subscription_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter expiring soon
        if ($request->boolean('expiring_soon')) {
            $query->where('subscription_type', 'complimentary')
                ->whereNotNull('complimentary_until')
                ->whereBetween('complimentary_until', [now(), now()->addDays(7)]);
        }

        $companies = $query->latest()->paginate(20)->withQueryString();

        // Add computed properties
        $companies->getCollection()->transform(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'industry_type' => $company->industry_type,
                'status' => $company->status,
                'subscription_type' => $company->subscription_type,
                'sponsor_reference' => $company->sponsor_reference,
                'subscription_notes' => $company->subscription_notes,
                'complimentary_until' => $company->complimentary_until?->format('Y-m-d H:i:s'),
                'users_count' => $company->users->count(),
                'created_at' => $company->created_at->format('Y-m-d H:i:s'),
                'has_valid_access' => $company->hasValidAccess(),
                'is_expiring_soon' => $company->isComplimentaryExpiringSoon(),
                'days_until_expires' => $company->daysUntilComplimentaryExpires(),
            ];
        });

        return Inertia::render('Admin/CMS/Companies/Index', [
            'companies' => $companies,
            'filters' => $request->only(['search', 'subscription_type', 'status', 'expiring_soon']),
            'stats' => [
                'total' => CompanyModel::count(),
                'active' => CompanyModel::where('status', 'active')->count(),
                'paid' => CompanyModel::where('subscription_type', 'paid')->count(),
                'complimentary' => CompanyModel::where('subscription_type', 'complimentary')->count(),
                'sponsored' => CompanyModel::where('subscription_type', 'sponsored')->count(),
                'partner' => CompanyModel::where('subscription_type', 'partner')->count(),
                'expiring_soon' => CompanyModel::where('subscription_type', 'complimentary')
                    ->whereNotNull('complimentary_until')
                    ->whereBetween('complimentary_until', [now(), now()->addDays(7)])
                    ->count(),
            ],
        ]);
    }

    public function edit(CompanyModel $company)
    {
        return Inertia::render('Admin/CMS/Companies/Edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'status' => $company->status,
                'subscription_type' => $company->subscription_type,
                'sponsor_reference' => $company->sponsor_reference,
                'subscription_notes' => $company->subscription_notes,
                'complimentary_until' => $company->complimentary_until?->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    public function update(Request $request, CompanyModel $company)
    {
        $validated = $request->validate([
            'subscription_type' => 'required|in:paid,sponsored,complimentary,partner',
            'sponsor_reference' => 'nullable|string|max:255',
            'subscription_notes' => 'nullable|string',
            'complimentary_until' => 'nullable|date|after:now',
            'status' => 'required|in:active,suspended',
        ]);

        // If changing to complimentary, require expiration date
        if ($validated['subscription_type'] === 'complimentary' && empty($validated['complimentary_until'])) {
            return back()->withErrors([
                'complimentary_until' => 'Expiration date is required for complimentary access.',
            ]);
        }

        $company->update($validated);

        return redirect()->route('admin.cms-companies.index')
            ->with('success', 'Company subscription updated successfully.');
    }
}
