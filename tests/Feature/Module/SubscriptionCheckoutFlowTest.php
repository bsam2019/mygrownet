<?php

namespace Tests\Feature\Module;

use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * End-to-end test of the unified subscription checkout flow:
 *
 *   module plans page -> subscriptions.checkout (creates pending subscription)
 *   -> shared PawaPay initiate (stores provider reference)
 *   -> webhook COMPLETED (dispatches PaymentCompleted)
 *   -> ActivateSubscriptionOnPaymentCompleted listener activates the subscription
 */
class SubscriptionCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private ModuleSubscriptionRepositoryInterface $subscriptions;
    private TransactionRepositoryInterface $transactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriptions = $this->app->make(ModuleSubscriptionRepositoryInterface::class);
        $this->transactions = $this->app->make(TransactionRepositoryInterface::class);
    }

    public function test_plans_page_lists_module_tiers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscriptions/bizboost/plans');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Payments/ModulePlans')
            ->where('module.id', 'bizboost')
            ->has('tiers.basic')
            ->has('tiers.business'));
    }

    public function test_plans_page_404_for_module_without_tiers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscriptions/does-not-exist/plans');

        $response->assertStatus(404);
    }

    public function test_checkout_creates_pending_subscription(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscriptions/bizboost/checkout?tier=basic&billing_cycle=monthly');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Payments/SharedCheckout')
            ->where('amount', 79)
            ->where('reference', fn ($ref) => is_string($ref) && str_starts_with($ref, 'sub_' . $user->id . '_')));

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'bizboost');
        $this->assertNotNull($subscription);
        $this->assertEquals('pending', $subscription->getStatus());
        $this->assertEquals('basic', $subscription->getTier());
        $this->assertNotNull($subscription->getProviderReference());
    }

    public function test_checkout_free_tier_activates_immediately(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscriptions/bizboost/checkout?tier=free&billing_cycle=monthly&return_url=/workspace');

        $response->assertRedirect('/workspace');

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'bizboost');
        $this->assertNotNull($subscription);
        $this->assertEquals('active', $subscription->getStatus());
    }

    public function test_full_flow_checkout_initiate_webhook_activates_subscription(): void
    {
        config()->set('services.pawapay.api_token', 'test-token');
        config()->set('services.pawapay.webhook_secret', 'whsec_test');

        Http::fake([
            '*api.sandbox.pawapay.io/deposits' => Http::response([
                'depositId' => 'DEP-500',
                'status' => 'PENDING',
            ], 200),
        ]);

        $user = User::factory()->create();

        // 1. Checkout creates a pending subscription with a provider reference
        $checkout = $this->actingAs($user)->get('/subscriptions/bizboost/checkout?tier=basic&billing_cycle=monthly');
        $checkout->assertStatus(200);
        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'bizboost');
        $this->assertEquals('pending', $subscription->getStatus());
        $reference = $subscription->getProviderReference();
        $this->assertNotNull($reference);

        // 2. Initiate payment through the shared API using that reference
        $initiate = $this->postJson('/api/payments/shared/initiate', [
            'phone_number' => '260977123456',
            'amount' => 79.00,
            'currency' => 'ZMW',
            'gateway' => 'pawapay',
            'description' => 'BizBoost basic subscription',
            'reference' => $reference,
            'metadata' => [
                'subscription' => [
                    'module_id' => 'bizboost',
                    'tier' => 'basic',
                    'billing_cycle' => 'monthly',
                    'provider_reference' => $reference,
                ],
            ],
        ]);
        $initiate->assertStatus(200);
        $initiate->assertJsonPath('success', true);

        $tx = $this->transactions->findByReference($reference);
        $this->assertNotNull($tx);
        $this->assertEquals(TransactionStatus::Pending, $tx->status());
        $this->assertEquals($reference, $tx->providerReference());

        // 3. PawaPay webhook fires COMPLETED for that deposit
        $payload = '{"depositId":"DEP-500","status":"COMPLETED"}';
        $signature = hash_hmac('sha256', $payload, 'whsec_test');

        $webhook = $this->postJson('/api/webhooks/payments/pawapay', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);
        $webhook->assertStatus(200);

        // 4. Transaction completed and subscription activated
        $tx = $this->transactions->findById($tx->id());
        $this->assertEquals(TransactionStatus::Completed, $tx->status());
        $this->assertEquals('DEP-500', $tx->providerTransactionId());

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'bizboost');
        $this->assertEquals('active', $subscription->getStatus());
        $this->assertNotNull($subscription->getExpiresAt());
    }

    public function test_webhook_completion_activates_subscription_for_any_module(): void
    {
        config()->set('services.pawapay.webhook_secret', 'whsec_test');

        // GrowMart has no wallet flow — the shared checkout covers it
        $user = User::factory()->create();

        $checkout = $this->actingAs($user)->get('/subscriptions/growmart/checkout?tier=basic&billing_cycle=monthly');
        $checkout->assertStatus(200);

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'growmart');
        $this->assertNotNull($subscription);
        $this->assertEquals('pending', $subscription->getStatus());
        $reference = $subscription->getProviderReference();

        $tx = $this->transactions->save(PaymentTransaction::create(
            organizationId: 1,
            amount: 199.00,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::MTNMoMo,
            provider: 'pawapay',
            metadata: ['reference' => $reference],
        ));
        $this->transactions->save(
            PaymentTransaction::reconstitute(
                id: $tx->id(),
                organizationId: $tx->organizationId(),
                amount: $tx->amount(),
                currency: $tx->currency(),
                paymentMethod: $tx->paymentMethod()->value,
                status: 'pending',
                providerTransactionId: 'DEP-600',
                providerReference: $reference,
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

        $payload = '{"depositId":"DEP-600","status":"COMPLETED"}';
        $signature = hash_hmac('sha256', $payload, 'whsec_test');

        $webhook = $this->postJson('/api/webhooks/payments/pawapay', json_decode($payload, true), [
            'X-Webhook-Signature' => $signature,
        ]);
        $webhook->assertStatus(200);

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'growmart');
        $this->assertEquals('active', $subscription->getStatus());
    }
}
