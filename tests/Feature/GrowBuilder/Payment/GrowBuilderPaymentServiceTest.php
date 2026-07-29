<?php

namespace Tests\Feature\GrowBuilder\Payment;

use App\Domain\GrowBuilder\Payment\Services\GrowBuilderPaymentService;
use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\GrowBuilder\SitePaymentConfig;
use App\Models\GrowBuilder\SitePaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GrowBuilderPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private GrowBuilderPaymentService $service;
    private int $siteId;
    private int $configId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GrowBuilderPaymentService::class);

        $user = User::factory()->create();
        $site = GrowBuilderSite::create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'subdomain' => 'shop-' . uniqid(),
            'status' => 'published',
        ]);
        $this->siteId = $site->id;

        $config = SitePaymentConfig::create([
            'site_id' => $this->siteId,
            'gateway' => 'pawapay',
            'credentials' => ['api_token' => 'test-token'],
            'is_active' => true,
            'test_mode' => true,
        ]);
        $this->configId = $config->id;
    }

    public function test_initiate_payment_success(): void
    {
        Http::fake([
            'api.sandbox.pawapay.io/deposits' => Http::response([
                'depositId' => 'DEP-001',
            ], 200),
        ]);

        $request = new PaymentRequest(
            amount: '50.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-001',
            description: 'Test order',
            customerName: 'John',
            customerEmail: 'john@test.com',
            metadata: ['order_id' => 1],
            callbackUrl: 'https://site.com/webhook',
        );

        $response = $this->service->initiatePayment($this->siteId, $request);

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::PENDING, $response->status);

        $this->assertDatabaseHas('growbuilder_site_payment_transactions', [
            'site_id' => $this->siteId,
            'payment_config_id' => $this->configId,
            'transaction_reference' => 'REF-001',
            'status' => 'pending',
        ]);
    }

    public function test_initiate_payment_without_active_config(): void
    {
        SitePaymentConfig::where('id', $this->configId)->update(['is_active' => false]);

        $request = new PaymentRequest(
            amount: '50.00',
            currency: 'ZMW',
            phoneNumber: '260977123456',
            reference: 'REF-002',
            description: 'Test',
        );

        $response = $this->service->initiatePayment($this->siteId, $request);

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);
    }

    public function test_verify_payment_completed(): void
    {
        SitePaymentTransaction::create([
            'site_id' => $this->siteId,
            'payment_config_id' => $this->configId,
            'transaction_reference' => 'REF-003',
            'amount' => '50.00',
            'currency' => 'ZMW',
            'phone_number' => '260977123456',
            'description' => 'Test payment',
            'status' => 'pending',
        ]);

        Http::fake([
            'api.sandbox.pawapay.io/deposits/REF-003' => Http::response([
                'depositId' => 'DEP-003',
                'status' => 'COMPLETED',
            ], 200),
        ]);

        $response = $this->service->verifyPayment($this->siteId, 'REF-003');

        $this->assertTrue($response->success);
        $this->assertEquals(PaymentStatus::COMPLETED, $response->status);

        $this->assertDatabaseHas('growbuilder_site_payment_transactions', [
            'transaction_reference' => 'REF-003',
            'status' => 'completed',
        ]);
    }

    public function test_verify_payment_failed(): void
    {
        SitePaymentTransaction::create([
            'site_id' => $this->siteId,
            'payment_config_id' => $this->configId,
            'transaction_reference' => 'REF-004',
            'amount' => '50.00',
            'currency' => 'ZMW',
            'phone_number' => '260977123456',
            'description' => 'Test payment',
            'status' => 'pending',
        ]);

        Http::fake([
            'api.sandbox.pawapay.io/deposits/REF-004' => Http::response([
                'depositId' => 'DEP-004',
                'status' => 'FAILED',
            ], 200),
        ]);

        $response = $this->service->verifyPayment($this->siteId, 'REF-004');

        $this->assertFalse($response->success);
        $this->assertEquals(PaymentStatus::FAILED, $response->status);

        $this->assertDatabaseHas('growbuilder_site_payment_transactions', [
            'transaction_reference' => 'REF-004',
            'status' => 'failed',
        ]);
    }

    public function test_refund_payment_via_pawapay(): void
    {
        SitePaymentTransaction::create([
            'site_id' => $this->siteId,
            'payment_config_id' => $this->configId,
            'transaction_reference' => 'REF-005',
            'amount' => '50.00',
            'currency' => 'ZMW',
            'phone_number' => '260977123456',
            'description' => 'Test payment',
            'status' => 'completed',
        ]);

        Http::fake([
            'api.sandbox.pawapay.io/refunds' => Http::response([
                'refundId' => 'RFND-001',
            ], 200),
        ]);

        $request = new RefundRequest(
            transactionReference: 'REF-005',
            amount: '50.00',
            reason: 'Customer request',
        );

        $response = $this->service->refundPayment($this->siteId, $request);

        $this->assertTrue($response->success);
        $this->assertEquals('RFND-001', $response->refundReference);

        $this->assertDatabaseHas('growbuilder_site_payment_transactions', [
            'transaction_reference' => 'REF-005',
            'status' => 'refunded',
        ]);
    }

    public function test_handle_webhook_success(): void
    {
        SitePaymentTransaction::create([
            'site_id' => $this->siteId,
            'payment_config_id' => $this->configId,
            'transaction_reference' => 'REF-006',
            'amount' => '50.00',
            'currency' => 'ZMW',
            'phone_number' => '260977123456',
            'description' => 'Test payment',
            'status' => 'pending',
        ]);

        Http::fake([
            'api.sandbox.pawapay.io/deposits/REF-006' => Http::response([
                'depositId' => 'DEP-006',
                'status' => 'COMPLETED',
            ], 200),
        ]);

        $payload = ['depositId' => 'REF-006'];
        $result = $this->service->handleWebhook($this->siteId, $payload);

        $this->assertTrue($result);
    }

    public function test_handle_webhook_without_active_config(): void
    {
        SitePaymentConfig::where('id', $this->configId)->update(['is_active' => false]);

        $result = $this->service->handleWebhook($this->siteId, []);

        $this->assertFalse($result);
    }
}
