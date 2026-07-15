<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\QuotationId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class Quotation implements Arrayable
{
    private function __construct(
        private QuotationId $id,
        private CompanyId $companyId,
        private string $quotationNumber,
        private ?string $customerName,
        private ?string $customerPhone,
        private ?string $customerEmail,
        private DateTimeImmutable $quotationDate,
        private ?DateTimeImmutable $expiryDate,
        private string $status,
        private Money $subtotal,
        private Money $discount,
        private Money $tax,
        private Money $total,
        private ?string $notes,
        private ?string $termsConditions,
        private int $createdBy,
        private ?int $convertedToSaleId,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        /** @var QuotationItem[] */
        private array $items = [],
    ) {}

    public static function create(
        CompanyId $companyId,
        string $quotationNumber,
        ?string $customerName,
        ?string $customerPhone,
        ?string $customerEmail,
        DateTimeImmutable $quotationDate,
        ?DateTimeImmutable $expiryDate,
        Money $subtotal,
        Money $discount,
        Money $tax,
        Money $total,
        ?string $notes,
        ?string $termsConditions,
        int $createdBy,
    ): self {
        return new self(
            QuotationId::generate(), $companyId, $quotationNumber, $customerName,
            $customerPhone, $customerEmail, $quotationDate, $expiryDate,
            'draft', $subtotal, $discount, $tax, $total, $notes,
            $termsConditions, $createdBy, null,
            new DateTimeImmutable(), new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        QuotationId $id, CompanyId $companyId, string $quotationNumber,
        ?string $customerName, ?string $customerPhone, ?string $customerEmail,
        DateTimeImmutable $quotationDate, ?DateTimeImmutable $expiryDate,
        string $status, Money $subtotal, Money $discount, Money $tax, Money $total,
        ?string $notes, ?string $termsConditions, int $createdBy,
        ?int $convertedToSaleId,
        DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $quotationNumber, $customerName,
            $customerPhone, $customerEmail, $quotationDate, $expiryDate,
            $status, $subtotal, $discount, $tax, $total, $notes,
            $termsConditions, $createdBy, $convertedToSaleId, $createdAt, $updatedAt);
    }

    public function addItem(QuotationItem $item): void { $this->items[] = $item; }

    public function markSent(): void { $this->status = 'sent'; }
    public function markAccepted(): void { $this->status = 'accepted'; }
    public function markDeclined(): void { $this->status = 'declined'; }
    public function markExpired(): void { $this->status = 'expired'; }
    public function markConverted(int $saleId): void { $this->status = 'converted'; $this->convertedToSaleId = $saleId; }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): QuotationId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getQuotationNumber(): string { return $this->quotationNumber; }
    public function getCustomerName(): ?string { return $this->customerName; }
    public function getCustomerPhone(): ?string { return $this->customerPhone; }
    public function getCustomerEmail(): ?string { return $this->customerEmail; }
    public function getQuotationDate(): DateTimeImmutable { return $this->quotationDate; }
    public function getExpiryDate(): ?DateTimeImmutable { return $this->expiryDate; }
    public function getStatus(): string { return $this->status; }
    public function getSubtotal(): Money { return $this->subtotal; }
    public function getDiscount(): Money { return $this->discount; }
    public function getTax(): Money { return $this->tax; }
    public function getTotal(): Money { return $this->total; }
    public function getNotes(): ?string { return $this->notes; }
    public function getTermsConditions(): ?string { return $this->termsConditions; }
    public function getCreatedBy(): int { return $this->createdBy; }
    public function getConvertedToSaleId(): ?int { return $this->convertedToSaleId; }
    public function getItems(): array { return $this->items; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'quotation_number' => $this->quotationNumber,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_email' => $this->customerEmail,
            'quotation_date' => $this->quotationDate->format('Y-m-d'),
            'expiry_date' => $this->expiryDate?->format('Y-m-d'),
            'status' => $this->status,
            'subtotal' => $this->subtotal->toFloat(),
            'discount' => $this->discount->toFloat(),
            'tax' => $this->tax->toFloat(),
            'total' => $this->total->toFloat(),
            'notes' => $this->notes,
            'terms_conditions' => $this->termsConditions,
            'created_by' => $this->createdBy,
            'converted_to_sale_id' => $this->convertedToSaleId,
            'items' => array_map(fn(QuotationItem $i) => $i->toArray(), $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
