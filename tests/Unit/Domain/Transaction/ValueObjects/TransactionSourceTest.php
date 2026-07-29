<?php

namespace Tests\Unit\Domain\Transaction\ValueObjects;

use App\Domain\Transaction\ValueObjects\TransactionSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransactionSourceTest extends TestCase
{
    private function createViaReflection(string $source): TransactionSource
    {
        $reflection = new \ReflectionClass(TransactionSource::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        $prop = $reflection->getProperty('source');
        $prop->setAccessible(true);
        $prop->setValue($instance, $source);
        return $instance;
    }

    #[Test]
    public function value_returns_constructor_value(): void
    {
        $sut = $this->createViaReflection('growmart');
        $this->assertSame('growmart', $sut->value());
    }

    #[Test]
    public function equals_returns_true_for_same_source(): void
    {
        $a = $this->createViaReflection('growfinance');
        $b = $this->createViaReflection('growfinance');
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function equals_returns_false_for_different_source(): void
    {
        $a = $this->createViaReflection('growmart');
        $b = $this->createViaReflection('growfinance');
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function to_string_returns_source(): void
    {
        $sut = $this->createViaReflection('stockflow');
        $this->assertSame('stockflow', (string) $sut);
    }

    #[Test]
    public function isEarningsSource_returns_true_for_lgr(): void
    {
        $sut = $this->createViaReflection('lgr');
        $this->assertTrue($sut->isEarningsSource());
    }

    #[Test]
    public function isEarningsSource_returns_true_for_commissions(): void
    {
        $sut = $this->createViaReflection('commissions');
        $this->assertTrue($sut->isEarningsSource());
    }

    #[Test]
    public function isEarningsSource_returns_true_for_profit_share(): void
    {
        $sut = $this->createViaReflection('profit_share');
        $this->assertTrue($sut->isEarningsSource());
    }

    #[Test]
    public function isEarningsSource_returns_false_for_non_earnings(): void
    {
        $sut = $this->createViaReflection('growmart');
        $this->assertFalse($sut->isEarningsSource());
    }
}
