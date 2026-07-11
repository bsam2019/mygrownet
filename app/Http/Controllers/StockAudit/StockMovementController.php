<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockMovementController extends Controller
{
    public function __construct(
        private StockMovementRepositoryInterface $movementRepository,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $movements = $this->movementRepository->findByCompanyId(CompanyId::fromInt($companyId));

        return Inertia::render('StockAudit/Movements/Index', [
            'movements' => $movements,
        ]);
    }

    public function byItem(Request $request, int $itemId)
    {
        $movements = $this->movementRepository->findByItemId(ItemId::fromInt($itemId));

        return response()->json($movements);
    }
}
