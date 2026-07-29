<?php

namespace Tests\Unit\Domain\Notification\ValueObjects;

use App\Domain\Notification\ValueObjects\TelegramChatId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TelegramChatIdTest extends TestCase
{
    public function test_from_string(): void
    {
        $chatId = TelegramChatId::fromString('-1001234567890');
        $this->assertEquals('-1001234567890', $chatId->value());
    }

    public function test_empty_string_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TelegramChatId::fromString('');
    }

    public function test_equals(): void
    {
        $a = TelegramChatId::fromString('12345');
        $b = TelegramChatId::fromString('12345');
        $c = TelegramChatId::fromString('67890');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_to_string(): void
    {
        $chatId = TelegramChatId::fromString('-1001234567890');
        $this->assertEquals('-1001234567890', (string) $chatId);
    }
}
