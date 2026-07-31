<?php

namespace Tests\Unit\PlatformPayments\Gateways;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Gateways\PawapayGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PawapayGatewayTest extends TestCase
{
    private PawapayGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new PawapayGateway(
            ['api_token' => 'test-token'],
            true,
        );
    }

    public function test_get_name(): void
    {
        $this->assertEquals('PawaPay', $this->gateway->getName());
    }

    public function test_initiate_payment_success(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/deposits' => Http::response([
                'depositId' => 'DEP-001',
                'status' => 'PENDING',
            ], 200),
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
        $this->assertEquals('REF-001', $response->transactionReference);
        $this->assertEquals('DEP-001', $response->externalReference);
    }

    public function test_initiate_payment_failure(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/deposits' => Http::response([
                'message' => 'Invalid amount',
            ], 400),
        ]);

        $request = new PaymentRequest(
            amount: '-1',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-002',
            description: 'Failed payment',
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_verify_payment_completed(): void
    {
        Http::fake([
            'api.sandbox.pawapay.io/deposits/REF-001' => Http::response([
                'depositId' => 'DEP-001',
                'status' => 'COMPLETED',
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);
    }

    public function test_verify_payment_failed(): void
    {
        Http::fake([
            'api.sandbox.pawapay.io/deposits/REF-001' => Http::response([
                'depositId' => 'DEP-001',
                'status' => 'FAILED',
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('REF-001');

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_verify_payment_not_found(): void
    {
        Http::fake([
            'api.sandbox.pawapay.io/deposits/REF-999' => Http::response(null, 404),
        ]);

        $response = $this->gateway->verifyPayment('REF-999');

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_refund_success(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/refunds' => Http::response([
                'refundId' => 'RFND-001',
            ], 200),
        ]);

        $request = new RefundRequest(
            transactionReference: 'DEP-001',
            amount: '50.00',
            reason: 'Customer request',
        );

        $response = $this->gateway->refundPayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals('RFND-001', $response->refundReference);
    }

    public function test_refund_failure(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/refunds' => Http::response([
                'message' => 'Deposit not found',
            ], 404),
        ]);

        $request = new RefundRequest(
            transactionReference: 'DEP-999',
            amount: '50.00',
            reason: 'Refund',
        );

        $response = $this->gateway->refundPayment($request);

        $this->assertFalse($response->success);
        $this->assertEquals('', $response->refundReference);
    }

    public function test_validate_configuration_valid(): void
    {
        $result = $this->gateway->validateConfiguration(['api_token' => 'tok']);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validate_configuration_missing_token(): void
    {
        $result = $this->gateway->validateConfiguration([]);

        $this->assertFalse($result['valid']);
        $this->assertContains('API token is required', $result['errors']);
    }

    public function test_required_fields(): void
    {
        $fields = $this->gateway->getRequiredFields();

        $this->assertNotEmpty($fields);
        $names = array_column($fields, 'name');
        $this->assertContains('api_token', $names);
    }

    public function test_supports_test_mode(): void
    {
        $this->assertTrue($this->gateway->supportsTestMode());
    }

    public function test_verify_webhook_signature_valid_hex(): void
    {
        $gateway = new PawapayGateway(
            ['api_token' => 'test-token', 'webhook_secret' => 'whsec_test'],
            true,
        );

        $payload = '{"depositId":"DEP-001","status":"COMPLETED"}';
        $signature = hash_hmac('sha256', $payload, 'whsec_test');

        $this->assertTrue($gateway->verifyWebhookSignature($payload, $signature));
    }

    public function test_verify_webhook_signature_valid_base64(): void
    {
        $gateway = new PawapayGateway(
            ['api_token' => 'test-token', 'webhook_secret' => 'whsec_test'],
            true,
        );

        $payload = '{"depositId":"DEP-001","status":"COMPLETED"}';
        $signature = base64_encode(hash_hmac('sha256', $payload, 'whsec_test', true));

        $this->assertTrue($gateway->verifyWebhookSignature($payload, $signature));
    }

    public function test_verify_webhook_signature_invalid(): void
    {
        $gateway = new PawapayGateway(
            ['api_token' => 'test-token', 'webhook_secret' => 'whsec_test'],
            true,
        );

        $payload = '{"depositId":"DEP-001","status":"COMPLETED"}';

        $this->assertFalse($gateway->verifyWebhookSignature($payload, 'wrong-signature'));
        $this->assertFalse($gateway->verifyWebhookSignature($payload, ''));
    }

    public function test_verify_webhook_signature_skipped_without_secret(): void
    {
        $gateway = new PawapayGateway(['api_token' => 'test-token'], true);

        $this->assertTrue($gateway->verifyWebhookSignature('{}', ''));
    }

    public function test_map_status_mappings(): void
    {
        $this->assertEquals(PaymentStatus::COMPLETED, $this->gateway->mapStatus('COMPLETED'));
        $this->assertEquals(PaymentStatus::COMPLETED, $this->gateway->mapStatus('accepted'));
        $this->assertEquals(PaymentStatus::FAILED, $this->gateway->mapStatus('REJECTED'));
        $this->assertEquals(PaymentStatus::CANCELLED, $this->gateway->mapStatus('CANCELLED'));
        $this->assertEquals(PaymentStatus::EXPIRED, $this->gateway->mapStatus('EXPIRED'));
        $this->assertEquals(PaymentStatus::PENDING, $this->gateway->mapStatus('SUBMITTED'));
        $this->assertEquals(PaymentStatus::PROCESSING, $this->gateway->mapStatus('UNKNOWN'));
    }
}
