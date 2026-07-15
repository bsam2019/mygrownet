<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\ReceiptService;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaReceiptModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptService $receiptService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $perPage = $request->get('per_page', 15);

        $receipts = SaReceiptModel::where('sa_company_id', $companyId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockAudit/Receipts/Index', [
            'receipts' => $receipts,
        ]);
    }

    public function show(int $receiptId)
    {
        $receipt = $this->receiptService->getReceiptById($receiptId, session('stock_audit_company_id'));

        if (!$receipt) {
            abort(404);
        }

        return Inertia::render('StockAudit/Receipts/Show', [
            'receipt' => $receipt->toArray(),
        ]);
    }
}
