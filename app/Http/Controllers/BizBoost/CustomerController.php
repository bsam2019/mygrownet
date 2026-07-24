<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\CustomerService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\CustomerTagRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService,
        private BusinessService $businessService,
        private CustomerTagRepositoryInterface $tagRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $filters = $request->only(['search', 'tag', 'status', 'sort']);
        $customers = $this->customerService->getCustomers($business->id, $filters);

        return Inertia::render('BizBoost/Customers/Index', [
            'customers' => $customers,
            'tags' => $this->tagRepo->findByBusiness($business->id),
            'filters' => $filters,
            'stats' => $this->customerService->getCustomerStats($business->id),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Customers/Create', [
            'tags' => $this->tagRepo->findByBusiness($business->id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'birth_date' => 'nullable|date',
            'anniversary_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:biz_boost_customer_tags,id',
            'source' => 'nullable|string|max:100',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $customer = $this->customerService->createCustomer($business->id, $validated);

        return redirect()->route('bizboost.customers.index')
            ->with('success', 'Customer added successfully.');
    }

    public function show(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $customerData = $this->customerService->getCustomerDetail($business->id, $id);

        if (!$customerData) {
            abort(404);
        }

        return Inertia::render('BizBoost/Customers/Show', $customerData);
    }

    public function edit(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $customer = $this->customerService->getCustomerById($business->id, $id);

        if (!$customer) {
            abort(404);
        }

        return Inertia::render('BizBoost/Customers/Edit', [
            'customer' => $customer->toArray(),
            'tags' => $this->tagRepo->findByBusiness($business->id),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'birth_date' => 'nullable|date',
            'anniversary_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:biz_boost_customer_tags,id',
            'source' => 'nullable|string|max:100',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->customerService->updateCustomer($business->id, $id, $validated);

        return redirect()->route('bizboost.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->customerService->deleteCustomer($business->id, $id);

        return redirect()->route('bizboost.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function import(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Customers/Import', [
            'tags' => $this->tagRepo->findByBusiness($business->id),
        ]);
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,txt|max:5120',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:biz_boost_customer_tags,id',
            'update_existing' => 'boolean',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $result = $this->customerService->importCustomers($business->id, $request->file('file'), $request->input('tags', []), $request->boolean('update_existing'));

        $message = "Imported {$result['imported']} customers.";
        if (($result['updated'] ?? 0) > 0) {
            $message .= " {$result['updated']} existing customers updated.";
        }
        if (($result['skipped'] ?? 0) > 0) {
            $message .= " {$result['skipped']} rows skipped.";
        }

        return redirect()->route('bizboost.customers.index')
            ->with('success', $message);
    }
}