<?php

namespace App\Http\Controllers\GrowFinance;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->withCount('invoices')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('GrowFinance/Customers/Index', [
            'customers' => $customers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('GrowFinance/Customers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $businessId = $request->user()->id;

        GrowFinanceCustomerModel::create([
            'business_id' => $businessId,
            ...$validated,
        ]);

        return redirect()->route('growfinance.customers.index')
            ->with('success', 'Customer added successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $customer = GrowFinanceCustomerModel::forBusiness($businessId)
            ->with(['invoices' => fn($q) => $q->latest('invoice_date')->limit(10)])
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $customer = GrowFinanceCustomerModel::forBusiness($businessId)
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $businessId = $request->user()->id;

        $customer = GrowFinanceCustomerModel::forBusiness($businessId)
            ->findOrFail($id);

        $customer->update($validated);

        return redirect()->route('growfinance.customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $customer = GrowFinanceCustomerModel::forBusiness($businessId)
            ->findOrFail($id);

        $customer->delete();

        return back()->with('success', 'Customer deleted successfully!');
    }
}
