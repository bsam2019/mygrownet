<?php

namespace App\Http\Controllers\StockFlow;

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
        if (Auth::guard('stockflow')->check()) {
            return app(DashboardController::class)->index($request);
        }

        $companyId = $request->attributes->get('stockflow_company_id');
        $company = $companyId
            ? $this->companyRepository->findById(CompanyId::fromInt($companyId))
            : null;

        return Inertia::render('StockFlow/Landing', [
            'company' => $company ? $company->toArray() : null,
        ]);
    }
}
