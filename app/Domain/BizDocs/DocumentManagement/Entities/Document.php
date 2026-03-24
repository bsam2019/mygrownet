<?php

namespace App\Domain\BizDocs\DocumentManagement\Entities;

use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentNumber;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentStatus;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;
use DateTimeImmutable;

class Document
{
    /** @var DocumentItem[] */
    private array $items = [];

    /** @var DocumentPayment[] */
    private array $payments = [];

    private function __construct(
        private ?int $id,
        private int $businessId,
        private int $customerId,
        private ?int $templateId,
        private DocumentType $type,
        private DocumentNumber $number,
        private DateTimeImmutable $issueDate,
        private ?DateTimeImmutable $dueDate,
        private ?DateTimeImmutable $validityDate,
        private DocumentStatus $status,
        private string $currency,
        private ?string $notes,
        private ?string $terms,
        private ?string $paymentInstructions,
        private ?string $pdfPath,
        private string $discountType = 'amount',
        private float $discountValue = 0,
        private bool $collectTax = true,
        private ?string $cancellationReason = null,
        private ?DateTimeImmutable $cancelledAt = null
    ) {
    }

    public static function create(
        int $businessId,
        int $customerId,
        DocumentType $type,
        DocumentNumber $number,
        DateTimeImmutable $issueDate,
        string $currency = 'ZMW',
        ?int $templateId = null,
        ?DateTimeImmutable $dueDate = null,
        ?DateTimeImmutable $validityDate = null,
        ?string $notes = null,
        ?string $terms = null,
        ?string $paymentInstructions = null,
        string $discountType = 'amount',
        float $discountValue = 0,
        bool $collectTax = true
    ): self {
        return new self(
            null,
            $businessId,
            $customerId,
            $templateId,
            $type,
            $number,
            $issueDate,
            $dueDate,
            $validityDate,
            DocumentStatus::draft(),
            $currency,
            $notes,
            $terms,
            $paymentInstructions,
            null,
            $discountType,
            $discountValue,
            $collectTax
        );
    }

    public static function fromPersistence(
        int $id,
        int $businessId,
        int $customerId,
        ?int $templateId,
        DocumentType $type,
        DocumentNumber $number,
        DateTimeImmutable $issueDate,
        ?DateTimeImmutable $dueDate,
        ?DateTimeImmutable $validityDate,
        DocumentStatus $status,
        string $currency,
        ?string $notes,
        ?string $terms,
        ?string $paymentInstructions,
        ?string $pdfPath,
        array $items = [],
        array $payments = [],
        string $discountType = 'amount',
        float $discountValue = 0,
        bool $collectTax = true,
        ?string $cancellationReason = null,
        ?DateTimeImmutable $cancelledAt = null
    ): self {
        $document = new self(
            $id,
            $businessId,
            $customerId,
            $templateId,
            $type,
            $number,
            $issueDate,
            $dueDate,
            $validityDate,
            $status,
            $currency,
            $notes,
            $terms,
            $paymentInstructions,
            $pdfPath,
            $discountType,
            $discountValue,
            $collectTax,
            $cancellationReason,
            $cancelledAt
        );

        $document->items = $items;
        $document->payments = $payments;

        return $document;
    }

    public function addItem(DocumentItem $item): void
    {
        if (!$this->status->isDraft()) {
            throw new \DomainException('Cannot add items to a finalized document');
        }

        $this->items[] = $item;
    }

    public function removeItem(int $index): void
    {
        if (!$this->status->isDraft()) {
            throw new \DomainException('Cannot remove items from a finalized document');
        }

        if (!isset($this->items[$index])) {
            throw new \InvalidArgumentException('Item not found');
        }

        array_splice($this->items, $index, 1);
    }

    public function finalize(): void
    {
        if (!$this->status->isDraft()) {
            throw new \DomainException('Document is already finalized');
        }

        if (empty($this->items)) {
            throw new \DomainException('Cannot finalize document without items');
        }

        $this->status = DocumentStatus::sent();
    }

    public function issue(): void
    {
        if (!$this->status->isDraft()) {
            throw new \DomainException('Document is already issued');
        }

        if (empty($this->items)) {
            throw new \DomainException('Cannot issue document without items');
        }

        // Receipts use 'issued' status instead of 'sent'
        $this->status = DocumentStatus::fromString('issued');
    }

