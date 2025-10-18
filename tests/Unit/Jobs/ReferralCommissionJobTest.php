<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ReferralCommissionJob;
use App\Domain\Reward\Services\ReferralMatrixService;
use App\Models\Investment;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\ReferralCommission;
use App\Notifications\ReferralCommissionNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class ReferralCommissionJobTest extends TestCase
{
    protected $matrixService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->matrixService = Mockery::mock(ReferralMatrixService::class);
        $this->app->instance(ReferralMatrixService::class, $this->matrixService);
        
        Notification::fake();
        Log::spy();
    }

    /** @test */
    public function it_can_be_constructed_with_investment_commission_parameters()
    {
        $job = new ReferralCommissionJob(
            jobType: 'process_investment_commissions',
            investmentId: 123
        );

        $this->assertEquals('process_investment_commissions', $job->jobType);
        $this->assertEquals(123, $job->investmentId);
        $this->assertNull($job->withdrawalRequestId);
    }

    /** @test */
    public function it_can_be_constructed_with_clawback_parameters()
    {
        $job = new ReferralCommissionJob(
            jobType: 'process_clawback',
            withdrawalRequestId: 456
        );

        $this->assertEquals('process_clawback', $job->jobType);
        $this->assertEquals(456, $job->withdrawalRequestId);
        $this->assertNull($job->investmentId);
    }

    /** @test */
    public function it_can_be_constructed_with_batch_processing_parameters()
    {
        $job = new ReferralCommissionJob(
            jobType: 'batch_process_pending',
            additionalData: ['batch_size' => 50, 'max_age_days' => 14]
        );

        $this->assertEquals('batch_process_pending', $job->jobType);
        $this->assertEquals(['batch_size' => 50, 'max_age_days' => 14], $job->additionalData);
    }

    /** @test */
    public function it_has_correct_queue_configuration()
    {
        $job = new ReferralCommissionJob('process_investment_commissions', 123);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
        $this->assertEquals([30, 60, 120], $job->backoff);
    }

    /** @test */
    public function it_handles_invalid_job_type()
    {
        $job = new ReferralCommissionJob('invalid_job_type');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid job type: invalid_job_type');

        $job->handle($this->matrixService);

        Log::shouldHaveReceived('error')
            ->with('Failed to process invalid_job_type referral commissions', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_processes_investment_commissions_successfully()
    {
        // Create a partial mock of the job to mock the processInvestmentCommissions method
        $job = Mockery::mock(ReferralCommissionJob::class, ['process_investment_commissions', 123])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $expectedResult = [
            'success' => true,
            'investment_id' => 123,
            'commissions_processed' => 3,
            'total_commission_amount' => 2250.00,
            'multi_level_commissions' => 1,
            'matrix_commissions' => 2,
            'processed_commissions' => [
                ['commission_id' => 1, 'referrer_id' => 2, 'amount' => 1000.00, 'level' => 1],
                ['commission_id' => 2, 'referrer_id' => 3, 'amount' => 500.00, 'level' => 2],
                ['commission_id' => 3, 'referrer_id' => 4, 'amount' => 750.00, 'level' => 1]
            ]
        ];

        $job->shouldReceive('processInvestmentCommissions')
            ->once()
            ->with($this->matrixService)
            ->andReturn($expectedResult);

        $job->shouldReceive('sendSuccessNotifications')
            ->once()
            ->with($expectedResult);

        $job->handle($this->matrixService);

        Log::shouldHaveReceived('info')
            ->with('Starting process_investment_commissions referral commission processing', Mockery::any())
            ->once();
            
        Log::shouldHaveReceived('info')
            ->with('Successfully completed process_investment_commissions processing', $expectedResult)
            ->once();
    }

    /** @test */
    public function it_handles_missing_investment_for_commission_processing()
    {
        $job = Mockery::mock(ReferralCommissionJob::class, ['process_investment_commissions', 999])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $job->shouldReceive('processInvestmentCommissions')
            ->once()
            ->with($this->matrixService)
            ->andThrow(new Exception('Investment not found: 999'));

        $job->shouldReceive('sendFailureNotifications')
            ->once()
            ->with(Mockery::type(Exception::class));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Investment not found: 999');

        $job->handle($this->matrixService);
    }

    /** @test */
    public function it_calculates_correct_clawback_percentages()
    {
        $job = new ReferralCommissionJob('process_clawback', 123);
        
        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('getClawbackPercentage');
        $method->setAccessible(true);

        // Test different time periods
        $this->assertEquals(50.0, $method->invoke($job, 0)); // 0 months = 50%
        $this->assertEquals(50.0, $method->invoke($job, 1)); // 1 month = 50%
        $this->assertEquals(25.0, $method->invoke($job, 2)); // 2 months = 25%
        $this->assertEquals(25.0, $method->invoke($job, 3)); // 3 months = 25%
        $this->assertEquals(0.0, $method->invoke($job, 4));  // 4+ months = 0%
        $this->assertEquals(0.0, $method->invoke($job, 12)); // 12 months = 0%
    }

    /** @test */
    public function it_handles_missing_withdrawal_request_for_clawback()
    {
        $job = Mockery::mock(ReferralCommissionJob::class, ['process_clawback', null, 999])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $job->shouldReceive('processCommissionClawback')
            ->once()
            ->andThrow(new Exception('Withdrawal request not found: 999'));

        $job->shouldReceive('sendFailureNotifications')
            ->once()
            ->with(Mockery::type(Exception::class));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Withdrawal request not found: 999');

        $job->handle($this->matrixService);
    }

    /** @test */
    public function it_returns_correct_tags_for_investment_processing()
    {
        $job = new ReferralCommissionJob('process_investment_commissions', 123);

        $tags = $job->tags();

        $this->assertEquals([
            'referral-commission',
            'process_investment_commissions',
            'investment:123'
        ], $tags);
    }

    /** @test */
    public function it_returns_correct_tags_for_clawback_processing()
    {
        $job = new ReferralCommissionJob('process_clawback', null, 456);

        $tags = $job->tags();

        $this->assertEquals([
            'referral-commission',
            'process_clawback',
            'withdrawal:456'
        ], $tags);
    }

    /** @test */
    public function it_returns_correct_tags_for_batch_processing()
    {
        $job = new ReferralCommissionJob('batch_process_pending');

        $tags = $job->tags();

        $this->assertEquals([
            'referral-commission',
            'batch_process_pending'
        ], $tags);
    }

    /** @test */
    public function it_logs_critical_failure_when_job_fails_permanently()
    {
        $exception = new Exception('Critical system failure');
        
        $job = new ReferralCommissionJob('process_investment_commissions', 123);

        // Mock the attempts method
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(3);

        $job->failed($exception);

        Log::shouldHaveReceived('critical')
            ->with('ReferralCommissionJob failed permanently', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_handles_matrix_service_exceptions()
    {
        $job = Mockery::mock(ReferralCommissionJob::class, ['process_investment_commissions', 123])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $exception = new Exception('Matrix service unavailable');
        
        $job->shouldReceive('processInvestmentCommissions')
            ->once()
            ->with($this->matrixService)
            ->andThrow($exception);

        $job->shouldReceive('sendFailureNotifications')
            ->once()
            ->with($exception);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Matrix service unavailable');

        $job->handle($this->matrixService);

        Log::shouldHaveReceived('error')
            ->with('Failed to process process_investment_commissions referral commissions', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_handles_notification_failures_gracefully()
    {
        // This test ensures that notification failures don't break the main job processing
        $job = new ReferralCommissionJob('process_investment_commissions', 123);
        
        // Mock a successful result
        $result = [
            'success' => true,
            'processed_commissions' => [
                ['referrer_id' => 999, 'amount' => 100.00] // Non-existent user
            ]
        ];

        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('sendSuccessNotifications');
        $method->setAccessible(true);

        // Should not throw exception even if notification fails
        $method->invoke($job, $result);

        Log::shouldHaveReceived('warning')
            ->with('Failed to send success notifications for process_investment_commissions', Mockery::any())
            ->once();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}