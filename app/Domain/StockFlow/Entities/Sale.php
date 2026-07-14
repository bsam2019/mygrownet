<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\SaleId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\PaymentMethod;
use DateTimeImmutable;

class Sale implements Arrayable
{
    private function __construct(
        private SaleId $id,
        private CompanyId $companyId,
        private string $receiptNumber,
        private DateTimeImmutable $saleDate,
        private string $saleTime,
        private PaymentMethod $paymentMethod,
        private Money $subtotal,
        private Money $discount,
        private Money $tax,
        private Money $total,
        private Money $amountTendered,
        private Money $changeDue,
        private int $soldBy,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        /** @var SaleItem[] */
        private array $items = [],
    ) {}

    public static function create(
        CompanyId $companyId,
        string $receiptNumber,
        DateTimeImmutable $saleDate,
        string $saleTime,
        PaymentMethod $paymentMethod,
        Money $subtotal,
        Money $discount,
        Money $tax,
        Money $total,
        Money $amountTendered,
        Money $changeDue,
        int $soldBy,
        ?string $notes = null,
    ): self {
        return new self(
            SaleId::generate(), $companyId, $receiptNumber, $saleDate, $saleTime,
            $paymentMethod, $subtotal, $discount, $tax, $total, $amountTendered, $changeDue,
            $soldBy, $notes, new DateTimeImmutable(), new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        SaleId $id, CompanyId $companyId, string $receiptNumber, DateTimeImmutable $saleDate,
        string $saleTime, PaymentMethod $paymentMethod, Money $subtotal,
        Money $discount, Money $tax, Money $total, Money $amountTendered,
        Money $changeDue, int $soldBy, ?string $notes,
        DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $receiptNumber, $saleDate, $saleTime,
            $paymentMethod, $subtotal, $discount, $tax, $total, $amountTendered, $changeDue,
            $soldBy, $notes, $createdAt, $updatedAt);
    }

    public function addItem(SaleItem $item): void { $this->items[] = $item; }
    public function isCashPayment(): bool { return $this->paymentMethod->isCash(); }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): SaleId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getReceiptNumber(): string { return $this->receiptNumber; }
    public function getSaleDate(): DateTimeImmutable { return $this->saleDate; }
    public function getPaymentMethod(): PaymentMethod { return $this->paymentMethod; }
    public function getSubtotal(): Money { return $this->subtotal; }
    public function getTotal(): Money { return $this->total; }
    public function getAmountTendered(): Money { return $this->amountTendered; }
    public function getChangeDue(): Money { return $this->changeDue; }
    public function getSoldBy(): int { return $this->soldBy; }
    public function getNotes(): ?string { return $this->notes; }
    public function getItems(): array { return $this->items; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'receipt_number' => $this->receiptNumber,
            'sale_date' => $this->saleDate->format('Y-m-d'),
            'sale_time' => $this->saleTime,
            'payment_method' => $this->paymentMethod->value(),
            'subtotal' => $this->subtotal->toFloat(),
            'discount' => $this->discount->toFloat(),
            'tax' => $this->tax->toFloat(),
            'total' => $this->total->toFloat(),
            'amount_tendered' => $this->amountTendered->toFloat(),
            'change_due' => $this->changeDue->toFloat(),
            'sold_by' => $this->soldBy,
            'notes' => $this->notes,
            'items' => array_map(fn(SaleItem $i) => $i->toArray(), $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
