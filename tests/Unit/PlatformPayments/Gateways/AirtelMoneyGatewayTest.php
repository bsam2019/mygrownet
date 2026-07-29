<?php

namespace Tests\Unit\PlatformPayments\Gateways;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Gateways\AirtelMoneyGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AirtelMoneyGatewayTest extends TestCase
{
    private AirtelMoneyGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new AirtelMoneyGateway(
            ['client_id' => 'cid', 'client_secret' => 'secret'],
            true,
        );
    }

    public function test_get_name(): void
    {
        $this->assertEquals('Airtel Money', $this->gateway->getName());
    }

    public function test_initiate_payment_success(): void
    {
        Http::fake([
            '*openapiuat.airtel.africa/auth/oauth2/token' => Http::response([
                'access_token' => 'tok',
            ], 200),
            '*openapiuat.airtel.africa/merchant/v1/payments/' => Http::response([
                'data' => [
                    'transaction' => ['id' => 'TXN-001'],
                ],
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
    }

    public function test_initiate_payment_failure(): void
    {
        Http::fake([
            '*openapiuat.airtel.africa/auth/oauth2/token' => Http::response([
                'access_token' => 'tok',
            ], 200),
            '*openapiuat.airtel.africa/merchant/v1/payments/' => Http::response([
                'message' => 'Error',
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
            '*openapiuat.airtel.africa/auth/oauth2/token' => Http::response([
                'access_token' => 'tok',
            ], 200),
            '*openapiuat.airtel.africa/standard/v1/payments/REF-001' => Http::response([
                'data' => [
                    'transaction' => ['status' => 'TS', 'airtel_money_id' => 'AM-001'],
                ],
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);
        $this->assertEquals('AM-001', $response->externalReference);
    }

    public function test_verify_payment_failed(): void
    {
        Http::fake([
            '*openapiuat.airtel.africa/auth/oauth2/token' => Http::response([
                'access_token' => 'tok',
            ], 200),
            '*openapiuat.airtel.africa/standard/v1/payments/REF-001' => Http::response([
                'data' => [
                    'transaction' => ['status' => 'TF'],
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
            '*openapiuat.airtel.africa/auth/oauth2/token' => Http::response([
                'access_token' => 'tok',
            ], 200),
            '*openapiuat.airtel.africa/standard/v1/payments/refund' => Http::response([
                'data' => [
                    'transaction' => ['id' => 'RFND-001'],
                ],
            ], 200),
        ]);

        $request = new RefundRequest('TXN-001', '50.00', 'Refund');
        $response = $this->gateway->refundPayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals('RFND-001', $response->refundReference);
    }

    public function test_validate_configuration(): void
    {
        $valid = $this->gateway->validateConfiguration([
            'client_id' => 'cid', 'client_secret' => 'secret',
        ]);
        $this->assertTrue($valid['valid']);

        $invalid = $this->gateway->validateConfiguration([]);
        $this->assertFalse($invalid['valid']);
        $this->assertCount(2, $invalid['errors']);
    }

    public function test_required_fields(): void
    {
        $names = array_column($this->gateway->getRequiredFields(), 'name');
        $this->assertContains('client_id', $names);
        $this->assertContains('client_secret', $names);
    }
}
