<?php

declare(strict_types=1);

namespace App\Extensions\Manufacturing\Services;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Manufacturing\Entities\BillOfMaterials;
use App\Extensions\Manufacturing\Entities\BomMaterial;
use App\Extensions\Manufacturing\Entities\WorkOrder;
use App\Extensions\Manufacturing\Repositories\BillOfMaterialsRepositoryInterface;
use App\Extensions\Manufacturing\Repositories\WorkOrderRepositoryInterface;
use Throwable;

class ManufacturingService
{
    public function __construct(
        private BillOfMaterialsRepositoryInterface $bomRepo,
        private WorkOrderRepositoryInterface $workOrderRepo,
    ) {}

    public function createBom(int $companyId, int $itemId, string $name, float $quantity, array $materials = []): array
    {
        $materials = array_map(fn($m) => new BomMaterial(
            itemId: (int) $m['sa_item_id'], quantity: (float) $m['quantity'],
            uom: $m['uom'] ?? 'each', wasteFactor: (float) ($m['waste_factor'] ?? 0),
            sortOrder: (int) ($m['sort_order'] ?? 0),
        ), $materials);

        $bom = BillOfMaterials::create(CompanyId::fromInt($companyId), ItemId::fromInt($itemId), $name, $quantity, $materials);
        return $this->bomRepo->save($bom)->toArray();
    }

    public function getBoms(int $companyId): array
    {
        return array_map(fn($b) => $b->toArray(), $this->bomRepo->findByCompany(CompanyId::fromInt($companyId)));
    }

    public function createWorkOrder(int $companyId, int $itemId, string $orderNumber, float $quantity, ?int $bomId = null, ?string $dueDate = null, ?string $notes = null): array
    {
        $wo = WorkOrder::create(CompanyId::fromInt($companyId), ItemId::fromInt($itemId), $orderNumber, $quantity, $bomId, $dueDate, $notes);
        return $this->workOrderRepo->save($wo)->toArray();
    }

    public function getWorkOrders(int $companyId): array
    {
        return array_map(fn($wo) => $wo->toArray(), $this->workOrderRepo->findByCompany(CompanyId::fromInt($companyId)));
    }
}
