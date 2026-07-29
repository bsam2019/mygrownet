<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\ModuleSlug;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleSlugTest extends TestCase
{
    #[Test]
    public function fromString_creates_vo()
    {
        $slug = ModuleSlug::fromString('stockflow');

        $this->assertEquals('stockflow', $slug->value());
    }

    #[Test]
    public function fromString_lowercases_input()
    {
        $slug = ModuleSlug::fromString('StockFlow-Pro');

        $this->assertEquals('stockflow-pro', $slug->value());
    }

    #[Test]
    public function fromString_trims_whitespace()
    {
        $slug = ModuleSlug::fromString('  grow-finance  ');

        $this->assertEquals('grow-finance', $slug->value());
    }

    #[Test]
    public function fromString_throws_on_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module slug cannot be empty');

        ModuleSlug::fromString('');
    }

    #[Test]
    public function fromString_throws_on_invalid_characters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module slug must contain only lowercase letters, numbers, and hyphens');

        ModuleSlug::fromString('invalid slug!');
    }

    #[Test]
    public function fromString_lowercases_uppercase_input()
    {
        $slug = ModuleSlug::fromString('UPPERCASE');

        $this->assertEquals('uppercase', $slug->value());
    }

    #[Test]
    public function fromString_accepts_numbers()
    {
        $slug = ModuleSlug::fromString('v2-module');

        $this->assertEquals('v2-module', $slug->value());
    }

    #[Test]
    public function toString_returns_value()
    {
        $slug = ModuleSlug::fromString('my-module');

        $this->assertEquals('my-module', (string) $slug);
    }
}
