<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\VendorModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $vendors = VendorModel::forCompany($companyId)
            ->with('createdBy.user')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('vendor_number', 'like', "%{$s}%"))
            ->when($request->vendor_type, fn($q) => $q->where('vendor_type', $request->vendor_type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(20);

        $vendorTypes = VendorModel::forCompany($companyId)->select('vendor_type')->distinct()->pluck('vendor_type');

        return Inertia::render('CMS/Vendors/Index', [
            'vendors' => $vendors,
            'filters' => $request->only(['search', 'vendor_type', 'status']),
            'vendorTypes' => $vendorTypes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CMS/Vendors/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->cmsUser->company_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'vendor_type' => 'nullable|string|max:100',
            'payment_terms_days' => 'nullable|integer|min:0',
            'payment_method' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'mobile_money_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $lastVendor = VendorModel::forCompany($companyId)->orderBy('id', 'desc')->first();
        $sequence = $lastVendor ? ((int) substr($lastVendor->vendor_number, -4)) + 1 : 1;
        $vendorNumber = 'VEN-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        VendorModel::create([
            ...$validated,
            'company_id' => $companyId,
            'vendor_number' => $vendorNumber,
            'status' => 'active',
            'created_by' => $request->user()->cmsUser->id,
        ]);

        return redirect()->route('cms.vendors.index')->with('success', 'Vendor created.');
    }

    public function show(VendorModel $vendor): Response
    {
        $vendor->load(['createdBy.user', 'purchaseOrders' => function ($q) {
            $q->latest()->limit(10);
        }]);

        return Inertia::render('CMS/Vendors/Show', ['vendor' => $vendor]);
    }

    public function edit(VendorModel $vendor): Response
    {
        return Inertia::render('CMS/Vendors/Edit', ['vendor' => $vendor]);
    }

    public function update(Request $request, VendorModel $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'vendor_type' => 'nullable|string|max:100',
            'payment_terms_days' => 'nullable|integer|min:0',
            'payment_method' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'mobile_money_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,blacklisted',
        ]);

        $vendor->update($validated);

        return redirect()->route('cms.vendors.show', $vendor->id)->with('success', 'Vendor updated.');
    }
}
