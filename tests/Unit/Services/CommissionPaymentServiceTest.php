<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CommissionPaymentService;
use App\Services\MobileMoneyService;
use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;

class CommissionPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommissionPaymentService $paymentService;
    protected $mockMobileMoneyService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockMobileMoneyService = Mockery::mock(MobileMoneyService::class);
        $this->paymentService = new CommissionPaymentService($this->mockMobileMoneyService);
    }

    public function test_processes_batch_payment_successfully()
    {
        // Arrange
        $user = User::factory()->create([
            'phone_number' => '+260977123456',
            'preferred_payment_method' => 'mobile_money'
        ]);

        $commissions = ReferralCommission::factory()->count(3)->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00,
            'created_at' => now()->subHours(25) // Eligible for payment
        ]);

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->once()
            ->andReturn([
                'success' => true,
                'external_reference' => 'MTN-12345',
                'provider' => 'mtn'
            ]);

        // Act
        $result = $this->paymentService->processBatchPayment($commissions);

        // Assert
        $this->assertEquals(3, $result['total_processed']);
        $this->assertEquals(3, $result['successful_payments']);
        $this->assertEquals(0, $result['failed_payments']);
        $this->assertEquals(300.00, $result['total_amount']);
        $this->assertEmpty($result['errors']);

        // Verify commissions are marked as paid
        $this->assertEquals(3, ReferralCommission::where('status', 'paid')->count());
        
        // Verify payment transaction is created
        $this->assertEquals(1, PaymentTransaction::where('status', 'completed')->count());
    }

    public function test_handles_payment_failure_gracefully()
    {
        // Arrange
        $user = User::factory()->create([
            'phone_number' => '+260977123456',
            'preferred_payment_method' => 'mobile_money'
        ]);

        $commissions = ReferralCommission::factory()->count(2)->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 50.00,
            'created_at' => now()->subHours(25)
        ]);

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'Insufficient funds in merchant account'
            ]);

        // Act
        $result = $this->paymentService->processBatchPayment($commissions);

        // Assert
        $this->assertEquals(2, $result['total_processed']);
        $this->assertEquals(0, $result['successful_payments']);
        $this->assertEquals(2, $result['failed_payments']);
        $this->assertNotEmpty($result['errors']);

        // Verify commissions remain pending
        $this->assertEquals(2, ReferralCommission::where('status', 'pending')->count());
        
        // Verify payment transaction is marked as failed
        $this->assertEquals(1, PaymentTransaction::where('status', 'failed')->count());
    }

    public function test_validates_minimum_payment_threshold()
    {
        // Arrange
        config(['mygrownet.minimum_payment_threshold' => 50.00]);
        
        $user = User::factory()->create([
            'phone_number' => '+260977123456'
        ]);

        $commissions = ReferralCommission::factory()->count(2)->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 20.00, // Below threshold
            'created_at' => now()->subHours(25)
        ]);

        // Act
        $result = $this->paymentService->processBatchPayment($commissions);

        // Assert
        $this->assertEquals(2, $result['total_processed']);
        $this->assertEquals(0, $result['successful_payments']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('below minimum threshold', $result['errors'][0]);
    }

    public function test_validates_user_payment_details()
    {
        // Arrange
        $user = User::factory()->create([
            'phone_number' => null // Invalid phone number
        ]);

        $commissions = ReferralCommission::factory()->count(1)->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00,
            'created_at' => now()->subHours(25)
        ]);

        // Act
        $result = $this->paymentService->processBatchPayment($commissions);

        // Assert
        $this->assertEquals(1, $result['total_processed']);
        $this->assertEquals(0, $result['successful_payments']);
        $this->assertEquals(1, $result['failed_payments']);
        $this->assertStringContainsString('Invalid payment details', $result['errors'][0]);
    }

    public function test_processes_individual_payment()
    {
        // Arrange
        $user = User::factory()->create([
            'phone_number' => '+260977123456',
            'preferred_payment_method' => 'mobile_money'
        ]);

        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 75.00,
            'created_at' => now()->subHours(25)
        ]);

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->once()
            ->andReturn([
                'success' => true,
                'external_reference' => 'MTN-67890'
            ]);

        // Act
        $result = $this->paymentService->processIndividualPayment($commission);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals('paid', $commission->fresh()->status);
        $this->assertNotNull($commission->fresh()->paid_at);
    }

    public function test_retries_failed_payments()
    {
        // Arrange
        $user = User::factory()->create([
            'phone_number' => '+260977123456'
        ]);

        $failedTransaction = PaymentTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => 'commission_payment',
            'status' => 'failed',
            'amount' => 100.00,
            'retry_count' => 0,
            'payment_details' => [
                'commission_ids' => [1, 2, 3]
            ]
        ]);

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->once()
            ->andReturn([
                'success' => true,
                'external_reference' => 'MTN-RETRY-123'
            ]);

        // Act
        $result = $this->paymentService->retryFailedPayments();

        // Assert
        $this->assertEquals(1, $result['total_retried']);
        $this->assertEquals(1, $result['successful_retries']);
        $this->assertEquals(0, $result['failed_retries']);
        
        $this->assertEquals('completed', $failedTransaction->fresh()->status);
        $this->assertEquals(1, $failedTransaction->fresh()->retry_count);
    }

    public function test_processes_all_pending_payments()
    {
        // Arrange
        $users = User::factory()->count(3)->create([
            'phone_number' => '+260977123456',
            'preferred_payment_method' => 'mobile_money'
        ]);

        foreach ($users as $user) {
            ReferralCommission::factory()->count(2)->create([
                'referrer_id' => $user->id,
                'status' => 'pending',
                'amount' => 50.00,
                'created_at' => now()->subHours(25)
            ]);
        }

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->times(3)
            ->andReturn([
                'success' => true,
                'external_reference' => 'MTN-BATCH-123'
            ]);

        // Act
        $result = $this->paymentService->processAllPendingPayments();

        // Assert
        $this->assertEquals(6, $result['total_processed']);
        $this->assertEquals(6, $result['successful_payments']);
        $this->assertEquals(0, $result['failed_payments']);
        $this->assertEquals(300.00, $result['total_amount']);
    }

    public function test_generates_unique_payment_reference()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->paymentService);
        $method = $reflection->getMethod('generatePaymentReference');
        $method->setAccessible(true);

        // Act
        $reference1 = $method->invoke($this->paymentService, 123);
        $reference2 = $method->invoke($this->paymentService, 123);

        // Assert
        $this->assertStringStartsWith('MGN-COM-123-', $reference1);
        $this->assertStringStartsWith('MGN-COM-123-', $reference2);
        $this->assertNotEquals($reference1, $reference2);
    }

    public function test_gets_payment_statistics()
    {
        // Arrange
        PaymentTransaction::factory()->create([
            'type' => 'commission_payment',
            'status' => 'completed',
            'amount' => 100.00,
            'created_at' => now()->subDays(5)
        ]);

        PaymentTransaction::factory()->create([
            'type' => 'commission_payment',
            'status' => 'failed',
            'amount' => 50.00,
            'created_at' => now()->subDays(3)
        ]);

        // Act
        $stats = $this->paymentService->getPaymentStatistics('month');

        // Assert
        $this->assertEquals(2, $stats['total_transactions']);
        $this->assertEquals(1, $stats['successful_payments']);
        $this->assertEquals(1, $stats['failed_payments']);
        $this->assertEquals(100.00, $stats['total_amount_paid']);
        $this->assertEquals(50.00, $stats['total_amount_failed']);
        $this->assertEquals(50.0, $stats['success_rate']);
    }

    public function test_skips_commissions_not_eligible_for_payment()
    {
        // Arrange
        $user = User::factory()->create([
            'phone_number' => '+260977123456'
        ]);

        // Create commission that's too recent (less than 24 hours)
        $recentCommission = ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00,
            'created_at' => now()->subHours(12) // Not eligible yet
        ]);

        // Create eligible commission
        $eligibleCommission = ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00,
            'created_at' => now()->subHours(25) // Eligible
        ]);

        $this->mockMobileMoneyService
            ->shouldReceive('sendPayment')
            ->once()
            ->andReturn([
                'success' => true,
                'external_reference' => 'MTN-ELIGIBLE-123'
            ]);

        // Act
        $result = $this->paymentService->processAllPendingPayments();

        // Assert
        $this->assertEquals(1, $result['total_processed']); // Only eligible commission
        $this->assertEquals(1, $result['successful_payments']);
        
        // Recent commission should still be pending
        $this->assertEquals('pending', $recentCommission->fresh()->status);
        // Eligible commission should be paid
        $this->assertEquals('paid', $eligibleCommission->fresh()->status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}