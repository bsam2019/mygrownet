<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\ValueObjects\DeliveryMethod;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeliveryMethodTest extends TestCase
{
    #[Test]
    public function creates_via_named_constructors(): void
    {
        $this->assertTrue(DeliveryMethod::selfDelivery()->isSelfDelivery());
        $this->assertTrue(DeliveryMethod::courier()->isCourier());
        $this->assertTrue(DeliveryMethod::pickup()->isPickup());
    }

    #[Test]
    public function creates_from_string(): void
    {
        $this->assertTrue(DeliveryMethod::fromString('courier')->isCourier());
    }

    #[Test]
    public function labels_are_correct(): void
    {
        $this->assertEquals('Seller Delivery', DeliveryMethod::selfDelivery()->label());
        $this->assertEquals('Courier Service', DeliveryMethod::courier()->label());
        $this->assertEquals('Pickup Station', DeliveryMethod::pickup()->label());
    }

    #[Test]
    public function value_returns_raw_string(): void
    {
        $this->assertEquals('self', DeliveryMethod::selfDelivery()->value());
    }

    #[Test]
    public function rejects_invalid_method(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DeliveryMethod::fromString('helicopter');
    }
}
