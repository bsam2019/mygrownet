<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Invoice;
use App\Domain\StockFlow\Entities\InvoiceItem;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\InvoiceRepositoryInterface;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\InvoiceId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private ItemRepositoryInterface $itemRepository,
        private QuotationService $quotationService,
    ) {}

    public function createInvoice(int $companyId, array $data, int $userId): Invoice
    {
        try {
            return DB::transaction(function () use ($companyId, $data, $userId) {
                $items = $data['items'];
                $subtotal = 0.0;
                $invoiceItems = [];

                foreach ($items as $line) {
                    $itemName = $line['item_name'] ?? '';
                    $unitPrice = (float) ($line['unit_price'] ?? 0);
                    $quantity = (float) ($line['quantity'] ?? 1);
                    $lineTotal = $quantity * $unitPrice;
                    $subtotal += $lineTotal;

                    $invoiceItems[] = [
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

                $invoiceNumber = $this->invoiceRepository->nextInvoiceNumber();

                // If linked to a quotation, mark quotation as accepted
                $quotationId = isset($data['sa_quotation_id']) ? (int) $data['sa_quotation_id'] : null;
                if ($quotationId) {
                    $this->quotationService->updateStatus($quotationId, $companyId, 'accepted');
                }

                $invoice = Invoice::create(
                    companyId: CompanyId::fromInt($companyId),
                    invoiceNumber: $invoiceNumber,
                    customerName: $data['customer_name'] ?? null,
                    customerPhone: $data['customer_phone'] ?? null,
                    customerEmail: $data['customer_email'] ?? null,
                    invoiceDate: new DateTimeImmutable($data['invoice_date'] ?? 'now'),
                    dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
                    subtotal: Money::fromFloat($subtotal),
                    discount: $discount,
                    tax: $tax,
                    total: $total,
                    paymentTerms: $data['payment_terms'] ?? null,
                    notes: $data['notes'] ?? null,
                    createdBy: $userId,
                    quotationId: $quotationId,
                    saleId: isset($data['sa_sale_id']) ? (int) $data['sa_sale_id'] : null,
                );

                $savedInvoice = $this->invoiceRepository->save($invoice);

                foreach ($invoiceItems as $ii) {
                    $invoiceItem = InvoiceItem::create(
                        invoiceId: InvoiceId::fromInt($savedInvoice->id()),
                        itemId: $ii['item_id'],
                        itemName: $ii['item_name'],
                        quantity: $ii['quantity'],
                        unitPrice: Money::fromFloat($ii['unit_price']),
                    );
                    $savedInvoice->addItem($invoiceItem);
                }

                return $this->invoiceRepository->save($savedInvoice);
            });
        } catch (Throwable $e) {
            throw new OperationFailedException('create invoice', $e->getMessage());
        }
    }

    public function recordPayment(int $invoiceId, int $companyId, float $amount): Invoice
    {
        $invoice = $this->getInvoiceById($invoiceId, $companyId);
        if (!$invoice) {
            throw new OperationFailedException('record payment', 'Invoice not found');
        }

        return DB::transaction(function () use ($invoice, $amount) {
            $invoice->recordPayment(Money::fromFloat($amount));
            return $this->invoiceRepository->save($invoice);
        });
    }

    public function updateStatus(int $invoiceId, int $companyId, string $status): Invoice
    {
        $invoice = $this->getInvoiceById($invoiceId, $companyId);
        if (!$invoice) {
            throw new OperationFailedException('update invoice status', 'Invoice not found');
        }

        return DB::transaction(function () use ($invoice, $status) {
            match ($status) {
                'sent' => $invoice->markSent(),
                'cancelled' => $invoice->markCancelled(),
                'overdue' => $invoice->markOverdue(),
                default => throw new OperationFailedException('update invoice status', "Invalid status: {$status}"),
            };
            return $this->invoiceRepository->save($invoice);
        });
    }

    public function getInvoiceById(int $invoiceId, int $companyId): ?Invoice
    {
        $invoice = $this->invoiceRepository->findById(InvoiceId::fromInt($invoiceId));
        if ($invoice && $invoice->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $invoice;
    }

    public function getInvoicesForCompany(int $companyId): array
    {
        return $this->invoiceRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }
}
