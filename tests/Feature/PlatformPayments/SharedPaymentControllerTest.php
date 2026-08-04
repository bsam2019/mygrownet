<?php

namespace Tests\Feature\PlatformPayments;

use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SharedPaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private TransactionRepositoryInterface $transactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactions = $this->app->make(TransactionRepositoryInterface::class);
    }

    public function test_gateways_lists_pawapay(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/payments/shared/gateways');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'gateways' => [['value', 'label', 'description']],
            'default',
        ]);
        $response->assertJsonPath('default', 'pawapay');
        $this->assertContains('pawapay', array_column($response->json('gateways'), 'value'));
    }

    public function test_initiate_creates_transaction_and_calls_gateway(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/deposits' => Http::response([
                'depositId' => '523a7165-d0b6-4986-bd19-1a9a4ec84afc',
                'status' => 'ACCEPTED',
                'created' => '2026-08-04T00:46:13Z',
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payments/shared/initiate', [
            'phone_number' => '260977123456',
            'amount' => 100.00,
            'currency' => 'ZMW',
            'gateway' => 'pawapay',
            'description' => 'Test payment',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'transaction' => ['id', 'reference', 'status', 'amount', 'currency'],
        ]);

        $txId = $response->json('transaction.id');
        $this->assertNotNull($txId);

        $tx = $this->transactions->findById((int) $txId);
        $this->assertNotNull($tx);
        $this->assertEquals(TransactionStatus::Pending, $tx->status());
        $this->assertEquals('pawapay', $tx->provider());
        $this->assertEquals(100.00, $tx->amount());
    }

    public function test_initiate_marks_transaction_failed_on_gateway_error(): void
    {
        Http::fake([
            '*api.sandbox.pawapay.io/v2/deposits' => Http::response([
                'message' => 'Invalid amount',
            ], 400),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payments/shared/initiate', [
            'phone_number' => '260977123456',
            'amount' => 50.00,
            'currency' => 'ZMW',
        ]);

        $response->assertStatus(200);

        $txId = $response->json('transaction.id');
        $tx = $this->transactions->findById((int) $txId);
        $this->assertEquals(TransactionStatus::Failed, $tx->status());
    }

    public function test_initiate_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payments/shared/initiate', [
            'amount' => 100.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone_number', 'currency']);
    }

    public function test_status_returns_transaction(): void
    {
        $tx = $this->transactions->save(PaymentTransaction::create(
            organizationId: 1,
            amount: 100.00,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::MTNMoMo,
            provider: 'pawapay',
            metadata: [],
        ));

        $this->transactions->save(
            PaymentTransaction::reconstitute(
                id: $tx->id(),
                organizationId: $tx->organizationId(),
                amount: $tx->amount(),
                currency: $tx->currency(),
                paymentMethod: $tx->paymentMethod()->value,
                status: 'pending',
                providerTransactionId: 'DEP-200',
                providerReference: 'DEP-200',
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

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/payments/shared/status/DEP-200');

        $response->assertStatus(200);
        $response->assertJsonPath('transaction.status', 'pending');
        $response->assertJsonPath('transaction.amount', 100);
    }

    public function test_status_returns_404_for_unknown_transaction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/payments/shared/status/DEP-999');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Transaction not found']);
    }

    public function test_refund_requires_completed_transaction(): void
    {
        $tx = $this->transactions->save(PaymentTransaction::create(
            organizationId: 1,
            amount: 100.00,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::MTNMoMo,
            provider: 'pawapay',
            metadata: [],
        ));

        $this->transactions->save(
            PaymentTransaction::reconstitute(
                id: $tx->id(),
                organizationId: $tx->organizationId(),
                amount: $tx->amount(),
                currency: $tx->currency(),
                paymentMethod: $tx->paymentMethod()->value,
                status: 'pending',
                providerTransactionId: 'DEP-300',
                providerReference: 'DEP-300',
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

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payments/shared/refund', [
            'reference' => 'DEP-300',
            'amount' => 50.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Payment processing failed: Only completed transactions can be refunded');
    }
}
