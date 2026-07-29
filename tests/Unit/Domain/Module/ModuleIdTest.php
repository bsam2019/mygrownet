<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\ModuleId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleIdTest extends TestCase
{
    #[Test]
    public function fromString_creates_vo()
    {
        $id = ModuleId::fromString('stockflow');

        $this->assertEquals('stockflow', $id->value());
    }

    #[Test]
    public function fromString_throws_on_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module ID cannot be empty');

        ModuleId::fromString('');
    }

    #[Test]
    public function equals_returns_true_for_same_value()
    {
        $a = ModuleId::fromString('stockflow');
        $b = ModuleId::fromString('stockflow');

        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function equals_returns_false_for_different_value()
    {
        $a = ModuleId::fromString('stockflow');
        $b = ModuleId::fromString('growfinance');

        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function toString_returns_value()
    {
        $id = ModuleId::fromString('test-module');

        $this->assertEquals('test-module', (string) $id);
    }
}
