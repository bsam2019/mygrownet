<?php

namespace Tests\Unit\Domain\Notification\ValueObjects;

use App\Domain\Notification\ValueObjects\TelegramLinkCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TelegramLinkCodeTest extends TestCase
{
    public function test_from_string(): void
    {
        $code = TelegramLinkCode::fromString('ABCD1234');
        $this->assertEquals('ABCD1234', $code->value());
    }

    public function test_from_string_converts_to_uppercase(): void
    {
        $code = TelegramLinkCode::fromString('abcd1234');
        $this->assertEquals('ABCD1234', $code->value());
    }

    public function test_generate(): void
    {
        $code = TelegramLinkCode::generate();
        $this->assertEquals(8, strlen($code->value()));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $code->value());
    }

    public function test_generate_produces_different_codes(): void
    {
        $a = TelegramLinkCode::generate();
        $b = TelegramLinkCode::generate();
        $this->assertNotEquals($a->value(), $b->value());
    }

    public function test_invalid_format_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TelegramLinkCode::fromString('short');
    }

    public function test_invalid_characters_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TelegramLinkCode::fromString('abcd-efg');
    }

    public function test_to_string(): void
    {
        $code = TelegramLinkCode::fromString('XYZ98765');
        $this->assertEquals('XYZ98765', (string) $code);
    }
}
