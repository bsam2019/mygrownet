<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\CashRegisterService;
use App\Domain\StockFlow\Services\SalesService;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashController extends Controller
{
    public function __construct(
        private CashRegisterService $cashRegisterService,
        private SalesService $salesService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $registers = $this->cashRegisterService->getRegistersForCompany($companyId);

        return Inertia::render('StockAudit/Cash/Index', [
            'registers' => $registers,
        ]);
    }

    public function open(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'register_date' => 'required|date',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        try {
            $register = $this->cashRegisterService->openRegister(
                $companyId,
                $validated['register_date'],
                $validated['opening_balance'],
                $request->user()->id,
            );
            return redirect()->route('stock-audit.cash.show', $register->id());
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    public function show(int $registerId)
    {
        $register = $this->cashRegisterService->getRegisterById($registerId, session('stock_audit_company_id'));

        if (!$register) {
            abort(404);
        }

        return Inertia::render('StockAudit/Cash/Show', [
            'register' => $register->toArray(),
        ]);
    }

    public function addMovement(Request $request, int $registerId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'type' => 'required|string|in:expense,banking,float_top_up,float_withdrawal,petty_cash',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->cashRegisterService->addMovement($companyId, $registerId, $validated, $request->user()->id);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->route('stock-audit.cash.show', $registerId);
    }

    public function close(Request $request, int $registerId)
    {
        $validated = $request->validate([
            'actual_closing' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->cashRegisterService->closeRegister($registerId, $validated['actual_closing'], $validated['notes'] ?? null, $request->user()->id);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->route('stock-audit.cash.show', $registerId);
    }

    public function verify(Request $request, int $registerId)
    {
        try {
            $this->cashRegisterService->verifyRegister($registerId);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->route('stock-audit.cash.show', $registerId);
    }

    public function summary(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $repo = app(\App\Domain\StockFlow\Repositories\CashRegisterRepositoryInterface::class);
        $registers = $repo->findByDateBetween(
            CompanyId::fromInt($companyId),
            new \DateTimeImmutable($validated['from']),
            new \DateTimeImmutable($validated['to']),
        );

        $totals = [
            'total_sales' => 0,
            'total_expenses' => 0,
            'total_banking' => 0,
            'total_variance' => 0,
        ];

        foreach ($registers as $r) {
            $totals['total_sales'] += $r->getTotalSales()->toFloat();
            $totals['total_expenses'] += $r->getTotalExpenses()->toFloat();
            $totals['total_banking'] += $r->getTotalBanking()->toFloat();
            $totals['total_variance'] += $r->getVariance()?->toFloat() ?? 0;
        }

        return Inertia::render('StockAudit/Cash/Summary', [
            'registers' => array_map(fn($r) => $r->toArray(), $registers),
            'totals' => $totals,
            'from' => $validated['from'],
            'to' => $validated['to'],
        ]);
    }
}
