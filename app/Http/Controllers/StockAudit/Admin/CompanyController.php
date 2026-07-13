<?php

namespace App\Http\Controllers\StockAudit\Admin;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Entities\Company;
use App\Domain\StockFlow\Entities\User;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Domain\StockFlow\Repositories\UserRepositoryInterface;
use App\Domain\StockFlow\Services\CompanyUserService;
use App\Domain\StockFlow\Services\CompanyRoleService;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
        private UserRepositoryInterface $userRepository,
        private CompanyUserService $companyUserService,
        private CompanyRoleService $companyRoleService,
    ) {}

    public function index()
    {
        $companies = array_map(fn($c) => $c->toArray(), $this->companyRepository->findAll());

        return Inertia::render('StockAudit/Admin/Companies/Index', [
            'companies' => $companies,
        ]);
    }

    public function create()
    {
        return Inertia::render('StockAudit/Admin/Companies/Create');
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
            'tagline' => 'nullable|string|max:255',
            'brand_color' => 'nullable|string|max:7',
        ]);

        // Generate subdomain
        $subdomain = $this->generateSubdomain($validated['name']);
        $counter = 1;
        while ($this->companyRepository->findBySubdomain($subdomain)) {
            $subdomain = $this->generateSubdomain($validated['name']) . '-' . $counter++;
        }

        $company = Company::create(
            name: $validated['name'],
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
            address: $validated['address'] ?? null,
            city: $validated['city'] ?? null,
            country: $validated['country'] ?? null,
            contactPerson: $validated['contact_person'] ?? null,
            currency: $validated['currency'] ?? 'MWK',
            subdomain: $subdomain,
            tagline: $validated['tagline'] ?? null,
            brandColor: $validated['brand_color'] ?? '#059669',
        );

        $saved = $this->companyRepository->save($company);

        // Seed default roles for the new company
        $this->companyRoleService->seedDefaultRoles($saved->id());

        // Create initial admin user for the company with auto-generated password
        $plainPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%'), 0, 14);
        $adminUserName = $validated['contact_person'] ?: $validated['name'] . ' Admin';

        $user = User::create(
            name: $adminUserName,
            email: $validated['email'] ?: "admin@{$subdomain}.mail",
            password: Hash::make($plainPassword),
        );

        $savedUser = $this->userRepository->save($user);

        // Assign owner role to the new user
        $roles = $this->companyRoleService->getRolesForCompany($saved->id());
        $ownerRole = collect($roles)->firstWhere('slug', 'owner');

        $this->companyUserService->inviteUser(
            $saved->id(),
            $savedUser->id(),
            $ownerRole ? $ownerRole['id'] : null,
        );

        // Auto-accept the invitation
        $this->companyUserService->acceptInvitation($saved->id(), $savedUser->id());

        return redirect()->route('stockflow.admin.companies.show', $saved->id())
            ->with('success', "Company created successfully! Initial credentials — Email: {$savedUser->getEmail()}, Password: {$plainPassword}")
            ->with('generated_password', $plainPassword)
            ->with('generated_email', $savedUser->getEmail());
    }

    public function show(int $companyId)
    {
        $company = $this->companyRepository->findById(CompanyId::fromInt($companyId));

        if (!$company) {
            abort(404);
        }

        $employees = $this->companyUserService->getEmployees($companyId);

        return Inertia::render('StockAudit/Admin/Companies/Show', [
            'company' => $company->toArray(),
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, int $companyId)
    {
        $company = $this->companyRepository->findById(CompanyId::fromInt($companyId));

        if (!$company) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'tagline' => 'nullable|string|max:255',
            'brand_color' => 'nullable|string|max:7',
            'status' => 'sometimes|in:active,suspended,cancelled',
        ]);

        // Rebuild company with updated values
        $updated = Company::reconstitute(
            id: $company->getId(),
            name: $validated['name'] ?? $company->getName(),
            subdomain: $company->getSubdomain(),
            email: $validated['email'] ?? $company->getEmail(),
            phone: $validated['phone'] ?? $company->getPhone(),
            address: $validated['address'] ?? $company->getAddress(),
            city: $validated['city'] ?? $company->getCity(),
            country: $validated['country'] ?? $company->getCountry(),
            contactPerson: $validated['contact_person'] ?? $company->getContactPerson(),
            currency: $validated['currency'] ?? $company->getCurrency(),
            status: $validated['status'] ?? $company->getStatus(),
            createdAt: $company->getCreatedAt(),
            updatedAt: $company->getUpdatedAt(),
            logoPath: $company->getLogoPath(),
            tagline: $validated['tagline'] ?? $company->getTagline(),
            brandColor: $validated['brand_color'] ?? $company->getBrandColor(),
        );

        $this->companyRepository->save($updated);

        return redirect()->route('stockflow.admin.companies.show', $companyId)
            ->with('success', 'Company updated successfully');
    }

    private function generateSubdomain(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    }
}
