<?php

namespace Tests\Unit\PlatformPayments\Gateways;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Gateways\DpoGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DpoGatewayTest extends TestCase
{
    private DpoGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new DpoGateway(
            ['company_token' => 'tok-123', 'service_type' => '3854'],
            false,
        );
    }

    public function test_get_name(): void
    {
        $this->assertEquals('DPO PayGate', $this->gateway->getName());
    }

    public function test_initiate_payment_success(): void
    {
        $xmlResponse = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <Result>000</Result>
    <ResultExplanation>Transaction created successfully</ResultExplanation>
    <TransToken>TOKEN-001</TransToken>
</API3G>
XML;

        Http::fake([
            '*secure.3gdirectpay.com/API/v6/*' => Http::response($xmlResponse, 200),
        ]);

        $request = new PaymentRequest(
            amount: '100.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-001',
            description: 'Test payment',
            returnUrl: 'https://mysite.com/return',
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::PENDING, $response->status);
        $this->assertEquals('REF-001', $response->transactionReference);
        $this->assertEquals('TOKEN-001', $response->externalReference);
        $this->assertStringContainsString('TOKEN-001', $response->checkoutUrl);
    }

    public function test_initiate_payment_failure(): void
    {
        $xmlResponse = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <Result>001</Result>
    <ResultExplanation>Invalid company token</ResultExplanation>
</API3G>
XML;

        Http::fake([
            '*secure.3gdirectpay.com/API/v6/*' => Http::response($xmlResponse, 200),
        ]);

        $request = new PaymentRequest(
            amount: '100.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-002',
            description: 'Fail',
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_verify_payment_completed(): void
    {
        $xmlResponse = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <Result>000</Result>
    <TransToken>TOKEN-001</TransToken>
    <TransactionStatus>1</TransactionStatus>
</API3G>
XML;

        Http::fake([
            '*secure.3gdirectpay.com/API/v6/*' => Http::response($xmlResponse, 200),
        ]);

        $response = $this->gateway->verifyPayment('TOKEN-001');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);
    }

    public function test_verify_payment_failed(): void
    {
        $xmlResponse = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <Result>000</Result>
    <TransToken>TOKEN-001</TransToken>
    <TransactionStatus>2</TransactionStatus>
</API3G>
XML;

        Http::fake([
            '*secure.3gdirectpay.com/API/v6/*' => Http::response($xmlResponse, 200),
        ]);

        $response = $this->gateway->verifyPayment('TOKEN-001');

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_refund_success(): void
    {
        $xmlResponse = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <Result>000</Result>
    <TransToken>RFND-001</TransToken>
</API3G>
XML;

        Http::fake([
            '*secure.3gdirectpay.com/API/v6/*' => Http::response($xmlResponse, 200),
        ]);

        $request = new RefundRequest('TOKEN-001', '50.00', 'Refund');
        $response = $this->gateway->refundPayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals('RFND-001', $response->refundReference);
    }

    public function test_validate_configuration(): void
    {
        $valid = $this->gateway->validateConfiguration([
            'company_token' => 'tok', 'service_type' => '1234',
        ]);
        $this->assertTrue($valid['valid']);

        $invalid = $this->gateway->validateConfiguration([]);
        $this->assertFalse($invalid['valid']);
        $this->assertCount(2, $invalid['errors']);
    }

    public function test_supports_test_mode(): void
    {
        $this->assertFalse($this->gateway->supportsTestMode());
    }
}
