<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class OrderStatus
{
    private const PENDING = 'pending';
    private const PAYMENT_PENDING = 'payment_pending';
    private const PAID = 'paid';
    private const PROCESSING = 'processing';
    private const SHIPPED = 'shipped';
    private const DELIVERED = 'delivered';
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';
    private const REFUNDED = 'refunded';

    private const LABELS = [
        self::PENDING => 'Pending',
        self::PAYMENT_PENDING => 'Awaiting Payment',
        self::PAID => 'Paid',
        self::PROCESSING => 'Processing',
        self::SHIPPED => 'Shipped',
        self::DELIVERED => 'Delivered',
        self::COMPLETED => 'Completed',
        self::CANCELLED => 'Cancelled',
        self::REFUNDED => 'Refunded',
    ];

    private const COLORS = [
        self::PENDING => 'yellow',
        self::PAYMENT_PENDING => 'orange',
        self::PAID => 'blue',
        self::PROCESSING => 'indigo',
        self::SHIPPED => 'purple',
        self::DELIVERED => 'green',
        self::COMPLETED => 'green',
        self::CANCELLED => 'red',
        self::REFUNDED => 'gray',
    ];

    private function __construct(private string $value) {}

    public static function pending(): self { return new self(self::PENDING); }
    public static function paymentPending(): self { return new self(self::PAYMENT_PENDING); }
    public static function paid(): self { return new self(self::PAID); }
    public static function processing(): self { return new self(self::PROCESSING); }
    public static function shipped(): self { return new self(self::SHIPPED); }
    public static function delivered(): self { return new self(self::DELIVERED); }
    public static function completed(): self { return new self(self::COMPLETED); }
    public static function cancelled(): self { return new self(self::CANCELLED); }
    public static function refunded(): self { return new self(self::REFUNDED); }

    public static function fromString(string $value): self
    {
        if (!array_key_exists($value, self::LABELS)) {
            throw new \InvalidArgumentException("Invalid order status: {$value}");
        }
        return new self($value);
    }

    public static function all(): array
    {
        return array_keys(self::LABELS);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return self::LABELS[$this->value];
    }

    public function color(): string
    {
        return self::COLORS[$this->value];
    }

    public function isPending(): bool { return $this->value === self::PENDING; }
    public function isPaymentPending(): bool { return $this->value === self::PAYMENT_PENDING; }
    public function isPaid(): bool { return $this->value === self::PAID; }
    public function isProcessing(): bool { return $this->value === self::PROCESSING; }
    public function isShipped(): bool { return $this->value === self::SHIPPED; }
    public function isDelivered(): bool { return $this->value === self::DELIVERED; }
    public function isCompleted(): bool { return $this->value === self::COMPLETED; }
    public function isCancelled(): bool { return $this->value === self::CANCELLED; }
    public function isRefunded(): bool { return $this->value === self::REFUNDED; }

    public function canTransitionTo(self $newStatus): bool
    {
        $transitions = [
            self::PENDING => [self::PAYMENT_PENDING, self::CANCELLED],
            self::PAYMENT_PENDING => [self::PAID, self::CANCELLED],
            self::PAID => [self::PROCESSING, self::CANCELLED, self::REFUNDED],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::DELIVERED],
            self::DELIVERED => [self::COMPLETED, self::REFUNDED],
            self::COMPLETED => [self::REFUNDED],
            self::CANCELLED => [],
            self::REFUNDED => [],
        ];

        return in_array($newStatus->value, $transitions[$this->value] ?? []);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
