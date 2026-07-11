<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\PurchaseOrder;
use App\Domain\StockFlow\Entities\PurchaseOrderItem;
use App\Domain\StockFlow\Entities\StockMovement;
use App\Domain\StockFlow\Entities\Supplier;
use App\Domain\StockFlow\Events\PurchaseOrderReceived;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\PurchaseOrderRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\Repositories\SupplierRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\MovementType;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Throwable;

class PurchasingService
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $poRepository,
        private ItemRepositoryInterface $itemRepository,
        private StockMovementRepositoryInterface $movementRepository,
        private SupplierRepositoryInterface $supplierRepository,
        private StockLevelProjector $stockLevelProjector,
    ) {}

    public function createPurchaseOrder(int $companyId, array $data): PurchaseOrder
    {
        try {
            return DB::transaction(function () use ($companyId, $data) {
                $items = $data['items'];
                $subtotal = 0.0;

                foreach ($items as $line) {
                    $subtotal += $line['quantity_ordered'] * $line['unit_cost'];
                }

                $orderNumber = $this->poRepository->nextOrderNumber();

                $order = PurchaseOrder::create(
                    companyId: CompanyId::fromInt($companyId),
                    supplierId: isset($data['sa_supplier_id']) ? SupplierId::fromInt($data['sa_supplier_id']) : null,
                    orderNumber: $orderNumber,
                    orderDate: new DateTimeImmutable($data['order_date'] ?? 'now'),
                    subtotal: Money::fromFloat($subtotal),
                    notes: $data['notes'] ?? null,
                );

                $savedOrder = $this->poRepository->save($order);

                foreach ($items as $line) {
                    $poItem = PurchaseOrderItem::create(
                        purchaseOrderId: PurchaseOrderId::fromInt($savedOrder->id()),
                        itemId: ItemId::fromInt($line['sa_item_id']),
                        quantityOrdered: $line['quantity_ordered'],
                        unitCost: Money::fromFloat($line['unit_cost']),
                    );
                    $savedOrder->addItem($poItem);
                }

                return $savedOrder;
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('create purchase order', $e->getMessage());
        }
    }

    public function receiveOrder(int $purchaseOrderId, int $companyId, array $items, int $userId): void
    {
        DB::transaction(function () use ($purchaseOrderId, $companyId, $items, $userId) {
            $order = $this->poRepository->findById(PurchaseOrderId::fromInt($purchaseOrderId));
            if (!$order) {
                throw new OperationFailedException('receive order', 'Purchase order not found');
            }

            $eventItems = [];

            foreach ($items as $data) {
                $qtyReceived = $data['quantity_received'];
                if ($qtyReceived <= 0) continue;

                // Get item for bin info
                $item = $this->itemRepository->findById(ItemId::fromInt($data['sa_item_id']));
                if (!$item) continue;

                // Get current stock from projection
                $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);
                $qtyBefore = (float) ($levels[$data['sa_item_id']]['qty_on_hand'] ?? 0);
                $qtyAfter = $qtyBefore + $qtyReceived;

                // Record stock movement (the ledger — this is the source of truth)
                $this->movementRepository->save(
                    StockMovement::create(
                        companyId: CompanyId::fromInt($companyId),
                        itemId: ItemId::fromInt($data['sa_item_id']),
                        binId: $item->getBinId(),
                        type: MovementType::purchaseIn(),
                        quantity: $qtyReceived,
                        unitPrice: Money::fromFloat((float) ($data['unit_cost'] ?? 0)),
                        quantityBefore: $qtyBefore,
                        quantityAfter: $qtyAfter,
                        reason: "PO #{$order->getOrderNumber()} received",
                        referenceType: 'purchase_order',
                        referenceId: $purchaseOrderId,
                        createdBy: $userId,
                    )
                );

                // Rebuild stock level projection from ledger
                $this->stockLevelProjector->rebuildForItem($companyId, $data['sa_item_id']);

                $eventItems[] = [
                    'item_id' => (int) $data['sa_item_id'],
                    'quantity' => $qtyReceived,
                    'unit_cost' => (float) ($data['unit_cost'] ?? 0),
                ];
            }

            $order->receive();
            $this->poRepository->save($order);

            // Dispatch domain event
            Event::dispatch(new PurchaseOrderReceived(
                companyId: $companyId,
                purchaseOrderId: $purchaseOrderId,
                orderNumber: $order->getOrderNumber(),
                receivedBy: $userId,
                items: $eventItems,
            ));
        });
    }

    public function createSupplier(int $companyId, array $data): Supplier
    {
        $supplier = Supplier::create(
            companyId: CompanyId::fromInt($companyId),
            name: $data['name'],
            contactPerson: $data['contact_person'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            address: $data['address'] ?? null,
            paymentTerms: $data['payment_terms'] ?? null,
        );
        return $this->supplierRepository->save($supplier);
    }

    public function getSuppliersForCompany(int $companyId): array
    {
        return $this->supplierRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function deleteSupplier(int $supplierId): void
    {
        $this->supplierRepository->delete(SupplierId::fromInt($supplierId));
    }

    public function getOrdersForCompany(int $companyId): array
    {
        return $this->poRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function getOrderById(int $orderId, int $companyId): ?PurchaseOrder
    {
        $order = $this->poRepository->findById(PurchaseOrderId::fromInt($orderId));
        if ($order && $order->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $order;
    }
}
