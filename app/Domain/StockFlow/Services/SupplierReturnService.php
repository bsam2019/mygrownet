<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\SupplierReturn;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\SupplierReturnRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\SupplierReturnId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderId;
use App\Domain\StockFlow\ValueObjects\UserId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class SupplierReturnService
{
    public function __construct(
        private SupplierReturnRepositoryInterface $returnRepo,
        private StockMovementRepositoryInterface $movementRepo,
        private ItemRepositoryInterface $itemRepo,
    ) {}

    public function createReturn(int $companyId, array $data, int $userId): array
    {
        try {
            return DB::transaction(function () use ($companyId, $data, $userId) {
                $companyCid = CompanyId::fromInt($companyId);
                $supplierReturn = SupplierReturn::create(
                    companyId: $companyCid, supplierId: SupplierId::fromInt((int) $data['sa_supplier_id']),
                    returnNumber: $data['return_number'] ?? ('SPR-' . time()),
                    returnDate: new DateTimeImmutable($data['return_date'] ?? 'today'),
                    reason: $data['reason'], totalRefund: (float) ($data['total_refund'] ?? 0),
                    createdBy: UserId::fromInt($userId),
                    purchaseOrderId: isset($data['sa_purchase_order_id']) ? PurchaseOrderId::fromInt((int) $data['sa_purchase_order_id']) : null,
                    notes: $data['notes'] ?? null,
                );
                $saved = $this->returnRepo->save($supplierReturn);
                $result = $saved->toArray();
                $result['items'] = [];

                foreach ($data['items'] ?? [] as $item) {
                    $itemId = ItemId::fromInt((int) $item['sa_item_id']);
                    $domainItem = $this->itemRepo->findById($itemId);
                    if ($domainItem) {
                        $domainItem->adjustStock(-abs((float) $item['quantity']));
                        $this->itemRepo->save($domainItem);
                    }

                    $result['items'][] = [
                        'sa_supplier_return_id' => $saved->id(),
                        'sa_item_id' => $item['sa_item_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'] ?? 0,
                        'subtotal' => $item['subtotal'] ?? 0,
                    ];
                }

                return $result;
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('create supplier return', $e->getMessage());
        }
    }

    public function getReturns(int $companyId): array
    {
        return array_map(fn($r) => $r->toArray(), $this->returnRepo->findByCompanyId(CompanyId::fromInt($companyId)));
    }
}
