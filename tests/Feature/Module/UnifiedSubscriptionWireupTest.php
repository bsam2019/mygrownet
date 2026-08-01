<?php

namespace Tests\Feature\Module;

use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Models\QuickInvoice\UserSubscription;
use App\Models\User;
use Database\Seeders\QuickInvoiceSubscriptionTiersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that BizDocs and QuickInvoice are wired into the unified
 * module subscription checkout flow:
 *
 *   plans page (config/modules/*.php) -> subscriptions.checkout
 *   -> free tier activates immediately
 *   -> paid tier mirrors into QuickInvoice's legacy subscription table
 */
class UnifiedSubscriptionWireupTest extends TestCase
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

    // ── BizDocs ───────────────────────────────────────────────────────────

    public function test_bizdocs_plans_page_lists_tiers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscriptions/bizdocs/plans');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Payments/ModulePlans')
            ->where('module.id', 'bizdocs')
            ->has('tiers.free')
            ->has('tiers.starter')
            ->has('tiers.business')
            ->has('tiers.pro'));
    }

    public function test_bizdocs_free_tier_checkout_activates_immediately(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscriptions/bizdocs/checkout?tier=free&billing_cycle=monthly&return_url=/workspace');

        $response->assertRedirect('/workspace');

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'bizdocs');
        $this->assertNotNull($subscription);
        $this->assertEquals('active', $subscription->getStatus());
    }

    public function test_bizdocs_subscription_route_redirects_to_unified_plans(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/bizdocs/subscription');

        $response->assertRedirect(route('subscriptions.plans', ['module' => 'bizdocs']));
    }

    // ── QuickInvoice ──────────────────────────────────────────────────────

    public function test_quickinvoice_plans_page_lists_tiers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscriptions/quickinvoice/plans');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Payments/ModulePlans')
            ->where('module.id', 'quickinvoice')
            ->has('tiers.free')
            ->has('tiers.basic')
            ->has('tiers.pro')
            ->has('tiers.enterprise'));
    }

    public function test_quickinvoice_free_tier_checkout_activates_immediately(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/subscriptions/quickinvoice/checkout?tier=free&billing_cycle=monthly&return_url=/workspace');

        $response->assertRedirect('/workspace');

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'quickinvoice');
        $this->assertNotNull($subscription);
        $this->assertEquals('active', $subscription->getStatus());
    }

    public function test_quickinvoice_legacy_plans_route_redirects_to_unified(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/quick-invoice/subscription');

        $response->assertRedirect(route('subscriptions.plans', ['module' => 'quickinvoice']));
    }

    public function test_quickinvoice_paid_checkout_mirrors_into_legacy_subscription_table(): void
    {
        $this->seed(QuickInvoiceSubscriptionTiersSeeder::class);

        $user = User::factory()->create();

        // 1. Checkout creates a pending module subscription
        $checkout = $this->actingAs($user)->get('/subscriptions/quickinvoice/checkout?tier=basic&billing_cycle=monthly');
        $checkout->assertStatus(200);

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'quickinvoice');
        $this->assertNotNull($subscription);
        $this->assertEquals('pending', $subscription->getStatus());
        $reference = $subscription->getProviderReference();
        $this->assertNotNull($reference);

        // 2. Mark the payment transaction completed (as the webhook does)
        $tx = $this->transactions->save(PaymentTransaction::create(
            organizationId: $user->id,
            amount: 50.00,
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
                status: 'completed',
                providerTransactionId: 'DEP-700',
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

        // 3. Fire the same event the webhook dispatches
        event(new PaymentCompleted(
            transactionId: $tx->id(),
            organizationId: $user->id,
            amount: 50.00,
            currency: 'ZMW',
            providerTransactionId: 'DEP-700',
        ));

        // 4. Module subscription activated
        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'quickinvoice');
        $this->assertEquals('active', $subscription->getStatus());

        // 5. Legacy QuickInvoice subscription row mirrored with the Basic tier
        $legacy = UserSubscription::with('tier')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($legacy);
        $this->assertEquals('Basic', $legacy->tier->name);
        $this->assertEquals($reference, $legacy->payment_reference);
        $this->assertEquals('pawapay', $legacy->payment_method);
    }

    public function test_webhook_completion_for_non_quickinvoice_does_not_mirror(): void
    {
        $this->seed(QuickInvoiceSubscriptionTiersSeeder::class);

        $user = User::factory()->create();

        // BizBoost checkout → pending subscription
        $checkout = $this->actingAs($user)->get('/subscriptions/bizboost/checkout?tier=basic&billing_cycle=monthly');
        $checkout->assertStatus(200);

        $subscription = $this->subscriptions->findByUserAndModule($user->id, 'bizboost');
        $reference = $subscription->getProviderReference();

        $tx = $this->transactions->save(PaymentTransaction::create(
            organizationId: $user->id,
            amount: 79.00,
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
                status: 'completed',
                providerTransactionId: 'DEP-800',
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

        event(new PaymentCompleted(
            transactionId: $tx->id(),
            organizationId: $user->id,
            amount: 79.00,
            currency: 'ZMW',
            providerTransactionId: 'DEP-800',
        ));

        // BizBoost subscription activated but no QuickInvoice row created
        $bizboost = $this->subscriptions->findByUserAndModule($user->id, 'bizboost');
        $this->assertEquals('active', $bizboost->getStatus());

        $this->assertDatabaseMissing('quick_invoice_user_subscriptions', [
            'user_id' => $user->id,
        ]);
    }
}
