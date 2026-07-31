<?php

namespace Tests\Feature\PlatformPayments;

use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use App\Domain\Core\Events\PlatformEvent;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Events\PaymentFailed;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private TransactionRepositoryInterface $transactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactions = $this->app->make(TransactionRepositoryInterface::class);
    }

    public function test_pawapay_webhook_rejects_invalid_signature(): void
    {
        config()->set('services.pawapay.webhook_secret', 'whsec_test');

        $response = $this->postJson('/api/webhooks/payments/pawapay', [
            'depositId' => 'DEP-001',
            'status' => 'COMPLETED',
        ], ['X-Webhook-Signature' => 'wrong-signature']);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid signature']);
    }

    public function test_pawapay_webhook_updates_transaction_and_dispatches_completed_event(): void
    {
        config()->set('services.pawapay.webhook_secret', 'whsec_test');

        $tx = $this->transactions->save(PaymentTransaction::create(
            organizationId: 1,
            amount: 100.00,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::MTNMoMo,
            provider: 'pawapay',
            metadata: ['reference' => 'DEP-001'],
        ));

        $this->transactions->save(
            PaymentTransaction::reconstitute(
                id: $tx->id(),
                organizationId: $tx->organizationId(),
                amount: $tx->amount(),
                currency: $tx->currency(),
                paymentMethod: $tx->paymentMethod()->value,
                status: 'pending',
                providerTransactionId: 'DEP-001',
                providerReference: 'DEP-001',
                provider: 'pawapay',
                fee: null,
                settledAmount: null,
                settledAt: null,
                metadata: $tx->metadata(),
                failureReason: null,
                attemptCount: 0,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            ),
        );

        Event::fake();

        $payload = '{"depositId":"DEP-001","status":"COMPLETED"}';
        $signature = hash_hmac('sha256', $payload, 'whsec_test');

        $response = $this->postJson('/api/webhooks/payments/pawapay', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['received' => true]);

        $updated = $this->transactions->findById($tx->id());
        $this->assertEquals(TransactionStatus::Completed, $updated->status());

        Event::assertDispatched(PaymentCompleted::class);
    }

    public function test_pawapay_webhook_marks_failed_transaction(): void
    {
        config()->set('services.pawapay.webhook_secret', 'whsec_test');

        $tx = $this->transactions->save(PaymentTransaction::create(
            organizationId: 1,
            amount: 50.00,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::AirtelMoney,
            provider: 'pawapay',
            metadata: ['reference' => 'DEP-002'],
        ));

        $this->transactions->save(
            PaymentTransaction::reconstitute(
                id: $tx->id(),
                organizationId: $tx->organizationId(),
                amount: $tx->amount(),
                currency: $tx->currency(),
                paymentMethod: $tx->paymentMethod()->value,
                status: 'pending',
                providerTransactionId: 'DEP-002',
                providerReference: 'DEP-002',
                provider: 'pawapay',
                fee: null,
                settledAmount: null,
                settledAt: null,
                metadata: $tx->metadata(),
                failureReason: null,
                attemptCount: 0,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable(),
            ),
        );

        Event::fake();

        $payload = '{"depositId":"DEP-002","status":"FAILED"}';
        $signature = hash_hmac('sha256', $payload, 'whsec_test');

        $response = $this->postJson('/api/webhooks/payments/pawapay', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        $updated = $this->transactions->findById($tx->id());
        $this->assertEquals(TransactionStatus::Failed, $updated->status());

        Event::assertDispatched(PaymentFailed::class);
    }

    public function test_pawapay_webhook_missing_fields_returns_422(): void
    {
        config()->set('services.pawapay.webhook_secret', 'whsec_test');

        $payload = '{"status":"COMPLETED"}';
        $signature = hash_hmac('sha256', $payload, 'whsec_test');

        $response = $this->postJson('/api/webhooks/payments/pawapay', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertStatus(422);
    }

    public function test_pawapay_webhook_unknown_transaction_is_accepted(): void
    {
        config()->set('services.pawapay.webhook_secret', 'whsec_test');

        $payload = '{"depositId":"DEP-999","status":"COMPLETED"}';
        $signature = hash_hmac('sha256', $payload, 'whsec_test');

        $response = $this->postJson('/api/webhooks/payments/pawapay', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['received' => true]);
    }
}
