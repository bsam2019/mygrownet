<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\SubscriptionBillingService;
use App\Services\MobileMoneyService;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;

class SubscriptionBillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionBillingService $billingService;
    protected $mockMobileMoneyService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockMobileMoneyService = Mockery::mock(MobileMoneyService::class);
        $this->billingService = new SubscriptionBillingService($this->mockMobileMoneyService);
    }

    public function test_processes_subscription_payment_with_balance()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        $user = User::factory()->create([
            'balance' => 300.00,
            'phone_number' => '+260977123456'
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay()
        ]);

        // Act
        $result = $this->billingService->processSubscriptionPayment($subscription);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(200.00, $result['amount']);
        $this->assertEquals('balance', $result['payment_method']);
        $this->assertFalse($result['downgraded']);

        // Verify user balance is deducted
        $this->assertEquals(100.00, $user->fresh()->balance);
        
        // Verify subscription is extended
        $subscription->refresh();
        $this->assertEquals(0, $subscription->failed_payment_attempts);
        $this->assertNotNull($subscription->last_payment_at);
        
        // Verify payment transaction is created
        $this->assertEquals(1, PaymentTransaction::where('type', 'subscription_payment')->count());
    }

    public function test_processes_subscription_payment_with_mobile_money()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'monthly_fee' => 100.00
        ]);

        $user = User::factory()->create([
            'balance' => 50.00, // Insufficient balance
            'phone_number' => '+260977123456'
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay()
        ]);

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->once()
            ->andReturn([
                'success' => true,
                'external_reference' => 'AIRTEL-12345'
            ]);

        // Act
        $result = $this->billingService->processSubscriptionPayment($subscription);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(100.00, $result['amount']);
        $this->assertEquals('mobile_money', $result['payment_method']);
        
        // Verify subscription is extended
        $subscription->refresh();
        $this->assertEquals(0, $subscription->failed_payment_attempts);
        $this->assertNotNull($subscription->last_payment_at);
    }

    public function test_handles_failed_payment_and_downgrades_user()
    {
        // Arrange
        config(['mygrownet.subscription.max_failed_attempts' => 3]);
        
        $goldTier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        $silverTier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'monthly_fee' => 100.00
        ]);

        $user = User::factory()->create([
            'balance' => 50.00, // Insufficient balance
            'phone_number' => '+260977123456'
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $goldTier->id,
            'status' => 'active',
            'next_billing_date' => now()->subDay(),
            'failed_payment_attempts' => 3 // At max attempts
        ]);

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'Insufficient funds'
            ]);

        // Act
        $result = $this->billingService->processSubscriptionPayment($subscription);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertTrue($result['downgraded']);
        $this->assertStringContainsString('Silver', $result['error']);
        
        // Verify user is downgraded
        $subscription->refresh();
        $this->assertEquals($silverTier->id, $subscription->tier_id);
        $this->assertNotNull($subscription->downgraded_at);
        $this->assertEquals('failed_payments', $subscription->downgrade_reason);
    }

    public function test_suspends_subscription_when_no_lower_tier_available()
    {
        // Arrange
        config(['mygrownet.subscription.max_failed_attempts' => 3]);
        
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
            'failed_payment_attempts' => 3 // At max attempts
        ]);

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'Payment declined'
            ]);

        // Act
        $result = $this->billingService->processSubscriptionPayment($subscription);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertTrue($result['downgraded']);
        $this->assertStringContainsString('Suspended', $result['error']);
        
        // Verify subscription is suspended
        $subscription->refresh();
        $this->assertEquals('suspended', $subscription->status);
        $this->assertNotNull($subscription->suspended_at);
        $this->assertEquals('failed_payments', $subscription->suspension_reason);
    }

    public function test_processes_all_due_subscriptions()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create([
            'monthly_fee' => 100.00
        ]);

        $users = User::factory()->count(3)->create([
            'balance' => 150.00,
            'phone_number' => '+260977123456'
        ]);

        foreach ($users as $user) {
            Subscription::factory()->create([
                'user_id' => $user->id,
                'tier_id' => $tier->id,
                'status' => 'active',
                'next_billing_date' => now()->subDay()
            ]);
        }

        // Act
        $results = $this->billingService->processAllDueSubscriptions();

        // Assert
        $this->assertEquals(3, $results['total_processed']);
        $this->assertEquals(3, $results['successful_payments']);
        $this->assertEquals(0, $results['failed_payments']);
        $this->assertEquals(300.00, $results['total_amount']);
        $this->assertEquals(0, $results['downgrades']);
        $this->assertEmpty($results['errors']);
    }

    public function test_creates_new_subscription()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        $user = User::factory()->create();

        // Act
        $subscription = $this->billingService->createSubscription($user, $tier);

        // Assert
        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals($user->id, $subscription->user_id);
        $this->assertEquals($tier->id, $subscription->tier_id);
        $this->assertEquals('active', $subscription->status);
        $this->assertNotNull($subscription->started_at);
        $this->assertNotNull($subscription->next_billing_date);
        $this->assertEquals(0, $subscription->failed_payment_attempts);
    }

    public function test_upgrades_subscription_with_prorated_payment()
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

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $silverTier->id,
            'status' => 'active',
            'next_billing_date' => now()->addDays(15) // Mid-cycle
        ]);

        // Act
        $result = $this->billingService->upgradeSubscription($subscription, $goldTier);

        // Assert
        $this->assertTrue($result);
        
        // Verify subscription is upgraded
        $subscription->refresh();
        $this->assertEquals($goldTier->id, $subscription->tier_id);
        $this->assertNotNull($subscription->upgraded_at);
        $this->assertEquals('user_requested', $subscription->upgrade_reason);
        
        // Verify user tier is updated
        $user->refresh();
        $this->assertEquals($goldTier->id, $user->current_investment_tier_id);
        $this->assertEquals(200.00, $user->monthly_subscription_fee);
    }

    public function test_calculates_prorated_upgrade_amount()
    {
        // Arrange
        $silverTier = InvestmentTier::factory()->create([
            'monthly_fee' => 100.00
        ]);

        $goldTier = InvestmentTier::factory()->create([
            'monthly_fee' => 200.00
        ]);

        $subscription = Subscription::factory()->create([
            'tier_id' => $silverTier->id,
            'next_billing_date' => now()->addDays(15) // Mid-cycle
        ]);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->billingService);
        $method = $reflection->getMethod('calculateProratedUpgradeAmount');
        $method->setAccessible(true);

        // Act
        $proratedAmount = $method->invoke($this->billingService, $subscription, $goldTier);

        // Assert
        $this->assertGreaterThan(0, $proratedAmount);
        $this->assertLessThan(100.00, $proratedAmount); // Should be less than full difference
    }

    public function test_retries_failed_subscription_payments()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create([
            'monthly_fee' => 100.00
        ]);

        $user = User::factory()->create([
            'balance' => 150.00
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'failed_payment_attempts' => 1,
            'next_billing_date' => now()->subDay()
        ]);

        // Act
        $results = $this->billingService->retryFailedSubscriptionPayments();

        // Assert
        $this->assertEquals(1, $results['total_retried']);
        $this->assertEquals(1, $results['successful_retries']);
        $this->assertEquals(0, $results['failed_retries']);
        
        // Verify subscription payment succeeded
        $subscription->refresh();
        $this->assertEquals(0, $subscription->failed_payment_attempts);
        $this->assertNotNull($subscription->last_payment_at);
    }

    public function test_gets_subscription_statistics()
    {
        // Arrange
        $tier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'monthly_fee' => 200.00
        ]);

        // Create various subscription statuses
        Subscription::factory()->create([
            'tier_id' => $tier->id,
            'status' => 'active',
            'created_at' => now()->subDays(5)
        ]);

        Subscription::factory()->create([
            'tier_id' => $tier->id,
            'status' => 'suspended',
            'created_at' => now()->subDays(3)
        ]);

        Subscription::factory()->create([
            'tier_id' => $tier->id,
            'status' => 'cancelled',
            'created_at' => now()->subDays(1)
        ]);

        // Act
        $stats = $this->billingService->getSubscriptionStatistics('month');

        // Assert
        $this->assertEquals(3, $stats['total_subscriptions']);
        $this->assertEquals(1, $stats['active_subscriptions']);
        $this->assertEquals(1, $stats['suspended_subscriptions']);
        $this->assertArrayHasKey('by_tier', $stats);
        $this->assertArrayHasKey('Gold', $stats['by_tier']);
    }

    public function test_validates_user_payment_details()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->billingService);
        $method = $reflection->getMethod('validateUserPaymentDetails');
        $method->setAccessible(true);

        // Test valid phone number
        $validUser = User::factory()->create([
            'phone_number' => '+260977123456'
        ]);
        $this->assertTrue($method->invoke($this->billingService, $validUser));

        // Test invalid phone number
        $invalidUser = User::factory()->create([
            'phone_number' => 'invalid'
        ]);
        $this->assertFalse($method->invoke($this->billingService, $invalidUser));

        // Test missing phone number
        $noPhoneUser = User::factory()->create([
            'phone_number' => null
        ]);
        $this->assertFalse($method->invoke($this->billingService, $noPhoneUser));
    }

    public function test_generates_unique_subscription_reference()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->billingService);
        $method = $reflection->getMethod('generateSubscriptionReference');
        $method->setAccessible(true);

        // Act
        $reference1 = $method->invoke($this->billingService, 123);
        $reference2 = $method->invoke($this->billingService, 123);

        // Assert
        $this->assertStringStartsWith('MGN-SUB-123-', $reference1);
        $this->assertStringStartsWith('MGN-SUB-123-', $reference2);
        $this->assertNotEquals($reference1, $reference2);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}