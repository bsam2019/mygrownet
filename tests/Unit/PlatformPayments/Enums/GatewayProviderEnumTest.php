<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\Enums\GatewayProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GatewayProviderEnumTest extends TestCase
{
    #[Test]
    public function it_has_all_gateway_cases(): void
    {
        $cases = GatewayProvider::cases();
        $values = array_map(fn($c) => $c->value, $cases);

        $this->assertContains('pawapay', $values);
        $this->assertContains('flutterwave', $values);
        $this->assertContains('dpo', $values);
        $this->assertContains('mtn_momo', $values);
        $this->assertContains('airtel_money', $values);
        $this->assertContains('money_unify', $values);
        $this->assertContains('zamtel_kwacha', $values);
        $this->assertCount(7, $cases);
    }

    #[Test]
    public function pawapay_has_correct_labels(): void
    {
        $gateway = GatewayProvider::PAWAPAY;
        $this->assertEquals('PawaPay', $gateway->getLabel());
        $this->assertStringContainsString('mobile money', strtolower($gateway->getDescription()));
        $this->assertEquals('https://pawapay.io', $gateway->getWebsite());
    }

    #[Test]
    public function flutterwave_has_correct_labels(): void
    {
        $gateway = GatewayProvider::FLUTTERWAVE;
        $this->assertEquals('Flutterwave', $gateway->getLabel());
        $this->assertStringContainsString('mobile money', strtolower($gateway->getDescription()));
        $this->assertEquals('https://flutterwave.com', $gateway->getWebsite());
    }

    #[Test]
    public function dpo_has_correct_labels(): void
    {
        $gateway = GatewayProvider::DPO;
        $this->assertEquals('DPO PayGate', $gateway->getLabel());
        $this->assertStringContainsString('mobile money', strtolower($gateway->getDescription()));
        $this->assertEquals('https://www.dpogroup.com', $gateway->getWebsite());
    }

    #[Test]
    public function mtn_momo_has_correct_labels(): void
    {
        $g = GatewayProvider::MTN_MOMO;
        $this->assertEquals('MTN Mobile Money', $g->getLabel());
        $this->assertStringContainsString('zambia', strtolower($g->getDescription()));
    }

    #[Test]
    public function airtel_money_has_correct_labels(): void
    {
        $g = GatewayProvider::AIRTEL_MONEY;
        $this->assertEquals('Airtel Money', $g->getLabel());
    }

    #[Test]
    public function money_unify_has_correct_labels(): void
    {
        $g = GatewayProvider::MONEY_UNIFY;
        $this->assertEquals('MoneyUnify', $g->getLabel());
    }

    #[Test]
    public function zamtel_kwacha_has_correct_labels(): void
    {
        $g = GatewayProvider::ZAMTEL_KWACHA;
        $this->assertEquals('Zamtel Kwacha', $g->getLabel());
    }

    #[Test]
    public function from_string_works(): void
    {
        $this->assertEquals(GatewayProvider::PAWAPAY, GatewayProvider::from('pawapay'));
        $this->assertEquals(GatewayProvider::FLUTTERWAVE, GatewayProvider::from('flutterwave'));
        $this->assertEquals(GatewayProvider::DPO, GatewayProvider::from('dpo'));
        $this->assertEquals(GatewayProvider::MTN_MOMO, GatewayProvider::from('mtn_momo'));
        $this->assertEquals(GatewayProvider::AIRTEL_MONEY, GatewayProvider::from('airtel_money'));
        $this->assertEquals(GatewayProvider::MONEY_UNIFY, GatewayProvider::from('money_unify'));
        $this->assertEquals(GatewayProvider::ZAMTEL_KWACHA, GatewayProvider::from('zamtel_kwacha'));
    }
}
