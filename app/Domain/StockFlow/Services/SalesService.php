<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Sale;
use App\Domain\StockFlow\Entities\SaleItem;
use App\Domain\StockFlow\Entities\StockMovement;
use App\Domain\StockFlow\Events\SaleCompleted;
use App\Domain\StockFlow\Exceptions\InsufficientStockException;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\SaleRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\Repositories\CashRegisterRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\SaleId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\PaymentMethod;
use App\Domain\StockFlow\ValueObjects\MovementType;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Throwable;

class SalesService
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private ItemRepositoryInterface $itemRepository,
        private StockMovementRepositoryInterface $movementRepository,
        private CashRegisterRepositoryInterface $cashRegisterRepository,
        private StockLevelProjector $stockLevelProjector,
    ) {}

    public function createSale(int $companyId, array $data, int $userId): Sale
    {
        try {
            return DB::transaction(function () use ($companyId, $data, $userId) {
                $paymentMethod = PaymentMethod::fromString($data['payment_method']);
                $items = $data['items'];
                $subtotal = 0.0;
                $saleItems = [];

                // Validate stock against projection (not items.system_quantity)
                $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);

                foreach ($items as $line) {
                    $item = $this->itemRepository->findById(ItemId::fromInt($line['sa_item_id']));
                    if (!$item || $item->getCompanyId()->toInt() !== $companyId) {
                        throw new OperationFailedException('create sale', "Item #{$line['sa_item_id']} not found");
                    }
                    $availableQty = (float) ($levels[$line['sa_item_id']]['qty_on_hand'] ?? $item->getSystemQuantity());
                    if ($availableQty < $line['quantity']) {
                        throw new InsufficientStockException($item->id(), $line['quantity'], $availableQty);
                    }
                    $lineTotal = $line['quantity'] * $line['unit_price'];
                    $subtotal += $lineTotal;
                    $saleItems[] = ['item' => $item, 'quantity' => $line['quantity'], 'unit_price' => $line['unit_price'], 'total' => $lineTotal];
                }

                $receiptNumber = $this->saleRepository->nextReceiptNumber();
                $saleDate = new DateTimeImmutable($data['sale_date']);

                $sale = Sale::create(
                    companyId: CompanyId::fromInt($companyId),
                    receiptNumber: $receiptNumber,
                    saleDate: $saleDate,
                    saleTime: $saleDate->format('H:i'),
                    paymentMethod: $paymentMethod,
                    subtotal: Money::fromFloat($subtotal),
                    discount: Money::fromFloat(0),
                    tax: Money::fromFloat(0),
                    total: Money::fromFloat($subtotal),
                    amountTendered: Money::fromFloat((float) $data['amount_tendered']),
                    changeDue: Money::fromFloat(max(0, (float) $data['amount_tendered'] - $subtotal)),
                    soldBy: $userId,
                    notes: $data['notes'] ?? null,
                    currency: $data['currency'] ?? 'USD',
                    exchangeRate: isset($data['exchange_rate']) ? (float) $data['exchange_rate'] : null,
                );

                $savedSale = $this->saleRepository->save($sale);

                $eventItems = [];

                foreach ($saleItems as $i => $si) {
                    $saleItem = SaleItem::create(
                        saleId: SaleId::fromInt($savedSale->id()),
                        itemId: $si['item']->getId(),
                        lotId: isset($items[$i]['sa_lot_id']) ? LotId::fromInt((int) $items[$i]['sa_lot_id']) : null,
                        itemName: $si['item']->getName(),
                        quantity: $si['quantity'],
                        unitPrice: Money::fromFloat($si['unit_price']),
                    );
                    $savedSale->addItem($saleItem);

                    // Record stock movement (the ledger — this is the source of truth)
                    $qtyBefore = (float) ($levels[$si['item']->id()]['qty_on_hand'] ?? $si['item']->getSystemQuantity());
                    $qtyAfter = max(0, $qtyBefore - $si['quantity']);

                    $this->movementRepository->save(
                        StockMovement::create(
                            companyId: CompanyId::fromInt($companyId),
                            itemId: $si['item']->getId(),
                            binId: $si['item']->getBinId(),
                            type: MovementType::saleOut(),
                            quantity: -$si['quantity'],
                            unitPrice: Money::fromFloat($si['unit_price']),
                            quantityBefore: $qtyBefore,
                            quantityAfter: $qtyAfter,
                            reason: "Sale {$receiptNumber}",
                            referenceType: 'sale',
                            referenceId: $savedSale->id(),
                            createdBy: $userId,
                        )
                    );

                    // Rebuild stock level projection for this item from ledger
                    $this->stockLevelProjector->rebuildForItem($companyId, $si['item']->id());

                    $eventItems[] = [
                        'item_id' => $si['item']->id(),
                        'quantity' => $si['quantity'],
                        'unit_price' => $si['unit_price'],
                        'line_total' => $si['total'],
                    ];
                }

                // Record cash in register if cash payment
                if ($paymentMethod->isCash()) {
                    $register = $this->cashRegisterRepository->findByDate(
                        CompanyId::fromInt($companyId),
                        $saleDate,
                    );

                    if (!$register) {
                        $register = \App\Domain\StockFlow\Entities\CashRegister::create(
                            companyId: CompanyId::fromInt($companyId),
                            registerDate: $saleDate,
                            openingBalance: Money::zero(),
                            openedBy: $userId,
                        );
                    }

                    if ($register->isOpen()) {
                        $register->recordSale(Money::fromFloat($subtotal));
                        $this->cashRegisterRepository->save($register);
                    }
                }

                // Dispatch domain event
                Event::dispatch(new SaleCompleted(
                    companyId: $companyId,
                    saleId: $savedSale->id(),
                    receiptNumber: $receiptNumber,
                    total: $subtotal,
                    paymentMethod: $paymentMethod->value(),
                    soldBy: $userId,
                    items: $eventItems,
                ));

                return $savedSale;
            });
        } catch (Throwable $e) {
            if ($e instanceof InsufficientStockException) throw $e;
            throw new OperationFailedException('create sale', $e->getMessage());
        }
    }

    public function getSaleById(int $saleId, int $companyId): ?Sale
    {
        $sale = $this->saleRepository->findById(SaleId::fromInt($saleId));
        if ($sale && $sale->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $sale;
    }

    public function getSalesForCompany(int $companyId): array
    {
        return $this->saleRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function getTodayTotal(int $companyId): float
    {
        return $this->saleRepository->getTodayTotal(CompanyId::fromInt($companyId));
    }
}
