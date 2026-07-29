<?php

namespace Tests\Unit\PlatformPayments\Gateways;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Gateways\MoneyUnifyGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MoneyUnifyGatewayTest extends TestCase
{
    private MoneyUnifyGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MoneyUnifyGateway(
            ['muid' => 'MUID-001'],
            true,
        );
    }

    public function test_get_name(): void
    {
        $this->assertEquals('MoneyUnify', $this->gateway->getName());
    }

    public function test_initiate_payment_success(): void
    {
        Http::fake([
            '*api.sandbox.moneyunify.com/v2/collections' => Http::response([
                'transaction_id' => 'TXN-001',
                'checkout_url' => 'https://checkout.moneyunify.com/pay',
            ], 200),
        ]);

        $request = new PaymentRequest(
            amount: '50.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-001',
            description: 'Test',
            callbackUrl: 'https://site.com/cb',
            metadata: ['order' => 1],
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::PENDING, $response->status);
        $this->assertEquals('TXN-001', $response->externalReference);
        $this->assertEquals('https://checkout.moneyunify.com/pay', $response->checkoutUrl);
    }

    public function test_initiate_payment_failure(): void
    {
        Http::fake([
            '*api.sandbox.moneyunify.com/v2/collections' => Http::response([
                'message' => 'Invalid MUID',
            ], 400),
        ]);

        $request = new PaymentRequest(
            amount: '50.00',
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
        Http::fake([
            'api.sandbox.moneyunify.com/v2/collections/REF-001' => Http::response([
                'status' => 'completed',
                'transaction_id' => 'TXN-001',
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);
    }

    public function test_refund_success(): void
    {
        Http::fake([
            '*api.sandbox.moneyunify.com/v2/refunds' => Http::response([
                'refund_id' => 'RFND-001',
            ], 200),
        ]);

        $request = new RefundRequest('TXN-001', '50.00', 'Refund');
        $response = $this->gateway->refundPayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals('RFND-001', $response->refundReference);
    }

    public function test_validate_configuration(): void
    {
        $valid = $this->gateway->validateConfiguration(['muid' => 'MUID']);
        $this->assertTrue($valid['valid']);

        $invalid = $this->gateway->validateConfiguration([]);
        $this->assertFalse($invalid['valid']);
    }
}
