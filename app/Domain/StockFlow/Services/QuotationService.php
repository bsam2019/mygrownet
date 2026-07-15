<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Quotation;
use App\Domain\StockFlow\Entities\QuotationItem;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\QuotationRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\QuotationId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class QuotationService
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepository,
        private ItemRepositoryInterface $itemRepository,
    ) {}

    public function createQuotation(int $companyId, array $data, int $userId): Quotation
    {
        try {
            return DB::transaction(function () use ($companyId, $data, $userId) {
                $items = $data['items'];
                $subtotal = 0.0;
                $quotationItems = [];

                foreach ($items as $line) {
                    $itemName = $line['item_name'] ?? '';
                    $unitPrice = (float) ($line['unit_price'] ?? 0);
                    $quantity = (float) ($line['quantity'] ?? 1);
                    $lineTotal = $quantity * $unitPrice;
                    $subtotal += $lineTotal;

                    $quotationItems[] = [
                        'item_id' => isset($line['sa_item_id']) ? ItemId::fromInt((int) $line['sa_item_id']) : null,
                        'item_name' => $itemName,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total' => $lineTotal,
                    ];
                }

                $discount = Money::fromFloat((float) ($data['discount'] ?? 0));
                $tax = Money::fromFloat((float) ($data['tax'] ?? 0));
                $total = Money::fromFloat($subtotal + $tax->toFloat() - $discount->toFloat());

                $quotationNumber = $this->quotationRepository->nextQuotationNumber();

                $quotation = Quotation::create(
                    companyId: CompanyId::fromInt($companyId),
                    quotationNumber: $quotationNumber,
                    customerName: $data['customer_name'] ?? null,
                    customerPhone: $data['customer_phone'] ?? null,
                    customerEmail: $data['customer_email'] ?? null,
                    quotationDate: new DateTimeImmutable($data['quotation_date'] ?? 'now'),
                    expiryDate: isset($data['expiry_date']) ? new DateTimeImmutable($data['expiry_date']) : null,
                    subtotal: Money::fromFloat($subtotal),
                    discount: $discount,
                    tax: $tax,
                    total: $total,
                    notes: $data['notes'] ?? null,
                    termsConditions: $data['terms_conditions'] ?? null,
                    createdBy: $userId,
                );

                $savedQuotation = $this->quotationRepository->save($quotation);

                foreach ($quotationItems as $qi) {
                    $quotationItem = QuotationItem::create(
                        quotationId: QuotationId::fromInt($savedQuotation->id()),
                        itemId: $qi['item_id'],
                        itemName: $qi['item_name'],
                        quantity: $qi['quantity'],
                        unitPrice: Money::fromFloat($qi['unit_price']),
                    );
                    $savedQuotation->addItem($quotationItem);
                }

                return $this->quotationRepository->save($savedQuotation);
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('create quotation', $e->getMessage());
        }
    }

    public function getQuotationById(int $quotationId, int $companyId): ?Quotation
    {
        $quotation = $this->quotationRepository->findById(QuotationId::fromInt($quotationId));
        if ($quotation && $quotation->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $quotation;
    }

    public function updateStatus(int $quotationId, int $companyId, string $status, ?int $saleId = null): Quotation
    {
        $quotation = $this->getQuotationById($quotationId, $companyId);
        if (!$quotation) {
            throw new OperationFailedException('update quotation status', 'Quotation not found');
        }

        return DB::transaction(function () use ($quotation, $status, $saleId) {
            match ($status) {
                'sent' => $quotation->markSent(),
                'accepted' => $quotation->markAccepted(),
                'declined' => $quotation->markDeclined(),
                'expired' => $quotation->markExpired(),
                'converted' => $quotation->markConverted($saleId ?? 0),
                default => throw new OperationFailedException('update quotation status', "Invalid status: {$status}"),
            };
            return $this->quotationRepository->save($quotation);
        });
    }

    public function getQuotationsForCompany(int $companyId): array
    {
        return $this->quotationRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }
}
