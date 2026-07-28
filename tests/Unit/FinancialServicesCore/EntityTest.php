<?php

namespace Tests\Unit\FinancialServicesCore;

use App\Domain\FinancialServicesCore\Entities\Currency;
use App\Domain\FinancialServicesCore\Entities\ExchangeRate;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    public function test_currency_create(): void
    {
        $c = Currency::create('ZMW', 'Zambian Kwacha', 'ZK', 2);

        $this->assertNull($c->id());
        $this->assertEquals('ZMW', $c->code());
        $this->assertEquals('Zambian Kwacha', $c->name());
        $this->assertEquals('ZK', $c->symbol());
        $this->assertEquals(2, $c->decimalPlaces());
        $this->assertTrue($c->isActive());
    }

    public function test_currency_disable(): void
    {
        $c = Currency::create('ZMW', 'Zambian Kwacha', 'ZK', 2);
        $c->disable();
        $this->assertFalse($c->isActive());
    }

    public function test_currency_enable(): void
    {
        $c = Currency::create('ZMW', 'Zambian Kwacha', 'ZK', 2);
        $c->disable();
        $c->enable();
        $this->assertTrue($c->isActive());
    }

    public function test_currency_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $c = Currency::reconstitute(
            id: 1,
            code: 'USD',
            name: 'US Dollar',
            symbol: '$',
            decimalPlaces: 2,
            isActive: true,
            createdAt: $now,
            updatedAt: null,
        );

        $this->assertEquals(1, $c->id());
        $this->assertEquals('USD', $c->code());
    }

    public function test_exchange_rate_create(): void
    {
        $now = new \DateTimeImmutable();
        $rate = ExchangeRate::create('ZMW', 'USD', 0.055, $now, 'bank-of-zambia');

        $this->assertNull($rate->id());
        $this->assertEquals('ZMW', $rate->fromCurrency());
        $this->assertEquals('USD', $rate->toCurrency());
        $this->assertEquals(0.055, $rate->rate());
        $this->assertEquals($now, $rate->date());
        $this->assertEquals('bank-of-zambia', $rate->source());
    }

    public function test_exchange_rate_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $rate = ExchangeRate::reconstitute(
            id: 10,
            fromCurrency: 'USD',
            toCurrency: 'ZMW',
            rate: 18.5,
            date: $now,
            source: 'exchangerate.host',
            createdAt: $now,
            updatedAt: null,
        );

        $this->assertEquals(10, $rate->id());
        $this->assertEquals(18.5, $rate->rate());
    }
}
