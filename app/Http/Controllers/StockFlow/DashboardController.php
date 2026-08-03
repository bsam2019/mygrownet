<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DashboardService;
use App\Domain\StockFlow\Services\CompanyUserService;
use App\Domain\Core\Services\OrganizationEntryResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private CompanyUserService $companyUserService,
        private OrganizationEntryResolver $orgResolver,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $isAdmin = $user && $user->hasRole(['admin', 'Administrator', 'superadmin', 'super-admin', 'stockflow-admin']);

        // Resolve the set of companies this user may see.
        $visibleCompanyIds = $isAdmin
            ? array_column($this->dashboardService->getActiveCompanies(), 'id')
            : ($user ? $this->companyUserService->activeCompanyIdsForUser((int) $user->getAuthIdentifier()) : []);

        $companies = $isAdmin
            ? $this->dashboardService->getActiveCompanies()
            : array_values(array_filter(
                $this->dashboardService->getActiveCompanies(),
                fn($c) => in_array($c['id'], $visibleCompanyIds)
            ));

        // Resolve current company context — never fall back to an arbitrary company.
        $companyId = $request->session()->get('stockflow_company_id');

        if ($companyId && !in_array($companyId, $visibleCompanyIds)) {
            // Session points at a company the user cannot access; clear it.
            $request->session()->forget('stockflow_company_id');
            $companyId = null;
        }

        if (!$companyId) {
            // Default to the user's first membership when available.
            $companyId = $visibleCompanyIds[0] ?? null;
            if ($companyId) {
                $request->session()->put('stockflow_company_id', $companyId);
            }
        }

        $data = $companyId ? $this->dashboardService->getDashboardData($companyId) : $this->dashboardService->getDashboardData(0);

        return Inertia::render('StockFlow/Dashboard', [
            'company' => $data['company'] ?? null,
            'companies' => $companies,
            'stats' => $data['stats'] ?? [],
            'open_register' => $data['open_register'] ?? null,
            'low_stock_items' => $data['low_stock_items'] ?? [],
            'out_of_stock_items' => $data['out_of_stock_items'] ?? [],
            'pending_pos' => $data['pending_pos'] ?? [],
            'partial_pos' => $data['partial_pos'] ?? [],
            'in_progress_counts' => $data['in_progress_counts'] ?? [],
            'unresolved_audits' => $data['unresolved_audits'] ?? [],
            'recent_audits' => $data['recent_audits'] ?? [],
            'recent_counts' => $data['recent_counts'] ?? [],
            'companyDetails' => $this->orgResolver->companyDetails($user),
        ]);
    }

    public function switchCompany(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:sa_companies,id',
        ]);

        $user = Auth::guard('web')->user();
        $isAdmin = $user && $user->hasRole(['admin', 'Administrator', 'superadmin', 'super-admin', 'stockflow-admin']);

        $allowed = $isAdmin
            || ($user && $this->companyUserService->isActiveMember((int) $validated['company_id'], (int) $user->getAuthIdentifier()));

        if (!$allowed) {
            abort(403, 'You do not have access to this company.');
        }

        $request->session()->put('stockflow_company_id', $validated['company_id']);

        return redirect()->sfRoute('stockflow.dashboard');
    }
}
