<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\Enums\PaymentStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentStatusEnumTest extends TestCase
{
    #[Test]
    public function it_has_all_status_values(): void
    {
        $this->assertEquals('pending', PaymentStatus::PENDING->value);
        $this->assertEquals('processing', PaymentStatus::PROCESSING->value);
        $this->assertEquals('completed', PaymentStatus::COMPLETED->value);
        $this->assertEquals('failed', PaymentStatus::FAILED->value);
        $this->assertEquals('cancelled', PaymentStatus::CANCELLED->value);
        $this->assertEquals('refunded', PaymentStatus::REFUNDED->value);
        $this->assertEquals('expired', PaymentStatus::EXPIRED->value);
    }

    #[Test]
    public function it_has_seven_cases(): void
    {
        $this->assertCount(7, PaymentStatus::cases());
    }

    #[Test]
    public function from_string_works(): void
    {
        $this->assertEquals(PaymentStatus::PENDING, PaymentStatus::from('pending'));
        $this->assertEquals(PaymentStatus::COMPLETED, PaymentStatus::from('completed'));
        $this->assertEquals(PaymentStatus::FAILED, PaymentStatus::from('failed'));
        $this->assertEquals(PaymentStatus::REFUNDED, PaymentStatus::from('refunded'));
    }

    #[Test]
    public function try_from_returns_null_for_unknown(): void
    {
        $this->assertNull(PaymentStatus::tryFrom('unknown'));
    }
}
