<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\CustomerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $customerService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $search = $request->get('search');

        $customers = $this->customerService->getCustomers($companyId);

        if ($search) {
            $customers = array_filter($customers, fn($c) =>
                stripos($c->getName(), $search) !== false ||
                stripos($c->getPhone() ?? '', $search) !== false ||
                stripos($c->getEmail() ?? '', $search) !== false
            );
        }

        return Inertia::render('StockFlow/Customers/Index', [
            'customers' => array_map(fn($c) => $c->toArray(), $customers),
        ]);
    }

    public function create()
    {
        return Inertia::render('StockFlow/Customers/Create');
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $this->customerService->createCustomer($companyId, $validated);

        return redirect()->sfRoute('stockflow.customers.index')->with('success', 'Customer created.');
    }

    public function show(int $customerId, Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $customer = $this->customerService->getCustomerById($customerId, $companyId);

        if (!$customer) {
            abort(404);
        }

        return Inertia::render('StockFlow/Customers/Show', [
            'customer' => $customer->toArray(),
        ]);
    }

    public function update(Request $request, int $customerId)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $this->customerService->updateCustomer($customerId, $companyId, $validated);

        return redirect()->sfRoute('stockflow.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(int $customerId, Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $this->customerService->deleteCustomer($customerId, $companyId);

        return redirect()->sfRoute('stockflow.customers.index')->with('success', 'Customer deleted.');
    }
}
