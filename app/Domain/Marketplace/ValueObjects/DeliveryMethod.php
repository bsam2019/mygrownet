<?php

namespace App\Domain\Marketplace\ValueObjects;

use InvalidArgumentException;

final class DeliveryMethod
{
    private const SELF_DELIVERY = 'self';
    private const COURIER = 'courier';
    private const PICKUP = 'pickup';

    private const VALID_METHODS = [
        self::SELF_DELIVERY,
        self::COURIER,
        self::PICKUP,
    ];

    private function __construct(private readonly string $method)
    {
        if (!in_array($method, self::VALID_METHODS, true)) {
            throw new InvalidArgumentException("Invalid delivery method: {$method}");
        }
    }

    public static function selfDelivery(): self { return new self(self::SELF_DELIVERY); }
    public static function courier(): self { return new self(self::COURIER); }
    public static function pickup(): self { return new self(self::PICKUP); }

    public static function fromString(string $method): self
    {
        return new self($method);
    }

    public function value(): string { return $this->method; }
    public function isSelfDelivery(): bool { return $this->method === self::SELF_DELIVERY; }
    public function isCourier(): bool { return $this->method === self::COURIER; }
    public function isPickup(): bool { return $this->method === self::PICKUP; }

    public function label(): string
    {
        return match ($this->method) {
            self::SELF_DELIVERY => 'Seller Delivery',
            self::COURIER => 'Courier Service',
            self::PICKUP => 'Pickup Station',
        };
    }
}
