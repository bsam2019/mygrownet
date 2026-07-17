<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Transfer;
use App\Domain\StockFlow\Entities\TransferItem;
use App\Domain\StockFlow\Entities\StockMovement;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\TransferRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\TransferId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\WarehouseId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\MovementType;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransferService
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
        private ItemRepositoryInterface $itemRepository,
        private StockMovementRepositoryInterface $movementRepository,
        private StockLevelProjector $stockLevelProjector,
    ) {}

    public function createTransfer(int $companyId, array $data, int $userId): Transfer
    {
        if ($data['from_warehouse_id'] === $data['to_warehouse_id']) {
            throw new OperationFailedException('create transfer', 'Source and destination warehouses must be different.');
        }

        try {
            return DB::transaction(function () use ($companyId, $data, $userId) {
                $transferNumber = $this->transferRepository->nextTransferNumber();

                $transfer = Transfer::create(
                    companyId: CompanyId::fromInt($companyId),
                    transferNumber: $transferNumber,
                    fromWarehouseId: WarehouseId::fromInt($data['from_warehouse_id']),
                    toWarehouseId: WarehouseId::fromInt($data['to_warehouse_id']),
                    transferredBy: $userId,
                    notes: $data['notes'] ?? null,
                );

                $savedTransfer = $this->transferRepository->save($transfer);

                $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);

                foreach ($data['items'] as $line) {
                    $item = $this->itemRepository->findById(ItemId::fromInt($line['sa_item_id']));
                    if (!$item || $item->getCompanyId()->toInt() !== $companyId) {
                        throw new OperationFailedException('create transfer', "Item #{$line['sa_item_id']} not found");
                    }

                    $availableQty = (float) ($levels[$line['sa_item_id']]['qty_on_hand'] ?? $item->getSystemQuantity());
                    if ($availableQty < $line['quantity']) {
                        throw new OperationFailedException('create transfer', "Insufficient stock for item {$item->getName()}. Available: {$availableQty}, requested: {$line['quantity']}");
                    }

                    $transferItem = TransferItem::create(
                        itemId: ItemId::fromInt($line['sa_item_id']),
                        quantity: $line['quantity'],
                        unitCost: isset($line['unit_cost']) ? Money::fromFloat((float) $line['unit_cost']) : null,
                        itemName: $item->getName(),
                    );
                    $savedTransfer->addItem($transferItem);

                    // Record outgoing stock movement
                    $qtyBefore = (float) ($levels[$line['sa_item_id']]['qty_on_hand'] ?? $item->getSystemQuantity());
                    $qtyAfter = max(0, $qtyBefore - $line['quantity']);

                    $this->movementRepository->save(
                        StockMovement::create(
                            companyId: CompanyId::fromInt($companyId),
                            itemId: ItemId::fromInt($line['sa_item_id']),
                            binId: $item->getBinId(),
                            type: MovementType::fromString('transfer_out'),
                            quantity: -$line['quantity'],
                            unitPrice: isset($line['unit_cost']) ? Money::fromFloat((float) $line['unit_cost']) : $item->getUnitPrice(),
                            quantityBefore: $qtyBefore,
                            quantityAfter: $qtyAfter,
                            reason: "Transfer {$transferNumber} out",
                            referenceType: 'transfer',
                            referenceId: $savedTransfer->id(),
                            createdBy: $userId,
                        )
                    );

                    $this->stockLevelProjector->rebuildForItem($companyId, $line['sa_item_id']);
                }

                $savedTransfer->send();
                $this->transferRepository->save($savedTransfer);

                return $savedTransfer;
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('create transfer', $e->getMessage());
        }
    }

    public function receiveTransfer(int $transferId, int $companyId, int $receivedBy): Transfer
    {
        try {
            return DB::transaction(function () use ($transferId, $companyId, $receivedBy) {
                $transfer = $this->transferRepository->findById(TransferId::fromInt($transferId));
                if (!$transfer || $transfer->getCompanyId()->toInt() !== $companyId) {
                    throw new OperationFailedException('receive transfer', 'Transfer not found');
                }
                if (!$transfer->isInTransit()) {
                    throw new OperationFailedException('receive transfer', 'Only in-transit transfers can be received.');
                }

                $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);

                foreach ($transfer->getItems() as $item) {
                    $domainItem = $this->itemRepository->findById($item->getItemId());
                    if (!$domainItem) continue;

                    $qtyBefore = (float) ($levels[$domainItem->id()]['qty_on_hand'] ?? $domainItem->getSystemQuantity());
                    $qtyAfter = $qtyBefore + $item->getQuantity();

                    // Record incoming stock movement at destination
                    $this->movementRepository->save(
                        StockMovement::create(
                            companyId: CompanyId::fromInt($companyId),
                            itemId: $item->getItemId(),
                            binId: $domainItem->getBinId(),
                            type: MovementType::fromString('transfer_in'),
                            quantity: $item->getQuantity(),
                            unitPrice: $item->getUnitCost() ?? $domainItem->getUnitPrice(),
                            quantityBefore: $qtyBefore,
                            quantityAfter: $qtyAfter,
                            reason: "Transfer {$transfer->getTransferNumber()} received",
                            referenceType: 'transfer',
                            referenceId: $transfer->id(),
                            createdBy: $receivedBy,
                        )
                    );

                    $this->stockLevelProjector->rebuildForItem($companyId, $domainItem->id());
                }

                $transfer->receive($receivedBy);
                return $this->transferRepository->save($transfer);
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('receive transfer', $e->getMessage());
        }
    }

    public function cancelTransfer(int $transferId, int $companyId): Transfer
    {
        try {
            return DB::transaction(function () use ($transferId, $companyId) {
                $transfer = $this->transferRepository->findById(TransferId::fromInt($transferId));
                if (!$transfer || $transfer->getCompanyId()->toInt() !== $companyId) {
                    throw new OperationFailedException('cancel transfer', 'Transfer not found');
                }
                if ($transfer->isReceived()) {
                    throw new OperationFailedException('cancel transfer', 'Cannot cancel a received transfer.');
                }

                // If in_transit, reverse stock movements (add stock back)
                if ($transfer->isInTransit()) {
                    $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);

                    foreach ($transfer->getItems() as $item) {
                        $domainItem = $this->itemRepository->findById($item->getItemId());
                        if (!$domainItem) continue;

                        $qtyBefore = (float) ($levels[$domainItem->id()]['qty_on_hand'] ?? $domainItem->getSystemQuantity());
                        $qtyAfter = $qtyBefore + $item->getQuantity();

                        $this->movementRepository->save(
                            StockMovement::create(
                                companyId: CompanyId::fromInt($companyId),
                                itemId: $item->getItemId(),
                                binId: $domainItem->getBinId(),
                                type: MovementType::fromString('transfer_cancelled'),
                                quantity: $item->getQuantity(),
                                unitPrice: $domainItem->getUnitPrice(),
                                quantityBefore: $qtyBefore,
                                quantityAfter: $qtyAfter,
                                reason: "Transfer {$transfer->getTransferNumber()} cancelled",
                                referenceType: 'transfer',
                                referenceId: $transfer->id(),
                                createdBy: 0,
                            )
                        );

                        $this->stockLevelProjector->rebuildForItem($companyId, $domainItem->id());
                    }
                }

                $transfer->cancel();
                return $this->transferRepository->save($transfer);
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('cancel transfer', $e->getMessage());
        }
    }

    public function getTransfers(int $companyId): array
    {
        return $this->transferRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function getTransferById(int $transferId, int $companyId): ?Transfer
    {
        $transfer = $this->transferRepository->findById(TransferId::fromInt($transferId));
        if ($transfer && $transfer->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $transfer;
    }
}
