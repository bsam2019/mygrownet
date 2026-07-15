<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaStockMovementModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockMovementController extends Controller
{
    public function __construct(
        private StockMovementRepositoryInterface $movementRepository,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaStockMovementModel::with('item')
            ->where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('reference_type', 'like', "%{$search}%");
            });
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockFlow/Movements/Index', [
            'movements' => $movements,
        ]);
    }

    public function byItem(Request $request, int $itemId)
    {
        $movements = $this->movementRepository->findByItemId(ItemId::fromInt($itemId));

        return response()->json($movements);
    }
}
