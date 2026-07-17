<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\TransferService;
use App\Domain\StockFlow\Services\InventoryService;
use App\Domain\StockFlow\Services\WarehouseService;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaTransferModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService,
        private InventoryService $inventoryService,
        private WarehouseService $warehouseService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaTransferModel::where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $transfers = $query->with('fromWarehouse', 'toWarehouse', 'items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockFlow/Transfers/Index', [
            'transfers' => $transfers,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        return Inertia::render('StockFlow/Transfers/Create', [
            'warehouses' => $this->warehouseService->getWarehouses($companyId),
            'items' => $this->inventoryService->getItemsForCompany($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:sa_warehouses,id',
            'to_warehouse_id' => 'required|exists:sa_warehouses,id|different:from_warehouse_id',
            'items' => 'required|array|min:1',
            'items.*.sa_item_id' => 'required|exists:sa_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->transferService->createTransfer($companyId, $validated, $request->user()->id);

        return redirect()->sfRoute('stockflow.transfers.index')->with('success', 'Transfer created successfully.');
    }

    public function show(int $transferId)
    {
        $transfer = $this->transferService->getTransferById($transferId, session('stockflow_company_id'));

        if (!$transfer) {
            abort(404);
        }

        return Inertia::render('StockFlow/Transfers/Show', [
            'transfer' => $transfer->toArray(),
        ]);
    }

    public function receive(Request $request, int $transferId)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $this->transferService->receiveTransfer($transferId, $companyId, $request->user()->id);

        return redirect()->sfRoute('stockflow.transfers.index')->with('success', 'Transfer received successfully.');
    }

    public function cancel(Request $request, int $transferId)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $this->transferService->cancelTransfer($transferId, $companyId);

        return redirect()->sfRoute('stockflow.transfers.index')->with('success', 'Transfer cancelled successfully.');
    }
}
