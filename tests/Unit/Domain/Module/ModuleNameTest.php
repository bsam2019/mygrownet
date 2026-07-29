<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\ModuleName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleNameTest extends TestCase
{
    #[Test]
    public function fromString_creates_vo()
    {
        $name = ModuleName::fromString('StockFlow');

        $this->assertEquals('StockFlow', $name->value());
    }

    #[Test]
    public function fromString_trims_whitespace()
    {
        $name = ModuleName::fromString('  GrowFinance  ');

        $this->assertEquals('GrowFinance', $name->value());
    }

    #[Test]
    public function fromString_throws_on_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module name cannot be empty');

        ModuleName::fromString('');
    }

    #[Test]
    public function fromString_throws_on_whitespace_only()
    {
        $this->expectException(\InvalidArgumentException::class);

        ModuleName::fromString('   ');
    }

    #[Test]
    public function fromString_throws_when_too_long()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module name cannot exceed 100 characters');

        ModuleName::fromString(str_repeat('a', 101));
    }

    #[Test]
    public function accepts_100_characters()
    {
        $name = ModuleName::fromString(str_repeat('a', 100));

        $this->assertEquals(100, strlen($name->value()));
    }

    #[Test]
    public function toString_returns_value()
    {
        $name = ModuleName::fromString('Test Module');

        $this->assertEquals('Test Module', (string) $name);
    }
}
