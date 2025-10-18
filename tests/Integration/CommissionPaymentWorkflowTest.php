<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Services\CommissionPaymentService;
use App\Services\MobileMoneyService;
use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification;

class CommissionPaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test configuration
        config([
            'mygrownet.minimum_payment_threshold' => 10.00,
            'mygrownet.payment_processing_delay_hours' => 24,
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

    public function test_complete_commission_payment_workflow()
    {
        // Arrange
        Queue::fake();
        Notification::fake();

        // Create users with referral relationships
        $referrer = User::factory()->create([
            'phone_number' => '+260967123456',
            'preferred_payment_method' => 'mobile_money',
            'name' => 'John Referrer'
        ]);

        $referee = User::factory()->create([
            'referrer_id' => $referrer->id,
            'name' => 'Jane Referee'
        ]);

        // Create eligible commissions (older than 24 hours)
        $commissions = ReferralCommission::factory()->count(3)->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $referee->id,
            'status' => 'pending',
            'amount' => 50.00,
            'level' => 1,
            'commission_type' => 'REFERRAL',
            'created_at' => now()->subHours(25)
        ]);

        // Mock successful MTN payment
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'test-access-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([], 202),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer/*' => Http::response([
                'status' => 'SUCCESSFUL',
                'financialTransactionId' => 'mtn-txn-12345'
            ])
        ]);

        // Act
        $paymentService = app(CommissionPaymentService::class);
        $results = $paymentService->processAllPendingPayments();

        // Assert
        $this->assertEquals(3, $results['total_processed']);
        $this->assertEquals(3, $results['successful_payments']);
        $this->assertEquals(0, $results['failed_payments']);
        $this->assertEquals(150.00, $results['total_amount']);
        $this->assertEmpty($results['errors']);

        // Verify commissions are marked as paid
        $this->assertEquals(3, ReferralCommission::where('status', 'paid')->count());
        
        // Verify payment transaction is created and completed
        $paymentTransaction = PaymentTransaction::where('user_id', $referrer->id)->first();
        $this->assertNotNull($paymentTransaction);
        $this->assertEquals('completed', $paymentTransaction->status);
        $this->assertEquals(150.00, $paymentTransaction->amount);
        $this->assertEquals('commission_payment', $paymentTransaction->type);
        $this->assertNotNull($paymentTransaction->completed_at);

        // Verify user balance is updated
        $referrer->refresh();
        $this->assertEquals(150.00, $referrer->balance);
        $this->assertEquals(150.00, $referrer->total_earnings);

        // Verify HTTP requests were made correctly
        Http::assertSent(function ($request) {
            return $request->url() === 'https://sandbox.momodeveloper.mtn.com/disbursement/token/';
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'disbursement/v1_0/transfer') &&
                   $request->method() === 'POST' &&
                   $request['amount'] === '150' &&
                   $request['currency'] === 'ZMW';
        });
    }

    public function test_handles_payment_failure_and_retry_workflow()
    {
        // Arrange
        $referrer = User::factory()->create([
            'phone_number' => '+260967123456',
            'preferred_payment_method' => 'mobile_money'
        ]);

        $commissions = ReferralCommission::factory()->count(2)->create([
            'referrer_id' => $referrer->id,
            'status' => 'pending',
            'amount' => 75.00,
            'created_at' => now()->subHours(25)
        ]);

        // Mock failed payment initially
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'test-access-token'
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([
                'code' => '500',
                'message' => 'Internal Server Error'
            ], 500)
        ]);

        $paymentService = app(CommissionPaymentService::class);

        // Act - Initial payment attempt (should fail)
        $results = $paymentService->processAllPendingPayments();

        // Assert - Payment failed
        $this->assertEquals(2, $results['total_processed']);
        $this->assertEquals(0, $results['successful_payments']);
        $this->assertEquals(2, $results['failed_payments']);
        $this->assertNotEmpty($results['errors']);

        // Verify commissions remain pending
        $this->assertEquals(2, ReferralCommission::where('status', 'pending')->count());
        
        // Verify failed payment transaction
        $paymentTransaction = PaymentTransaction::where('user_id', $referrer->id)->first();
        $this->assertEquals('failed', $paymentTransaction->status);
        $this->assertNotNull($paymentTransaction->failed_at);
        $this->assertNotNull($paymentTransaction->failure_reason);

        // Mock successful retry
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'test-access-token'
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([], 202),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer/*' => Http::response([
                'status' => 'SUCCESSFUL',
                'financialTransactionId' => 'mtn-retry-12345'
            ])
        ]);

        // Act - Retry failed payments
        $retryResults = $paymentService->retryFailedPayments();

        // Assert - Retry successful
        $this->assertEquals(1, $retryResults['total_retried']);
        $this->assertEquals(1, $retryResults['successful_retries']);
        $this->assertEquals(0, $retryResults['failed_retries']);

        // Verify transaction is now completed
        $paymentTransaction->refresh();
        $this->assertEquals('completed', $paymentTransaction->status);
        $this->assertNotNull($paymentTransaction->completed_at);
        $this->assertEquals(1, $paymentTransaction->retry_count);

        // Verify commissions are now paid
        $this->assertEquals(2, ReferralCommission::where('status', 'paid')->count());
    }

    public function test_processes_multiple_users_with_different_providers()
    {
        // Arrange
        $mtnUser = User::factory()->create([
            'phone_number' => '+260967123456', // MTN
            'preferred_payment_method' => 'mobile_money'
        ]);

        $airtelUser = User::factory()->create([
            'phone_number' => '+260977123456', // Airtel
            'preferred_payment_method' => 'mobile_money'
        ]);

        // Create commissions for both users
        ReferralCommission::factory()->count(2)->create([
            'referrer_id' => $mtnUser->id,
            'status' => 'pending',
            'amount' => 60.00,
            'created_at' => now()->subHours(25)
        ]);

        ReferralCommission::factory()->count(1)->create([
            'referrer_id' => $airtelUser->id,
            'status' => 'pending',
            'amount' => 80.00,
            'created_at' => now()->subHours(25)
        ]);

        // Mock both MTN and Airtel responses
        Http::fake([
            // MTN endpoints
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'mtn-token'
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([], 202),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer/*' => Http::response([
                'status' => 'SUCCESSFUL'
            ]),
            
            // Airtel endpoints
            'https://openapiuat.airtel.africa/auth/oauth2/token' => Http::response([
                'access_token' => 'airtel-token'
            ]),
            'https://openapiuat.airtel.africa/standard/v1/disbursements/' => Http::response([
                'status' => ['success' => true],
                'data' => ['transaction' => ['id' => 'airtel-txn-123']]
            ])
        ]);

        // Set Airtel configuration
        config([
            'mygrownet.mobile_money.airtel' => [
                'client_id' => 'test-client-id',
                'client_secret' => 'test-client-secret',
                'token_url' => 'https://openapiuat.airtel.africa/auth/oauth2/token',
                'disbursement_url' => 'https://openapiuat.airtel.africa/standard/v1/disbursements/'
            ]
        ]);

        // Act
        $paymentService = app(CommissionPaymentService::class);
        $results = $paymentService->processAllPendingPayments();

        // Assert
        $this->assertEquals(3, $results['total_processed']);
        $this->assertEquals(3, $results['successful_payments']);
        $this->assertEquals(0, $results['failed_payments']);
        $this->assertEquals(200.00, $results['total_amount']);

        // Verify both users have completed transactions
        $mtnTransaction = PaymentTransaction::where('user_id', $mtnUser->id)->first();
        $airtelTransaction = PaymentTransaction::where('user_id', $airtelUser->id)->first();

        $this->assertEquals('completed', $mtnTransaction->status);
        $this->assertEquals('completed', $airtelTransaction->status);
        $this->assertEquals(120.00, $mtnTransaction->amount);
        $this->assertEquals(80.00, $airtelTransaction->amount);

        // Verify correct API calls were made
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'momodeveloper.mtn.com');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'openapiuat.airtel.africa');
        });
    }

    public function test_respects_minimum_payment_threshold()
    {
        // Arrange
        config(['mygrownet.minimum_payment_threshold' => 100.00]);

        $user = User::factory()->create([
            'phone_number' => '+260967123456',
            'preferred_payment_method' => 'mobile_money'
        ]);

        // Create commissions below threshold
        ReferralCommission::factory()->count(2)->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 30.00, // Total: 60.00 (below 100.00 threshold)
            'created_at' => now()->subHours(25)
        ]);

        // Act
        $paymentService = app(CommissionPaymentService::class);
        $results = $paymentService->processAllPendingPayments();

        // Assert
        $this->assertEquals(2, $results['total_processed']);
        $this->assertEquals(0, $results['successful_payments']);
        $this->assertEquals(2, $results['failed_payments']);
        $this->assertStringContainsString('below minimum threshold', $results['errors'][0]);

        // Verify commissions remain pending
        $this->assertEquals(2, ReferralCommission::where('status', 'pending')->count());
        
        // Verify no payment transaction was created
        $this->assertEquals(0, PaymentTransaction::count());
    }

    public function test_skips_commissions_within_delay_period()
    {
        // Arrange
        config(['mygrownet.payment_processing_delay_hours' => 24]);

        $user = User::factory()->create([
            'phone_number' => '+260967123456'
        ]);

        // Create recent commission (within delay period)
        $recentCommission = ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00,
            'created_at' => now()->subHours(12) // Within 24-hour delay
        ]);

        // Create eligible commission (outside delay period)
        $eligibleCommission = ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'status' => 'pending',
            'amount' => 100.00,
            'created_at' => now()->subHours(25) // Outside 24-hour delay
        ]);

        // Mock successful payment
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'test-token'
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([], 202),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer/*' => Http::response([
                'status' => 'SUCCESSFUL'
            ])
        ]);

        // Act
        $paymentService = app(CommissionPaymentService::class);
        $results = $paymentService->processAllPendingPayments();

        // Assert
        $this->assertEquals(1, $results['total_processed']); // Only eligible commission
        $this->assertEquals(1, $results['successful_payments']);
        $this->assertEquals(100.00, $results['total_amount']);

        // Verify only eligible commission is paid
        $this->assertEquals('paid', $eligibleCommission->fresh()->status);
        $this->assertEquals('pending', $recentCommission->fresh()->status);
    }
}