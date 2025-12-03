<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    public function __construct(
        private readonly AccountingService $accountingService
    ) {}

    public function index(): Response
    {
        return Inertia::render('GrowFinance/Setup/Index');
    }

    public function initialize(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $this->accountingService->initializeChartOfAccounts($businessId);

        return redirect()->route('growfinance.dashboard')
            ->with('success', 'GrowFinance has been set up successfully!');
    }
}
