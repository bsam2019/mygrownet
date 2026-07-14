<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
    ) {}

    public function index(Request $request)
    {
        $isAuth = Auth::guard('stockflow')->check();

        \Log::debug('StockFlow LandingController::index', [
            'is_authenticated' => $isAuth,
            'host' => $request->getHost(),
            'path' => $request->path(),
            'session_company_id' => $request->session()->get('stock_audit_company_id'),
            'route_name' => $request->route()?->getName(),
        ]);

        if ($isAuth) {
            return app(DashboardController::class)->index($request);
        }

        $companyId = $request->attributes->get('stock_audit_company_id');
        $company = $companyId
            ? $this->companyRepository->findById(CompanyId::fromInt($companyId))
            : null;

        return Inertia::render('StockAudit/Landing', [
            'company' => $company ? $company->toArray() : null,
        ]);
    }
}
