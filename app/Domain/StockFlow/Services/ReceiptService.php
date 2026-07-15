<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Receipt;
use App\Domain\StockFlow\Entities\ReceiptItem;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\ReceiptRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\ReceiptId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\PaymentMethod;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReceiptService
{
    public function __construct(
        private ReceiptRepositoryInterface $receiptRepository,
    ) {}

    public function createReceipt(int $companyId, array $data, int $userId): Receipt
    {
        try {
            return DB::transaction(function () use ($companyId, $data, $userId) {
                $items = $data['items'] ?? [];
                $subtotal = 0.0;

                $receiptItems = [];
                foreach ($items as $line) {
                    $description = $line['item_description'] ?? '';
                    $unitPrice = (float) ($line['unit_price'] ?? 0);
                    $quantity = (float) ($line['quantity'] ?? 1);
                    $lineTotal = $quantity * $unitPrice;
                    $subtotal += $lineTotal;
                    $receiptItems[] = compact('description', 'quantity', 'unitPrice', 'lineTotal');
                }

                $total = Money::fromFloat((float) ($data['total'] ?? $subtotal));
                $amountReceived = Money::fromFloat((float) ($data['amount_received'] ?? $subtotal));
                $changeDue = Money::fromFloat(max(0, $amountReceived->toFloat() - $total->toFloat()));

                $receiptNumber = $this->receiptRepository->nextReceiptNumber();

                $receipt = Receipt::create(
                    companyId: CompanyId::fromInt($companyId),
                    receiptNumber: $receiptNumber,
                    saleId: isset($data['sa_sale_id']) ? (int) $data['sa_sale_id'] : null,
                    invoiceId: isset($data['sa_invoice_id']) ? (int) $data['sa_invoice_id'] : null,
                    customerName: $data['customer_name'] ?? null,
                    customerPhone: $data['customer_phone'] ?? null,
                    customerEmail: $data['customer_email'] ?? null,
                    receiptDate: new DateTimeImmutable($data['receipt_date'] ?? 'now'),
                    paymentMethod: PaymentMethod::fromString($data['payment_method'] ?? 'cash'),
                    subtotal: Money::fromFloat($subtotal),
                    total: $total,
                    amountReceived: $amountReceived,
                    changeDue: $changeDue,
                    referenceNumber: $data['reference_number'] ?? null,
                    notes: $data['notes'] ?? null,
                    createdBy: $userId,
                );

                $savedReceipt = $this->receiptRepository->save($receipt);

                foreach ($receiptItems as $ri) {
                    $receiptItem = ReceiptItem::create(
                        receiptId: ReceiptId::fromInt($savedReceipt->id()),
                        itemDescription: $ri['description'],
                        quantity: $ri['quantity'],
                        unitPrice: Money::fromFloat($ri['unitPrice']),
                    );
                    $savedReceipt->addItem($receiptItem);
                }

                return $this->receiptRepository->save($savedReceipt);
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('create receipt', $e->getMessage());
        }
    }

    public function getReceiptById(int $receiptId, int $companyId): ?Receipt
    {
        $receipt = $this->receiptRepository->findById(ReceiptId::fromInt($receiptId));
        if ($receipt && $receipt->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $receipt;
    }

    public function getReceiptsForCompany(int $companyId): array
    {
        return $this->receiptRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }
}
