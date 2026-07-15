<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\InvoiceId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class Invoice implements Arrayable
{
    private function __construct(
        private InvoiceId $id,
        private CompanyId $companyId,
        private string $invoiceNumber,
        private ?string $customerName,
        private ?string $customerPhone,
        private ?string $customerEmail,
        private DateTimeImmutable $invoiceDate,
        private ?DateTimeImmutable $dueDate,
        private string $status,
        private Money $subtotal,
        private Money $discount,
        private Money $tax,
        private Money $total,
        private Money $amountPaid,
        private Money $balanceDue,
        private ?string $paymentTerms,
        private ?string $notes,
        private int $createdBy,
        private ?int $quotationId,
        private ?int $saleId,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        /** @var InvoiceItem[] */
        private array $items = [],
    ) {}

    public static function create(
        CompanyId $companyId,
        string $invoiceNumber,
        ?string $customerName,
        ?string $customerPhone,
        ?string $customerEmail,
        DateTimeImmutable $invoiceDate,
        ?DateTimeImmutable $dueDate,
        Money $subtotal,
        Money $discount,
        Money $tax,
        Money $total,
        ?string $paymentTerms,
        ?string $notes,
        int $createdBy,
        ?int $quotationId = null,
        ?int $saleId = null,
    ): self {
        return new self(
            InvoiceId::generate(), $companyId, $invoiceNumber, $customerName,
            $customerPhone, $customerEmail, $invoiceDate, $dueDate,
            'draft', $subtotal, $discount, $tax, $total,
            Money::zero(), Money::fromFloat($total->toFloat()),
            $paymentTerms, $notes, $createdBy, $quotationId, $saleId,
            new DateTimeImmutable(), new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        InvoiceId $id, CompanyId $companyId, string $invoiceNumber,
        ?string $customerName, ?string $customerPhone, ?string $customerEmail,
        DateTimeImmutable $invoiceDate, ?DateTimeImmutable $dueDate,
        string $status, Money $subtotal, Money $discount, Money $tax, Money $total,
        Money $amountPaid, Money $balanceDue, ?string $paymentTerms, ?string $notes,
        int $createdBy, ?int $quotationId, ?int $saleId,
        DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $invoiceNumber, $customerName,
            $customerPhone, $customerEmail, $invoiceDate, $dueDate,
            $status, $subtotal, $discount, $tax, $total, $amountPaid,
            $balanceDue, $paymentTerms, $notes, $createdBy, $quotationId,
            $saleId, $createdAt, $updatedAt);
    }

    public function addItem(InvoiceItem $item): void { $this->items[] = $item; }

    public function markSent(): void { $this->status = 'sent'; }
    public function markPaid(): void { $this->status = 'paid'; }
    public function markOverdue(): void { $this->status = 'overdue'; }
    public function markCancelled(): void { $this->status = 'cancelled'; }
    public function markPartiallyPaid(): void { $this->status = 'partially_paid'; }

    public function recordPayment(Money $amount): void
    {
        $newPaid = $this->amountPaid->add($amount);
        $this->amountPaid = $newPaid;
        $this->balanceDue = Money::fromFloat(max(0, $this->total->toFloat() - $newPaid->toFloat()));
        if ($this->balanceDue->isZero()) {
            $this->markPaid();
        } else {
            $this->markPartiallyPaid();
        }
    }

    public function isOverdue(): bool
    {
        if ($this->dueDate === null) return false;
        if (in_array($this->status, ['paid', 'cancelled'])) return false;
        return $this->dueDate < new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): InvoiceId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getInvoiceNumber(): string { return $this->invoiceNumber; }
    public function getCustomerName(): ?string { return $this->customerName; }
    public function getDueDate(): ?DateTimeImmutable { return $this->dueDate; }
    public function getStatus(): string { return $this->status; }
    public function getSubtotal(): Money { return $this->subtotal; }
    public function getDiscount(): Money { return $this->discount; }
    public function getTax(): Money { return $this->tax; }
    public function getTotal(): Money { return $this->total; }
    public function getAmountPaid(): Money { return $this->amountPaid; }
    public function getBalanceDue(): Money { return $this->balanceDue; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedBy(): int { return $this->createdBy; }
    public function getQuotationId(): ?int { return $this->quotationId; }
    public function getSaleId(): ?int { return $this->saleId; }
    public function getItems(): array { return $this->items; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'invoice_number' => $this->invoiceNumber,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_email' => $this->customerEmail,
            'invoice_date' => $this->invoiceDate->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'status' => $this->status,
            'subtotal' => $this->subtotal->toFloat(),
            'discount' => $this->discount->toFloat(),
            'tax' => $this->tax->toFloat(),
            'total' => $this->total->toFloat(),
            'amount_paid' => $this->amountPaid->toFloat(),
            'balance_due' => $this->balanceDue->toFloat(),
            'payment_terms' => $this->paymentTerms,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'sa_quotation_id' => $this->quotationId,
            'sa_sale_id' => $this->saleId,
            'items' => array_map(fn(InvoiceItem $i) => $i->toArray(), $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
