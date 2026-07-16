<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\SaleReturn;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\SaleReturnRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\SaleReturnId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\SaleId;
use App\Domain\StockFlow\ValueObjects\UserId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\MovementType;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaleReturnService
{
    public function __construct(
        private SaleReturnRepositoryInterface $returnRepo,
        private StockMovementRepositoryInterface $movementRepo,
        private ItemRepositoryInterface $itemRepo,
    ) {}

    public function createReturn(int $companyId, array $data, int $userId): array
    {
        try {
            return DB::transaction(function () use ($companyId, $data, $userId) {
                $companyCid = CompanyId::fromInt($companyId);
                $saleReturn = SaleReturn::create(
                    companyId: $companyCid, saleId: SaleId::fromInt((int) $data['sa_sale_id']),
                    returnNumber: $data['return_number'] ?? ('SR-' . time()),
                    returnDate: new DateTimeImmutable($data['return_date'] ?? 'today'),
                    reason: $data['reason'], totalRefund: (float) ($data['total_refund'] ?? 0),
                    createdBy: UserId::fromInt($userId),
                    refundMethod: $data['refund_method'] ?? null, notes: $data['notes'] ?? null,
                );
                $saved = $this->returnRepo->save($saleReturn);
                $result = $saved->toArray();
                $result['items'] = [];

                // Process return items and create stock movements
                foreach ($data['items'] ?? [] as $item) {
                    $itemId = ItemId::fromInt((int) $item['sa_item_id']);
                    $domainItem = $this->itemRepo->findById($itemId);
                    if ($domainItem) {
                        $domainItem->adjustStock((float) $item['quantity']);
                        $this->itemRepo->save($domainItem);
                    }

                    $result['items'][] = [
                        'sa_sale_return_id' => $saved->id(),
                        'sa_item_id' => $item['sa_item_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'] ?? 0,
                        'subtotal' => $item['subtotal'] ?? 0,
                        'condition' => $item['condition'] ?? null,
                    ];
                }

                return $result;
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('create sale return', $e->getMessage());
        }
    }

    public function getReturns(int $companyId): array
    {
        return array_map(fn($r) => $r->toArray(), $this->returnRepo->findByCompanyId(CompanyId::fromInt($companyId)));
    }
}
