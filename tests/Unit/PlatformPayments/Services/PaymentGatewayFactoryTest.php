<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\Enums\GatewayProvider;
use App\Domain\PlatformPayments\Services\PaymentGatewayFactory;
use App\Domain\PlatformPayments\Gateways\PawapayGateway;
use App\Domain\PlatformPayments\Gateways\FlutterwaveGateway;
use App\Domain\PlatformPayments\Gateways\DpoGateway;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentGatewayFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_pawapay_gateway(): void
    {
        $gateway = PaymentGatewayFactory::create(
            GatewayProvider::PAWAPAY,
            ['api_token' => 'test-token'],
            false,
        );

        $this->assertInstanceOf(PawapayGateway::class, $gateway);
        $this->assertEquals('PawaPay', $gateway->getName());
    }

    #[Test]
    public function it_creates_flutterwave_gateway(): void
    {
        $gateway = PaymentGatewayFactory::create(
            GatewayProvider::FLUTTERWAVE,
            ['secret_key' => 'sk_test'],
            true,
        );

        $this->assertInstanceOf(FlutterwaveGateway::class, $gateway);
        $this->assertEquals('Flutterwave', $gateway->getName());
    }

    #[Test]
    public function it_creates_dpo_gateway(): void
    {
        $gateway = PaymentGatewayFactory::create(
            GatewayProvider::DPO,
            ['company_token' => 'tok', 'service_type' => '1234'],
            false,
        );

        $this->assertInstanceOf(DpoGateway::class, $gateway);
        $this->assertEquals('DPO PayGate', $gateway->getName());
    }

    #[Test]
    public function get_available_gateways_returns_all(): void
    {
        $gateways = PaymentGatewayFactory::getAvailableGateways();

        $this->assertCount(7, $gateways);
        $values = array_column($gateways, 'value');
        $this->assertContains('pawapay', $values);
        $this->assertContains('flutterwave', $values);
        $this->assertContains('dpo', $values);
        $this->assertContains('mtn_momo', $values);
        $this->assertContains('airtel_money', $values);
        $this->assertContains('money_unify', $values);
        $this->assertContains('zamtel_kwacha', $values);
    }

    #[Test]
    public function available_gateways_have_labels(): void
    {
        $gateways = PaymentGatewayFactory::getAvailableGateways();

        foreach ($gateways as $g) {
            $this->assertArrayHasKey('value', $g);
            $this->assertArrayHasKey('label', $g);
            $this->assertArrayHasKey('description', $g);
            $this->assertNotEmpty($g['label']);
        }
    }

    #[Test]
    public function get_gateway_fields_returns_requirements(): void
    {
        $fields = PaymentGatewayFactory::getGatewayFields(GatewayProvider::DPO);

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $names = array_column($fields, 'name');
        $this->assertContains('company_token', $names);
    }
}
