<?php

namespace Tests\Unit\PlatformPayments\Gateways;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Gateways\ZamtelKwachaGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZamtelKwachaGatewayTest extends TestCase
{
    private ZamtelKwachaGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new ZamtelKwachaGateway(
            ['username' => 'user', 'password' => 'pass'],
            false,
        );
    }

    public function test_get_name(): void
    {
        $this->assertEquals('Zamtel Kwacha', $this->gateway->getName());
    }

    public function test_initiate_payment_success(): void
    {
        Http::fake([
            '*api.zamtel.co.zm/auth/token' => Http::response([
                'access_token' => 'tok',
            ], 200),
            '*api.zamtel.co.zm/payments/collect' => Http::response([
                'transaction_id' => 'TXN-001',
            ], 200),
        ]);

        $request = new PaymentRequest(
            amount: '50.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-001',
            description: 'Test',
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::PENDING, $response->status);
        $this->assertEquals('TXN-001', $response->externalReference);
    }

    public function test_initiate_payment_failure(): void
    {
        Http::fake([
            '*api.zamtel.co.zm/auth/token' => Http::response([
                'access_token' => 'tok',
            ], 200),
            '*api.zamtel.co.zm/payments/collect' => Http::response([
                'message' => 'Payment failed',
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
            '*api.zamtel.co.zm/auth/token' => Http::response([
                'access_token' => 'tok',
            ], 200),
            '*api.zamtel.co.zm/payments/REF-001' => Http::response([
                'status' => 'completed',
                'transaction_id' => 'TXN-001',
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);
    }

    public function test_refund_not_supported(): void
    {
        $request = new RefundRequest('TXN-001', '50.00', 'Manual');
        $response = $this->gateway->refundPayment($request);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('manual', strtolower($response->message));
    }

    public function test_validate_configuration(): void
    {
        $valid = $this->gateway->validateConfiguration([
            'username' => 'u', 'password' => 'p',
        ]);
        $this->assertTrue($valid['valid']);

        $invalid = $this->gateway->validateConfiguration([]);
        $this->assertFalse($invalid['valid']);
        $this->assertCount(2, $invalid['errors']);
    }
}
