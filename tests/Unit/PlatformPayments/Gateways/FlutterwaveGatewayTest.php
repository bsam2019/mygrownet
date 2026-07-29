<?php

namespace Tests\Unit\PlatformPayments\Gateways;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Gateways\FlutterwaveGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlutterwaveGatewayTest extends TestCase
{
    private FlutterwaveGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new FlutterwaveGateway(
            ['public_key' => 'pk_test', 'secret_key' => 'sk_test'],
            true,
        );
    }

    public function test_get_name(): void
    {
        $this->assertEquals('Flutterwave', $this->gateway->getName());
    }

    public function test_initiate_payment_success(): void
    {
        Http::fake([
            '*api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'data' => [
                    'link' => 'https://checkout.flw.com/pay',
                    'id' => 12345,
                ],
            ], 200),
        ]);

        $request = new PaymentRequest(
            amount: '100.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-001',
            description: 'Test',
            customerName: 'John',
            customerEmail: 'john@test.com',
            returnUrl: 'https://mysite.com/return',
            metadata: ['order_id' => 1],
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::PENDING, $response->status);
        $this->assertEquals('https://checkout.flw.com/pay', $response->checkoutUrl);
        $this->assertEquals(12345, $response->externalReference);
    }

    public function test_initiate_payment_failure(): void
    {
        Http::fake([
            '*api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'error',
                'message' => 'Invalid amount',
            ], 400),
        ]);

        $request = new PaymentRequest(
            amount: '0',
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
            '*api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 12345,
                    'status' => 'successful',
                ],
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);
        $this->assertEquals(12345, $response->externalReference);
    }

    public function test_verify_payment_failed(): void
    {
        Http::fake([
            '*api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'failed',
                ],
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_refund_success(): void
    {
        Http::fake([
            '*api.flutterwave.com/v3/transactions/123/refund' => Http::response([
                'status' => 'success',
                'data' => ['id' => 'RFND-001'],
            ], 200),
        ]);

        $request = new RefundRequest('123', '50.00', 'Refund');
        $response = $this->gateway->refundPayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals('RFND-001', $response->refundReference);
    }

    public function test_validate_configuration(): void
    {
        $valid = $this->gateway->validateConfiguration([
            'public_key' => 'pk', 'secret_key' => 'sk',
        ]);
        $this->assertTrue($valid['valid']);

        $invalid = $this->gateway->validateConfiguration([]);
        $this->assertFalse($invalid['valid']);
        $this->assertCount(2, $invalid['errors']);
    }

    public function test_required_fields(): void
    {
        $names = array_column($this->gateway->getRequiredFields(), 'name');
        $this->assertContains('public_key', $names);
        $this->assertContains('secret_key', $names);
    }
}
