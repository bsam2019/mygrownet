<?php

namespace Tests\Unit\PlatformPayments\Gateways;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Gateways\MtnMomoGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MtnMomoGatewayTest extends TestCase
{
    private MtnMomoGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MtnMomoGateway(
            [
                'subscription_key' => 'sub-key',
                'user_id' => 'user-1',
                'api_key' => 'api-key',
            ],
            true,
        );
    }

    public function test_get_name(): void
    {
        $this->assertEquals('MTN Mobile Money', $this->gateway->getName());
    }

    public function test_initiate_payment_success(): void
    {
        Http::fake([
            '*sandbox.momodeveloper.mtn.com/collection/token/*' => Http::response([
                'access_token' => 'test-token',
            ], 200),
            '*sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay' => Http::response(null, 202),
        ]);

        $request = new PaymentRequest(
            amount: '50.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-001',
            description: 'Test payment',
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::PENDING, $response->status);
        $this->assertEquals('REF-001', $response->externalReference);
    }

    public function test_initiate_payment_failure(): void
    {
        Http::fake([
            '*sandbox.momodeveloper.mtn.com/collection/token/*' => Http::response([
                'access_token' => 'test-token',
            ], 200),
            '*sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay' => Http::response([
                'message' => 'Invalid payer',
            ], 400),
        ]);

        $request = new PaymentRequest(
            amount: '50.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-002',
            description: 'Failed',
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_verify_payment_completed(): void
    {
        Http::fake([
            '*sandbox.momodeveloper.mtn.com/collection/token/*' => Http::response([
                'access_token' => 'test-token',
            ], 200),
            '*sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/REF-001' => Http::response([
                'status' => 'SUCCESSFUL',
                'financialTransactionId' => 'FT-001',
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);
        $this->assertEquals('FT-001', $response->externalReference);
    }

    public function test_verify_payment_failed(): void
    {
        Http::fake([
            '*sandbox.momodeveloper.mtn.com/collection/token/*' => Http::response([
                'access_token' => 'test-token',
            ], 200),
            '*sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/REF-001' => Http::response([
                'status' => 'FAILED',
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_refund_not_supported(): void
    {
        $request = new RefundRequest('TXN-001', '50.00', 'Manual refund');
        $response = $this->gateway->refundPayment($request);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('manual', strtolower($response->message));
    }

    public function test_validate_configuration_valid(): void
    {
        $result = $this->gateway->validateConfiguration([
            'subscription_key' => 'key',
            'user_id' => 'uid',
            'api_key' => 'apik',
        ]);

        $this->assertTrue($result['valid']);
    }

    public function test_validate_configuration_missing_fields(): void
    {
        $result = $this->gateway->validateConfiguration([]);

        $this->assertFalse($result['valid']);
        $this->assertCount(3, $result['errors']);
    }

    public function test_required_fields(): void
    {
        $fields = $this->gateway->getRequiredFields();
        $names = array_column($fields, 'name');

        $this->assertContains('subscription_key', $names);
        $this->assertContains('user_id', $names);
        $this->assertContains('api_key', $names);
    }

    public function test_supports_test_mode(): void
    {
        $this->assertTrue($this->gateway->supportsTestMode());
    }
}
