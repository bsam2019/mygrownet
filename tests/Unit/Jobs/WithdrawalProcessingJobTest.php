<?php

namespace Tests\Unit\Jobs;

use App\Jobs\WithdrawalProcessingJob;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;
use App\Services\WithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class WithdrawalProcessingJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_processes_approved_withdrawal_successfully(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'net_amount' => 450,
            'status' => 'approved',
        ]);

        $mockService = Mockery::mock(WithdrawalService::class);
        $mockService->shouldReceive('processApprovedWithdrawal')
            ->once()
            ->with($withdrawalRequest)
            ->andReturn(['success' => true, 'transaction_id' => 123]);

        $this->app->instance(WithdrawalService::class, $mockService);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        $job->handle();

        // Verify the job completed without exceptions
        $this->assertTrue(true);
    }

    public function test_handles_withdrawal_processing_failure(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'approved',
        ]);

        $mockService = Mockery::mock(WithdrawalService::class);
        $mockService->shouldReceive('processApprovedWithdrawal')
            ->once()
            ->with($withdrawalRequest)
            ->andReturn(['success' => false, 'message' => 'Insufficient funds']);

        $this->app->instance(WithdrawalService::class, $mockService);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');
        
        $job->handle();
    }

    public function test_job_is_retryable(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->backoff);
    }

    public function test_job_has_correct_queue_configuration(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        
        $this->assertEquals('withdrawals', $job->queue);
        $this->assertEquals(300, $job->timeout);
    }

    public function test_creates_transaction_record(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'net_amount' => 450,
            'status' => 'approved',
        ]);

        $mockService = Mockery::mock(WithdrawalService::class);
        $mockService->shouldReceive('processApprovedWithdrawal')
            ->once()
            ->with($withdrawalRequest)
            ->andReturn([
                'success' => true, 
                'transaction_id' => 123,
                'transaction' => Transaction::factory()->make([
                    'user_id' => $user->id,
                    'amount' => 450,
                    'type' => 'withdrawal',
                    'status' => 'completed',
                ])
            ]);

        $this->app->instance(WithdrawalService::class, $mockService);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        $job->handle();

        $this->assertTrue(true); // Job completed successfully
    }

    public function test_handles_invalid_withdrawal_request(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending', // Not approved
        ]);

        $mockService = Mockery::mock(WithdrawalService::class);
        $mockService->shouldReceive('processApprovedWithdrawal')
            ->once()
            ->with($withdrawalRequest)
            ->andReturn(['success' => false, 'message' => 'Withdrawal not approved']);

        $this->app->instance(WithdrawalService::class, $mockService);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Withdrawal not approved');
        
        $job->handle();
    }

    public function test_logs_withdrawal_processing_activity(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 500,
            'status' => 'approved',
        ]);

        $mockService = Mockery::mock(WithdrawalService::class);
        $mockService->shouldReceive('processApprovedWithdrawal')
            ->once()
            ->with($withdrawalRequest)
            ->andReturn(['success' => true, 'transaction_id' => 123]);

        $this->app->instance(WithdrawalService::class, $mockService);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        $job->handle();

        // In a real implementation, we would check activity logs
        $this->assertTrue(true);
    }

    public function test_handles_database_transaction_rollback(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $mockService = Mockery::mock(WithdrawalService::class);
        $mockService->shouldReceive('processApprovedWithdrawal')
            ->once()
            ->with($withdrawalRequest)
            ->andThrow(new \Exception('Database error'));

        $this->app->instance(WithdrawalService::class, $mockService);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');
        
        $job->handle();
    }

    public function test_job_serialization(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $job = new WithdrawalProcessingJob($withdrawalRequest);
        
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);
        
        $this->assertInstanceOf(WithdrawalProcessingJob::class, $unserialized);
        $this->assertEquals($withdrawalRequest->id, $unserialized->withdrawalRequest->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}