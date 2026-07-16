<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\CashRegisterService;
use App\Domain\StockFlow\Services\SalesService;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCashRegisterModel;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $companyId = $request->session()->get('stockflow_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaCashRegisterModel::where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $registers = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockFlow/Cash/Index', [
            'registers' => $registers,
        ]);
    }

    public function open(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

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
            return redirect()->sfRoute('stockflow.cash.show', $register->id())->with('success', 'Register opened successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    public function show(int $registerId)
    {
        $register = $this->cashRegisterService->getRegisterById($registerId, session('stockflow_company_id'));

        if (!$register) {
            abort(404);
        }

        return Inertia::render('StockFlow/Cash/Show', [
            'register' => $register->toArray(),
        ]);
    }

    public function addMovement(Request $request, int $registerId)
    {
        $companyId = $request->session()->get('stockflow_company_id');

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

        return redirect()->sfRoute('stockflow.cash.show', $registerId)->with('success', 'Movement added successfully.');
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

        return redirect()->sfRoute('stockflow.cash.show', $registerId)->with('success', 'Register closed successfully.');
    }

    public function verify(Request $request, int $registerId)
    {
        try {
            $this->cashRegisterService->verifyRegister($registerId);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }

        return redirect()->sfRoute('stockflow.cash.show', $registerId)->with('success', 'Register verified successfully.');
    }

    public function summary(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

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

        return Inertia::render('StockFlow/Cash/Summary', [
            'registers' => array_map(fn($r) => $r->toArray(), $registers),
            'totals' => $totals,
            'from' => $validated['from'],
            'to' => $validated['to'],
        ]);
    }

    public function summaryPdf(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $companyModel = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::find($companyId);
        $companyName = $companyModel?->name ?? 'Company';

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

        $totals = ['total_sales' => 0, 'total_expenses' => 0, 'total_banking' => 0, 'total_variance' => 0];
        foreach ($registers as $r) {
            $totals['total_sales'] += $r->getTotalSales()->toFloat();
            $totals['total_expenses'] += $r->getTotalExpenses()->toFloat();
            $totals['total_banking'] += $r->getTotalBanking()->toFloat();
            $totals['total_variance'] += $r->getVariance()?->toFloat() ?? 0;
        }

        $pdf = Pdf::loadView('pdf.stockflow.cash-summary', [
            'companyName' => $companyName,
            'registers' => array_map(fn($r) => $r->toArray(), $registers),
            'totals' => $totals,
            'from' => $validated['from'],
            'to' => $validated['to'],
        ]);

        return $pdf->download("cash-summary-{$validated['from']}-to-{$validated['to']}.pdf");
    }
}
