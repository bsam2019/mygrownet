<?php

namespace App\Domain\Marketplace\ValueObjects;

use InvalidArgumentException;

final class OrderStatus
{
    private const PENDING = 'pending';
    private const PAID = 'paid';
    private const PROCESSING = 'processing';
    private const SHIPPED = 'shipped';
    private const DELIVERED = 'delivered';
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';
    private const DISPUTED = 'disputed';
    private const REFUNDED = 'refunded';

    private const VALID_STATUSES = [
        self::PENDING,
        self::PAID,
        self::PROCESSING,
        self::SHIPPED,
        self::DELIVERED,
        self::COMPLETED,
        self::CANCELLED,
        self::DISPUTED,
        self::REFUNDED,
    ];

    private function __construct(private readonly string $status)
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid order status: {$status}");
        }
    }

    public static function pending(): self { return new self(self::PENDING); }
    public static function paid(): self { return new self(self::PAID); }
    public static function processing(): self { return new self(self::PROCESSING); }
    public static function shipped(): self { return new self(self::SHIPPED); }
    public static function delivered(): self { return new self(self::DELIVERED); }
    public static function completed(): self { return new self(self::COMPLETED); }
    public static function cancelled(): self { return new self(self::CANCELLED); }
    public static function disputed(): self { return new self(self::DISPUTED); }
    public static function refunded(): self { return new self(self::REFUNDED); }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public function value(): string { return $this->status; }
    public function isPending(): bool { return $this->status === self::PENDING; }
    public function isPaid(): bool { return $this->status === self::PAID; }
    public function isProcessing(): bool { return $this->status === self::PROCESSING; }
    public function isShipped(): bool { return $this->status === self::SHIPPED; }
    public function isDelivered(): bool { return $this->status === self::DELIVERED; }
    public function isCompleted(): bool { return $this->status === self::COMPLETED; }
    public function isCancelled(): bool { return $this->status === self::CANCELLED; }
    public function isDisputed(): bool { return $this->status === self::DISPUTED; }
    public function isRefunded(): bool { return $this->status === self::REFUNDED; }

    public function label(): string
    {
        return match ($this->status) {
            self::PENDING => 'Pending Payment',
            self::PAID => 'Paid',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::DISPUTED => 'Disputed',
            self::REFUNDED => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this->status) {
            self::PENDING => 'yellow',
            self::PAID => 'blue',
            self::PROCESSING => 'indigo',
            self::SHIPPED => 'purple',
            self::DELIVERED => 'teal',
            self::COMPLETED => 'green',
            self::CANCELLED => 'gray',
            self::DISPUTED => 'red',
            self::REFUNDED => 'orange',
        };
    }
}
