<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Entities;

use App\Domain\GrowBuilder\ValueObjects\Money;
use App\Domain\GrowBuilder\ValueObjects\OrderId;
use App\Domain\GrowBuilder\ValueObjects\OrderStatus;
use DateTimeImmutable;

class Order
{
    private function __construct(
        private ?OrderId $id,
        private int $siteId,
        private string $orderNumber,
        private string $customerName,
        private string $customerPhone,
        private ?string $customerEmail,
        private ?string $customerAddress,
        private ?string $customerCity,
        private array $items,
        private Money $subtotal,
        private Money $shippingCost,
        private Money $discountAmount,
        private ?string $discountCode,
        private Money $total,
        private OrderStatus $status,
        private ?string $paymentMethod,
        private ?string $paymentReference,
        private ?string $notes,
        private ?string $adminNotes,
        private ?DateTimeImmutable $paidAt,
        private ?DateTimeImmutable $shippedAt,
        private ?DateTimeImmutable $deliveredAt,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $siteId,
        string $customerName,
        string $customerPhone,
        array $items,
        int $subtotalInNgwee,
        int $shippingCostInNgwee = 0,
        ?string $customerEmail = null,
        ?string $customerAddress = null,
    ): self {
        $now = new DateTimeImmutable();
        $subtotal = Money::fromNgwee($subtotalInNgwee);
        $shipping = Money::fromNgwee($shippingCostInNgwee);
        $total = Money::fromNgwee($subtotalInNgwee + $shippingCostInNgwee);

        return new self(
            id: null,
            siteId: $siteId,
            orderNumber: self::generateOrderNumber(),
            customerName: $customerName,
            customerPhone: $customerPhone,
            customerEmail: $customerEmail,
            customerAddress: $customerAddress,
            customerCity: null,
            items: $items,
            subtotal: $subtotal,
            shippingCost: $shipping,
            discountAmount: Money::zero(),
            discountCode: null,
            total: $total,
            status: OrderStatus::pending(),
            paymentMethod: null,
            paymentReference: null,
            notes: null,
            adminNotes: null,
            paidAt: null,
            shippedAt: null,
            deliveredAt: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public static function reconstitute(
        OrderId $id,
        int $siteId,
        string $orderNumber,
        string $customerName,
        string $customerPhone,
        ?string $customerEmail,
        ?string $customerAddress,
        ?string $customerCity,
        array $items,
        Money $subtotal,
        Money $shippingCost,
        Money $discountAmount,
        ?string $discountCode,
        Money $total,
        OrderStatus $status,
        ?string $paymentMethod,
        ?string $paymentReference,
        ?string $notes,
        ?string $adminNotes,
        ?DateTimeImmutable $paidAt,
        ?DateTimeImmutable $shippedAt,
        ?DateTimeImmutable $deliveredAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id, $siteId, $orderNumber, $customerName, $customerPhone,
            $customerEmail, $customerAddress, $customerCity, $items,
            $subtotal, $shippingCost, $discountAmount, $discountCode,
            $total, $status, $paymentMethod, $paymentReference, $notes,
            $adminNotes, $paidAt, $shippedAt, $deliveredAt, $createdAt, $updatedAt
        );
    }

    public function applyDiscount(string $code, int $amountInNgwee): void
    {
        $this->discountCode = $code;
        $this->discountAmount = Money::fromNgwee($amountInNgwee);
        $this->recalculateTotal();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setPaymentMethod(string $method): void
    {
        $this->paymentMethod = $method;
        $this->status = OrderStatus::paymentPending();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsPaid(string $reference): void
    {
        $this->paymentReference = $reference;
        $this->status = OrderStatus::paid();
        $this->paidAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsProcessing(): void
    {
        $this->status = OrderStatus::processing();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsShipped(): void
    {
        $this->status = OrderStatus::shipped();
        $this->shippedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsDelivered(): void
    {
        $this->status = OrderStatus::delivered();
        $this->deliveredAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsCompleted(): void
    {
        $this->status = OrderStatus::completed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = OrderStatus::cancelled();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function refund(): void
    {
        $this->status = OrderStatus::refunded();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addNote(string $note): void
    {
        $this->notes = $note;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addAdminNote(string $note): void
    {
        $this->adminNotes = $note;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status->value(), ['pending', 'payment_pending', 'paid']);
    }

    public function isPaid(): bool
    {
        return $this->paidAt !== null;
    }

    private function recalculateTotal(): void
    {
        $total = $this->subtotal->getAmountInNgwee() 
               + $this->shippingCost->getAmountInNgwee() 
               - $this->discountAmount->getAmountInNgwee();
        $this->total = Money::fromNgwee(max(0, $total));
    }

    private static function generateOrderNumber(): string
    {
        return 'GB-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    // Getters
    public function getId(): ?OrderId { return $this->id; }
    public function getSiteId(): int { return $this->siteId; }
    public function getOrderNumber(): string { return $this->orderNumber; }
    public function getCustomerName(): string { return $this->customerName; }
    public function getCustomerPhone(): string { return $this->customerPhone; }
    public function getCustomerEmail(): ?string { return $this->customerEmail; }
    public function getCustomerAddress(): ?string { return $this->customerAddress; }
    public function getCustomerCity(): ?string { return $this->customerCity; }
    public function getItems(): array { return $this->items; }
    public function getSubtotal(): Money { return $this->subtotal; }
    public function getShippingCost(): Money { return $this->shippingCost; }
    public function getDiscountAmount(): Money { return $this->discountAmount; }
    public function getDiscountCode(): ?string { return $this->discountCode; }
    public function getTotal(): Money { return $this->total; }
    public function getStatus(): OrderStatus { return $this->status; }
    public function getPaymentMethod(): ?string { return $this->paymentMethod; }
    public function getPaymentReference(): ?string { return $this->paymentReference; }
    public function getNotes(): ?string { return $this->notes; }
    public function getAdminNotes(): ?string { return $this->adminNotes; }
    public function getPaidAt(): ?DateTimeImmutable { return $this->paidAt; }
    public function getShippedAt(): ?DateTimeImmutable { return $this->shippedAt; }
    public function getDeliveredAt(): ?DateTimeImmutable { return $this->deliveredAt; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function getItemCount(): int
    {
        return array_sum(array_column($this->items, 'quantity'));
    }
}
