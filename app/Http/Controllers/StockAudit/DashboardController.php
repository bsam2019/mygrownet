<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $companies = $this->dashboardService->getActiveCompanies();

        if (!$companyId && !empty($companies)) {
            $companyId = $companies[0]['id'] ?? null;
            $request->session()->put('stock_audit_company_id', $companyId);
        }

        $data = $companyId ? $this->dashboardService->getDashboardData($companyId) : $this->dashboardService->getDashboardData(0);

        return Inertia::render('StockAudit/Dashboard', [
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
        ]);
    }

    public function switchCompany(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:sa_companies,id',
        ]);

        $request->session()->put('stock_audit_company_id', $validated['company_id']);

        return redirect()->route('stock-audit.dashboard');
    }
}