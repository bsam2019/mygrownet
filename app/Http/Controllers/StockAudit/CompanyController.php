<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function index()
    {
        $companies = $this->dashboardService->getAllCompanies();

        return Inertia::render('StockAudit/Companies/Index', [
            'companies' => $companies,
        ]);
    }

    public function create()
    {
        return Inertia::render('StockAudit/Companies/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
        ]);

        // Generate subdomain from company name
        $subdomain = $this->generateSubdomain($validated['name']);
        // Ensure uniqueness
        $repo = app(\App\Domain\StockFlow\Repositories\CompanyRepositoryInterface::class);
        $counter = 1;
        $originalSubdomain = $subdomain;
        while ($repo->findBySubdomain($subdomain)) {
            $subdomain = $originalSubdomain . '-' . $counter++;
        }

        $company = \App\Domain\StockFlow\Entities\Company::create(
            name: $validated['name'],
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
            address: $validated['address'] ?? null,
            city: $validated['city'] ?? null,
            country: $validated['country'] ?? null,
            contactPerson: $validated['contact_person'] ?? null,
            currency: $validated['currency'] ?? 'MWK',
            subdomain: $subdomain,
        );

        $repo->save($company);

        return redirect()->sfRoute('stock-audit.companies.index')
            ->with('success', "Company created with subdomain: {$subdomain}.mygrownet.com");
    }

    private function generateSubdomain(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    }

    public function show(int $companyId)
    {
        $repo = app(\App\Domain\StockFlow\Repositories\CompanyRepositoryInterface::class);
        $company = $repo->findById(\App\Domain\StockFlow\ValueObjects\CompanyId::fromInt($companyId));

        if (!$company) {
            abort(404);
        }

        return Inertia::render('StockAudit/Companies/Show', [
            'company' => $company->toArray(),
        ]);
    }
}
