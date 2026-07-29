<?php

namespace Tests\Unit\Domain\Notification\ValueObjects;

use App\Domain\Notification\ValueObjects\NotificationType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationTypeTest extends TestCase
{
    public function test_from_string(): void
    {
        $type = NotificationType::fromString('wallet.topup');
        $this->assertEquals('wallet.topup', $type->value());
    }

    public function test_category_extracts_prefix(): void
    {
        $type = NotificationType::fromString('wallet.topup');
        $this->assertEquals('wallet', $type->category());
    }

    public function test_category_with_multiple_dots(): void
    {
        $type = NotificationType::fromString('commissions.bonus.monthly');
        $this->assertEquals('commissions', $type->category());
    }

    public function test_empty_string_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        NotificationType::fromString('');
    }

    public function test_missing_dot_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        NotificationType::fromString('invalid');
    }

    public function test_equals(): void
    {
        $a = NotificationType::fromString('wallet.topup');
        $b = NotificationType::fromString('wallet.topup');
        $c = NotificationType::fromString('wallet.withdrawal');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_to_string(): void
    {
        $type = NotificationType::fromString('subscription.expiring');
        $this->assertEquals('subscription.expiring', (string) $type);
    }
}