    public function changeStatus(DocumentStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus, $this->type)) {
            throw new \DomainException(
                "Cannot transition from {$this->status->value()} to {$newStatus->value()}"
            );
        }

        $this->status = $newStatus;
    }

    public function markAsPaid(): void
    {
        $this->changeStatus(DocumentStatus::paid());
    }

    public function cancel(): void
    {
        $this->changeStatus(DocumentStatus::fromString('cancelled'));
    }

    public function cancelWithReason(string $reason): void
    {
        $this->changeStatus(DocumentStatus::fromString('cancelled'));
        $this->cancellationReason = $reason;
        $this->cancelledAt = new DateTimeImmutable();
    }

    public function voidWithReason(string $reason): void
    {
        if (!$this->type->isReceipt()) {
            throw new \DomainException('Only receipts can be voided');
        }
        
        $this->changeStatus(DocumentStatus::fromString('voided'));
        $this->cancellationReason = $reason;
        $this->cancelledAt = new DateTimeImmutable();
    }

    public function recordPayment(DocumentPayment $payment): void
    {
        if (!$this->type->isInvoice()) {
            throw new \DomainException('Can only record payments against invoices');
        }

        if ($this->status->equals(DocumentStatus::paid())) {
            throw new \DomainException('Invoice is already fully paid');
        }

        if ($this->status->equals(DocumentStatus::fromString('cancelled'))) {
            throw new \DomainException('Cannot record payment for cancelled invoice');
        }

        $this->payments[] = $payment;

        // Auto-update status based on payment amount
        $this->updatePaymentStatus();
    }

    private function updatePaymentStatus(): void
    {
        $totals = $this->calculateTotals();
        $grandTotal = $totals['grand_total'];
        $totalPaid = $this->calculateTotalPaid();

        if ($totalPaid->greaterThanOrEqual($grandTotal)) {
            $this->status = DocumentStatus::paid();
        } elseif ($totalPaid->amount() > 0) {
            $this->status = DocumentStatus::fromString('partially_paid');
        }
    }

    public function calculateTotalPaid(): Money
    {
        $total = Money::fromAmount(0, $this->currency);

        foreach ($this->payments as $payment) {
            $total = $total->add($payment->amount());
        }

        return $total;
    }

    public function calculateRemainingBalance(): Money
    {
        $totals = $this->calculateTotals();
        $grandTotal = $totals['grand_total'];
        $totalPaid = $this->calculateTotalPaid();

        return $grandTotal->subtract($totalPaid);
    }

    public function calculateTotals(): array
    {
        $subtotal = Money::fromAmount(0, $this->currency);
        $taxTotal = Money::fromAmount(0, $this->currency);
        $discountTotal = Money::fromAmount(0, $this->currency);

        foreach ($this->items as $item) {
            $subtotal = $subtotal->add($item->calculateSubtotal());
            if ($this->collectTax) {
                $taxTotal = $taxTotal->add($item->calculateTaxAmount());
            }
            $discountTotal = $discountTotal->add($item->discountAmount());
        }

        // Apply document-level discount
        $documentDiscount = Money::fromAmount(0, $this->currency);
        if ($this->discountValue > 0) {
            if ($this->discountType === 'percentage') {
                $documentDiscount = Money::fromAmount((int)($subtotal->amount() * $this->discountValue / 100), $this->currency);
            } else {
                $documentDiscount = Money::fromAmount((int)($this->discountValue * 100), $this->currency);
            }
        }

        $discountTotal = $discountTotal->add($documentDiscount);
        $grandTotal = $subtotal->add($taxTotal)->subtract($discountTotal);

        return [
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'grand_total' => $grandTotal,
        ];
    }

    public function setPdfPath(string $path): void
    {
        $this->pdfPath = $path;
    }

    // Getters
    public function id(): ?int
    {
        return $this->id;
    }

    public function businessId(): int
    {
        return $this->businessId;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function templateId(): ?int
    {
        return $this->templateId;
    }

    public function type(): DocumentType
    {
        return $this->type;
    }

    public function number(): DocumentNumber
    {
        return $this->number;
    }

    public function issueDate(): DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function dueDate(): ?DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function validityDate(): ?DateTimeImmutable
    {
        return $this->validityDate;
    }

    public function status(): DocumentStatus
    {
        return $this->status;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function terms(): ?string
    {
        return $this->terms;
    }

    public function paymentInstructions(): ?string
    {
        return $this->paymentInstructions;
    }

    public function pdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function payments(): array
    {
        return $this->payments;
    }

    public function cancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function cancelledAt(): ?DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function isCancelled(): bool
    {
        return $this->status->equals(DocumentStatus::fromString('cancelled')) ||
               $this->status->equals(DocumentStatus::fromString('voided'));
    }

    public function discountType(): string
    {
        return $this->discountType;
    }

    public function discountValue(): float
    {
        return $this->discountValue;
    }

    public function collectTax(): bool
    {
        return $this->collectTax;
    }

    public function toArray(): array
    {
        $totals = $this->calculateTotals();
        
        return [
            'id' => $this->id,
            'businessId' => $this->businessId,
            'customerId' => $this->customerId,
            'templateId' => $this->templateId,
            'documentType' => $this->type->value(),
            'documentNumber' => $this->number->value(),
            'issueDate' => $this->issueDate->format('Y-m-d'),
            'dueDate' => $this->dueDate?->format('Y-m-d'),
            'validityDate' => $this->validityDate?->format('Y-m-d'),
            'subtotal' => $totals['subtotal']->amount() / 100,
            'taxTotal' => $totals['tax_total']->amount() / 100,
            'discountTotal' => $totals['discount_total']->amount() / 100,
            'grandTotal' => $totals['grand_total']->amount() / 100,
            'currency' => $this->currency,
            'status' => $this->status->value(),
            'notes' => $this->notes,
            'terms' => $this->terms,
            'paymentInstructions' => $this->paymentInstructions,
            'pdfPath' => $this->pdfPath,
            'discountType' => $this->discountType,
            'discountValue' => $this->discountValue,
            'collectTax' => $this->collectTax,
            'cancellationReason' => $this->cancellationReason,
            'cancelledAt' => $this->cancelledAt?->format('Y-m-d H:i:s'),
            'items' => array_map(function (DocumentItem $item) {
                return [
                    'description' => $item->description(),
                    'dimensions' => $item->dimensions(),
                    'dimensionsValue' => $item->dimensionsValue(),
                    'quantity' => $item->quantity(),
                    'unitPrice' => $item->unitPrice()->amount() / 100,
                    'taxRate' => $item->taxRate(),
                    'discountAmount' => $item->discountAmount()->amount() / 100,
                    'lineTotal' => $item->calculateLineTotal()->amount() / 100,
                ];
            }, $this->items),
        ];
    }
}
