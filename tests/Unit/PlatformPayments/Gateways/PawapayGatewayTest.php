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

    public function test_initiate_payment_accepted_uses_uuid_and_v2_payload(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/deposits' => Http::response([
                'depositId' => '523a7165-d0b6-4986-bd19-1a9a4ec84afc',
                'status' => 'ACCEPTED',
                'created' => '2026-08-04T00:46:13Z',
            ], 200),
        ]);

        $request = new PaymentRequest(
            amount: '50.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: '42', // non-UUID -> gateway must generate a UUID depositId
            description: 'Test payment',
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::PENDING, $response->status);

        // depositId must be a UUIDv4 (V2 requirement)
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $response->transactionReference
        );

        // Assert the request body was built per V2 (MMO payer + accountDetails)
        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);

            return is_array($body)
                && isset($body['depositId'])
                && $body['payer']['type'] === 'MMO'
                && isset($body['payer']['accountDetails']['provider'])
                && isset($body['payer']['accountDetails']['phoneNumber'])
                && $body['amount'] === '50'
                && $body['currency'] === 'ZMW';
        });
    }

    public function test_initiate_payment_rejected(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/deposits' => Http::response([
                'depositId' => '523a7165-d0b6-4986-bd19-1a9a4ec84afc',
                'status' => 'REJECTED',
                'failureReason' => [
                    'failureCode' => 'INVALID_CURRENCY',
                    'failureMessage' => 'The currency USD is not supported',
                ],
            ], 200),
        ]);

        $request = new PaymentRequest(
            amount: '50',
            currency: 'USD',
            phoneNumber: '260977123456',
            reference: '42',
            description: 'Rejected',
        );

        $response = $this->gateway->initiatePayment($request);

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
        $this->assertStringContainsString('INVALID_CURRENCY', $response->message);
    }

    public function test_verify_payment_completed_reads_found_envelope(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/deposits/523a7165-d0b6-4986-bd19-1a9a4ec84afc' => Http::response([
                'status' => 'FOUND',
                'data' => [
                    'depositId' => '523a7165-d0b6-4986-bd19-1a9a4ec84afc',
                    'status' => 'COMPLETED',
                    'amount' => '50.00',
                    'currency' => 'ZMW',
                ],
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('523a7165-d0b6-4986-bd19-1a9a4ec84afc');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);
    }

    public function test_verify_payment_failed_reads_found_envelope(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/deposits/523a7165-d0b6-4986-bd19-1a9a4ec84afc' => Http::response([
                'status' => 'FOUND',
                'data' => [
                    'depositId' => '523a7165-d0b6-4986-bd19-1a9a4ec84afc',
                    'status' => 'FAILED',
                    'failureReason' => ['failureCode' => 'PAYMENT_NOT_APPROVED'],
                ],
            ], 200),
        ]);

        $response = $this->gateway->verifyPayment('523a7165-d0b6-4986-bd19-1a9a4ec84afc');

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_verify_payment_not_found_is_pending(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/deposits/523a7165-d0b6-4986-bd19-1a9a4ec84afc' => Http::response(null, 404),
        ]);

        $response = $this->gateway->verifyPayment('523a7165-d0b6-4986-bd19-1a9a4ec84afc');

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::PENDING, $response->status);
    }

    public function test_refund_success(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/refunds' => Http::response([
                'refundId' => '723a7165-d0b6-4986-bd19-1a9a4ec84afc',
                'status' => 'ACCEPTED',
            ], 200),
        ]);

        $request = new RefundRequest(
            transactionReference: '523a7165-d0b6-4986-bd19-1a9a4ec84afc',
            amount: '50.00',
            reason: 'Customer request',
        );

        $response = $this->gateway->refundPayment($request);

        $this->assertTrue($response->success);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $response->refundReference
        );
    }

    public function test_refund_failure(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/refunds' => Http::response([
                'refundId' => '723a7165-d0b6-4986-bd19-1a9a4ec84afc',
                'status' => 'REJECTED',
                'failureReason' => ['failureCode' => 'INVALID_PARAMETER'],
            ], 200),
        ]);

        $request = new RefundRequest(
            transactionReference: '523a7165-d0b6-4986-bd19-1a9a4ec84afc',
            amount: '50.00',
            reason: 'Refund',
        );

        $response = $this->gateway->refundPayment($request);

        $this->assertFalse($response->success);
        $this->assertStringContainsString('INVALID_PARAMETER', $response->message);
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

    public function test_verify_webhook_signature_accepted_when_no_signature_headers(): void
    {
        // Signed callbacks disabled on the account -> no Signature headers -> accept.
        $this->assertTrue($this->gateway->verifyWebhookSignature('{}'));
    }

    public function test_verify_webhook_signature_rejected_when_partial_headers(): void
    {
        // Signature present but no Signature-Input -> cannot verify -> reject.
        $this->assertFalse($this->gateway->verifyWebhookSignature(
            '{}',
            signatureHeader: 'sig-pp=:AAAA:',
            signatureInputHeader: '',
        ));
    }

    public function test_map_status_v2_deposit_statuses(): void
    {
        $this->assertEquals(PaymentStatus::COMPLETED, $this->gateway->mapStatus('COMPLETED'));
        $this->assertEquals(PaymentStatus::PROCESSING, $this->gateway->mapStatus('ACCEPTED'));
        $this->assertEquals(PaymentStatus::PROCESSING, $this->gateway->mapStatus('PROCESSING'));
        $this->assertEquals(PaymentStatus::PROCESSING, $this->gateway->mapStatus('IN_RECONCILIATION'));
        $this->assertEquals(PaymentStatus::FAILED, $this->gateway->mapStatus('FAILED'));
        $this->assertEquals(PaymentStatus::CANCELLED, $this->gateway->mapStatus('CANCELLED'));
        $this->assertEquals(PaymentStatus::EXPIRED, $this->gateway->mapStatus('EXPIRED'));
        $this->assertEquals(PaymentStatus::PROCESSING, $this->gateway->mapStatus('UNKNOWN'));
    }

    public function test_amount_formatting(): void
    {
        $reflection = new \ReflectionClass($this->gateway);
        $method = $reflection->getMethod('formatAmount');
        $method->setAccessible(true);

        $this->assertEquals('15', $method->invoke($this->gateway, '15'));
        $this->assertEquals('15', $method->invoke($this->gateway, '15.00'));
        $this->assertEquals('10.5', $method->invoke($this->gateway, '10.50'));
        $this->assertEquals('0.5', $method->invoke($this->gateway, '0.50'));
        $this->assertEquals('123', $method->invoke($this->gateway, '0123'));
    }
}
