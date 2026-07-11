<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\AuditService;
use App\Domain\StockFlow\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private DashboardService $dashboardService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $audits = $this->auditService->getAuditsForCompany($companyId);

        if ($request->wantsJson()) {
            return response()->json($audits);
        }

        return Inertia::render('StockAudit/Audits/Index', [
            'audits' => $audits,
        ]);
    }

    public function show(int $auditId)
    {
        $audit = $this->auditService->getAuditById($auditId, session('stock_audit_company_id'));

        if (!$audit) {
            abort(404);
        }

        return Inertia::render('StockAudit/Audits/Show', [
            'audit' => $audit->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sa_company_id' => 'required|exists:sa_companies,id',
            'title' => 'required|string|max:255',
            'audit_date' => 'required|date',
            'prepared_for' => 'nullable|string|max:255',
            'prepared_by' => 'nullable|string|max:255',
        ]);

        $audit = \App\Domain\StockFlow\Entities\Audit::create(
            companyId: \App\Domain\StockFlow\ValueObjects\CompanyId::fromInt($validated['sa_company_id']),
            title: $validated['title'],
            auditDate: new \DateTimeImmutable($validated['audit_date']),
            totalSystemValue: \App\Domain\StockFlow\ValueObjects\Money::zero(),
            totalPhysicalValue: \App\Domain\StockFlow\ValueObjects\Money::zero(),
            preparedFor: $validated['prepared_for'] ?? null,
            preparedBy: $validated['prepared_by'] ?? null,
        );

        $repo = app(\App\Domain\StockFlow\Repositories\AuditRepositoryInterface::class);
        $saved = $repo->save($audit);

        return redirect()->route('stock-audit.audits.show', $saved->id());
    }

    public function finalize(Request $request, int $auditId)
    {
        $validated = $request->validate([
            'executive_summary' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'total_recorded_sales' => 'nullable|numeric|min:0',
        ]);

        $this->auditService->finalizeAudit($auditId, $validated);

        return redirect()->route('stock-audit.audits.show', $auditId);
    }

    public function exportCsv(int $auditId)
    {
        $audit = $this->auditService->getAuditById($auditId);

        if (!$audit) {
            abort(404);
        }

        $items = $this->auditService->getAuditItemData($auditId);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-' . $auditId . '.csv"',
        ];

        $callback = function () use ($audit, $items) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Item', 'Bin', 'Unit Price', 'System Qty', 'Physical Qty', 'Gap Qty', 'System Value', 'Physical Value', 'Gap Value', 'Notes']);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item['item_name'] ?? '',
                    '',
                    number_format($item['unit_price'] ?? 0, 2),
                    $item['system_qty'] ?? 0,
                    $item['physical_qty'] ?? 0,
                    $item['gap_qty'] ?? 0,
                    number_format($item['system_value'] ?? 0, 2),
                    number_format($item['physical_value'] ?? 0, 2),
                    number_format($item['gap_value'] ?? 0, 2),
                    '',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['SUMMARY']);
            fputcsv($handle, ['Total System Value', number_format($audit->getTotalSystemValue()->toFloat(), 2)]);
            fputcsv($handle, ['Total Physical Value', number_format($audit->getTotalPhysicalValue()->toFloat(), 2)]);
            fputcsv($handle, ['Total Variance', number_format($audit->getTotalVariance()->toFloat(), 2)]);
            fputcsv($handle, ['Recorded Sales', number_format($audit->getTotalRecordedSales()->toFloat(), 2)]);
            fputcsv($handle, ['Unaccounted Value', number_format($audit->getUnaccountedValue()->toFloat(), 2)]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
