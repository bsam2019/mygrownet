<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Services\SubscriptionBillingService;
use App\Services\MobileMoneyService;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test configuration
        config([
            'mygrownet.subscription.max_failed_attempts' => 3,
            'mygrownet.mobile_money.mtn' => [
                'subscription_key' => 'test-key',
                'user_id' => 'test-user',
                'api_key' => 'test-api-key',
                'environment' => 'sandbox',
                'token_url' => 'https://sandbox.momodeveloper.mtn.com/disbursement/token/',
                'disbursement_url' => 'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer'
            ]
        ]);
    }

    public function test_complete_subscription_creation_and_billing_cycle()
    {
        // Arrange
        Queue::fake();
        Notification::fake();

        $goldTier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        $user = User::factory()->create([
            'balance' => 500.00,
            'phone_number' => '+260977123456',
            'name' => 'John Subscriber'
        ]);

        $billingService = app(SubscriptionBillingService::class);

        // Act 1: Create subscription
        $subscription = $billingService->createSubscription($user, $goldTier);

        // Assert 1: Subscription created correctly
        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals('active', $subscription->status);
        $this->assertEquals($user->id, $subscription->user_id);
        $this->assertEquals($goldTier->id, $subscription->tier_id);
        $this->assertNotNull($subscription->started_at);
        $this->assertNotNull($subscription->next_billing_date);

        // Act 2: Simulate billing cycle (set billing date to past)
        $subscription->update(['next_billing_date' => now()->subDay()]);
        
        $billingResult = $billingService->processSubscriptionPayment($subscription);

        // Assert 2: Billing processed successfully
        $this->assertTrue($billingResult['success']);
        $this->assertEquals(200.00, $billingResult['amount']);
        $this->assertEquals('balance', $billingResult['payment_method']);

        // Verify user balance deducted
        $this->assertEquals(300.00, $user->fresh()->balance);

        // Verify subscription extended
        $subscription->refresh();
        $this->assertEquals(0, $subscription->failed_payment_attempts);
        $this->assertNotNull($subscription->last_payment_at);
        $this->assertEquals(200.00, $subscription->last_payment_amount);

        // Verify payment transaction created
        $paymentTransaction = PaymentTransaction::where('user_id', $user->id)
            ->where('type', 'subscription_payment')
            ->first();
        
        $this->assertNotNull($paymentTransaction);
        $this->assertEquals('completed', $paymentTransaction->status);
        $this->assertEquals(200.00, $paymentTransaction->amount);
    }

    public function test_subscription_upgrade_with_prorated_billing()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'monthly_fee' => 100.00
        ]);

        $goldTier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        $user = User::factory()->create([
            'balance' => 500.00,
            'current_investment_tier_id' => $silverTier->id
        ]);

        $billingService = app(SubscriptionBillingService::class);
        
        // Create subscription mid-cycle
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $silverTier->id,
            'status' => 'active',
            'started_at' => now()->subDays(15),
            'next_billing_date' => now()->addDays(15) // Mid-cycle
        ]);

        $initialBalance = $user->balance;

        // Act: Upgrade subscription
        $upgradeResult = $billingService->upgradeSubscription($subscription, $goldTier);

        // Assert: Upgrade successful
        $this->assertTrue($upgradeResult);

        // Verify subscription updated
        $subscription->refresh();
        $this->assertEquals($goldTier->id, $subscription->tier_id);
        $this->assertNotNull($subscription->upgraded_at);
        $this->assertEquals('user_requested', $subscription->upgrade_reason);

        // Verify user tier updated
        $user->refresh();
        $this->assertEquals($goldTier->id, $user->current_investment_tier_id);
        $this->assertEquals(200.00, $user->monthly_subscription_fee);

        // Verify prorated amount was charged
        $this->assertLessThan($initialBalance, $user->balance);
        $chargedAmount = $initialBalance - $user->balance;
        $this->assertGreaterThan(0, $chargedAmount);
        $this->assertLessThan(100.00, $chargedAmount); // Should be less than full difference
    }

    public function test_subscription_downgrade_after_failed_payments()
    {
        // Arrange
        $goldTier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        $silverTier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'monthly_fee' => 100.00
        ]);

        $user = User::factory()->create([
            'balance' => 50.00, // Insufficient for Gold tier
            'phone_number' => '+260977123456'
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $goldTier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay(),
            'failed_payment_attempts' => 3 // At maximum attempts
        ]);

        // Mock failed mobile money payment
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'test-token'
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([
                'code' => '400',
                'message' => 'Insufficient funds'
            ], 400)
        ]);

        $billingService = app(SubscriptionBillingService::class);

        // Act: Process billing (should fail and downgrade)
        $billingResult = $billingService->processSubscriptionPayment($subscription);

        // Assert: Payment failed but user was downgraded
        $this->assertFalse($billingResult['success']);
        $this->assertTrue($billingResult['downgraded']);
        $this->assertStringContainsString('Silver', $billingResult['error']);

        // Verify subscription downgraded
        $subscription->refresh();
        $this->assertEquals($silverTier->id, $subscription->tier_id);
        $this->assertNotNull($subscription->downgraded_at);
        $this->assertEquals('failed_payments', $subscription->downgrade_reason);
        $this->assertEquals(0, $subscription->failed_payment_attempts); // Reset after downgrade

        // Verify user tier updated
        $user->refresh();
        $this->assertEquals($silverTier->id, $user->current_investment_tier_id);
    }

    public function test_subscription_suspension_when_no_downgrade_available()
    {
        // Arrange - Create only Bronze tier (lowest)
        $bronzeTier = InvestmentTier::factory()->create([
            'name' => 'Bronze',
            'monthly_fee' => 50.00
        ]);

        $user = User::factory()->create([
            'balance' => 10.00, // Insufficient balance
            'phone_number' => '+260977123456'
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $bronzeTier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay(),
            'failed_payment_attempts' => 3 // At maximum attempts
        ]);

        // Mock failed mobile money payment
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'test-token'
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([
                'code' => '500',
                'message' => 'Payment declined'
            ], 500)
        ]);

        $billingService = app(SubscriptionBillingService::class);

        // Act: Process billing (should fail and suspend)
        $billingResult = $billingService->processSubscriptionPayment($subscription);

        // Assert: Payment failed and subscription suspended
        $this->assertFalse($billingResult['success']);
        $this->assertTrue($billingResult['downgraded']);
        $this->assertStringContainsString('Suspended', $billingResult['error']);

        // Verify subscription suspended
        $subscription->refresh();
        $this->assertEquals('suspended', $subscription->status);
        $this->assertNotNull($subscription->suspended_at);
        $this->assertEquals('failed_payments', $subscription->suspension_reason);

        // Verify user subscription status updated
        $user->refresh();
        $this->assertEquals('suspended', $user->subscription_status);
    }

    public function test_batch_subscription_billing_processing()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create([
            'monthly_fee' => 100.00
        ]);

        // Create users with different payment scenarios
        $successUser = User::factory()->create([
            'balance' => 200.00, // Sufficient balance
            'phone_number' => '+260977123456'
        ]);

        $mobileMoneyUser = User::factory()->create([
            'balance' => 50.00, // Insufficient balance, will use mobile money
            'phone_number' => '+260967123456'
        ]);

        $failUser = User::factory()->create([
            'balance' => 20.00, // Insufficient balance
            'phone_number' => 'invalid-phone' // Invalid phone for mobile money
        ]);

        // Create subscriptions due for billing
        $successSubscription = Subscription::factory()->create([
            'user_id' => $successUser->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay()
        ]);

        $mobileMoneySubscription = Subscription::factory()->create([
            'user_id' => $mobileMoneyUser->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay()
        ]);

        $failSubscription = Subscription::factory()->create([
            'user_id' => $failUser->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay(),
            'failed_payment_attempts' => 0
        ]);

        // Mock mobile money responses
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'test-token'
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([], 202),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer/*' => Http::response([
                'status' => 'SUCCESSFUL'
            ])
        ]);

        $billingService = app(SubscriptionBillingService::class);

        // Act: Process all due subscriptions
        $results = $billingService->processAllDueSubscriptions();

        // Assert: Batch processing results
        $this->assertEquals(3, $results['total_processed']);
        $this->assertEquals(2, $results['successful_payments']); // Balance + mobile money
        $this->assertEquals(1, $results['failed_payments']); // Invalid phone
        $this->assertEquals(300.00, $results['total_amount']);
        $this->assertEquals(0, $results['downgrades']); // No downgrades on first failure

        // Verify individual subscription states
        $successSubscription->refresh();
        $this->assertEquals(0, $successSubscription->failed_payment_attempts);
        $this->assertNotNull($successSubscription->last_payment_at);

        $mobileMoneySubscription->refresh();
        $this->assertEquals(0, $mobileMoneySubscription->failed_payment_attempts);
        $this->assertNotNull($mobileMoneySubscription->last_payment_at);

        $failSubscription->refresh();
        $this->assertEquals(1, $failSubscription->failed_payment_attempts);
        $this->assertNull($failSubscription->last_payment_at);
    }

    public function test_subscription_retry_mechanism()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create([
            'monthly_fee' => 100.00
        ]);

        $user = User::factory()->create([
            'balance' => 150.00, // Now has sufficient balance
            'phone_number' => '+260977123456'
        ]);

        // Create subscription with previous failed attempts
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay(),
            'failed_payment_attempts' => 2 // Previous failures
        ]);

        $billingService = app(SubscriptionBillingService::class);

        // Act: Retry failed payments
        $retryResults = $billingService->retryFailedSubscriptionPayments();

        // Assert: Retry successful
        $this->assertEquals(1, $retryResults['total_retried']);
        $this->assertEquals(1, $retryResults['successful_retries']);
        $this->assertEquals(0, $retryResults['failed_retries']);

        // Verify subscription payment succeeded
        $subscription->refresh();
        $this->assertEquals(0, $subscription->failed_payment_attempts);
        $this->assertNotNull($subscription->last_payment_at);
        $this->assertEquals(100.00, $subscription->last_payment_amount);

        // Verify user balance deducted
        $this->assertEquals(50.00, $user->fresh()->balance);
    }

    public function test_subscription_statistics_calculation()
    {
        // Arrange
        $goldTier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        $silverTier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'monthly_fee' => 100.00
        ]);

        // Create various subscription states
        Subscription::factory()->active()->forTier($goldTier)->create([
            'created_at' => now()->subDays(5)
        ]);

        Subscription::factory()->suspended()->forTier($silverTier)->create([
            'created_at' => now()->subDays(3)
        ]);

        Subscription::factory()->cancelled()->forTier($goldTier)->create([
            'created_at' => now()->subDays(1)
        ]);

        Subscription::factory()->upgraded()->forTier($goldTier)->create([
            'created_at' => now()->subDays(10)
        ]);

        $billingService = app(SubscriptionBillingService::class);

        // Act: Get statistics
        $stats = $billingService->getSubscriptionStatistics('month');

        // Assert: Statistics calculated correctly
        $this->assertEquals(4, $stats['total_subscriptions']);
        $this->assertEquals(2, $stats['active_subscriptions']); // Active + upgraded
        $this->assertEquals(1, $stats['suspended_subscriptions']);
        $this->assertArrayHasKey('by_tier', $stats);
        $this->assertArrayHasKey('Gold', $stats['by_tier']);
        $this->assertArrayHasKey('Silver', $stats['by_tier']);
        $this->assertEquals(1, $stats['upgrades']);
        $this->assertGreaterThan(0, $stats['success_rate']);
    }
}