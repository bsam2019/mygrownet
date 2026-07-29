<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\Contracts\PaymentGatewayInterface;
use App\Domain\PlatformPayments\Gateways\PawapayGateway;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AbstractPaymentGatewayTest extends TestCase
{
    private PawapayGateway $gateway;

    protected function setUp(): void
    {
        $this->gateway = new PawapayGateway(
            ['api_token' => 'test-token'],
            true,
        );
    }

    #[Test]
    public function it_implements_the_interface(): void
    {
        $this->assertInstanceOf(PaymentGatewayInterface::class, $this->gateway);
    }

    #[Test]
    public function it_detects_sandbox_mode(): void
    {
        $sandbox = new PawapayGateway(['api_token' => 'tok'], true);
        $production = new PawapayGateway(['api_token' => 'tok'], false);

        $sandboxRef = (new \ReflectionClass($sandbox))->getProperty('baseUrl');
        $sandboxRef->setAccessible(true);
        $prodRef = (new \ReflectionClass($production))->getProperty('baseUrl');
        $prodRef->setAccessible(true);

        $this->assertStringContainsString('sandbox', $sandboxRef->getValue($sandbox));
        $this->assertStringNotContainsString('sandbox', $prodRef->getValue($production));
    }

    #[Test]
    public function pawapay_supports_test_mode(): void
    {
        $this->assertTrue($this->gateway->supportsTestMode());
    }
}
