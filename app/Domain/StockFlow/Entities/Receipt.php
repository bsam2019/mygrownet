<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\ReceiptId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\PaymentMethod;
use DateTimeImmutable;

class Receipt implements Arrayable
{
    private function __construct(
        private ReceiptId $id,
        private CompanyId $companyId,
        private string $receiptNumber,
        private ?int $saleId,
        private ?int $invoiceId,
        private ?string $customerName,
        private ?string $customerPhone,
        private ?string $customerEmail,
        private DateTimeImmutable $receiptDate,
        private PaymentMethod $paymentMethod,
        private Money $subtotal,
        private Money $total,
        private Money $amountReceived,
        private Money $changeDue,
        private ?string $referenceNumber,
        private ?string $notes,
        private int $createdBy,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        /** @var ReceiptItem[] */
        private array $items = [],
    ) {}

    public static function create(
        CompanyId $companyId,
        string $receiptNumber,
        ?int $saleId,
        ?int $invoiceId,
        ?string $customerName,
        ?string $customerPhone,
        ?string $customerEmail,
        DateTimeImmutable $receiptDate,
        PaymentMethod $paymentMethod,
        Money $subtotal,
        Money $total,
        Money $amountReceived,
        Money $changeDue,
        ?string $referenceNumber,
        ?string $notes,
        int $createdBy,
    ): self {
        return new self(
            ReceiptId::generate(), $companyId, $receiptNumber, $saleId, $invoiceId,
            $customerName, $customerPhone, $customerEmail, $receiptDate,
            $paymentMethod, $subtotal, $total, $amountReceived, $changeDue,
            $referenceNumber, $notes, $createdBy,
            new DateTimeImmutable(), new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        ReceiptId $id, CompanyId $companyId, string $receiptNumber,
        ?int $saleId, ?int $invoiceId,
        ?string $customerName, ?string $customerPhone, ?string $customerEmail,
        DateTimeImmutable $receiptDate, PaymentMethod $paymentMethod,
        Money $subtotal, Money $total, Money $amountReceived, Money $changeDue,
        ?string $referenceNumber, ?string $notes, int $createdBy,
        DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $receiptNumber, $saleId, $invoiceId,
            $customerName, $customerPhone, $customerEmail, $receiptDate,
            $paymentMethod, $subtotal, $total, $amountReceived, $changeDue,
            $referenceNumber, $notes, $createdBy, $createdAt, $updatedAt);
    }

    public function addItem(ReceiptItem $item): void { $this->items[] = $item; }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): ReceiptId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getReceiptNumber(): string { return $this->receiptNumber; }
    public function getSaleId(): ?int { return $this->saleId; }
    public function getInvoiceId(): ?int { return $this->invoiceId; }
    public function getCustomerName(): ?string { return $this->customerName; }
    public function getPaymentMethod(): PaymentMethod { return $this->paymentMethod; }
    public function getTotal(): Money { return $this->total; }
    public function getAmountReceived(): Money { return $this->amountReceived; }
    public function getChangeDue(): Money { return $this->changeDue; }
    public function getReferenceNumber(): ?string { return $this->referenceNumber; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedBy(): int { return $this->createdBy; }
    public function getItems(): array { return $this->items; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'receipt_number' => $this->receiptNumber,
            'sa_sale_id' => $this->saleId,
            'sa_invoice_id' => $this->invoiceId,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_email' => $this->customerEmail,
            'receipt_date' => $this->receiptDate->format('Y-m-d'),
            'payment_method' => $this->paymentMethod->value(),
            'subtotal' => $this->subtotal->toFloat(),
            'total' => $this->total->toFloat(),
            'amount_received' => $this->amountReceived->toFloat(),
            'change_due' => $this->changeDue->toFloat(),
            'reference_number' => $this->referenceNumber,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'items' => array_map(fn(ReceiptItem $i) => $i->toArray(), $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
