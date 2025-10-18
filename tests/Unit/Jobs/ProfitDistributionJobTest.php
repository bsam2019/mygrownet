<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProfitDistributionJob;
use App\Domain\Financial\Services\ProfitDistributionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ProfitDistributionJobTest extends TestCase
{
    protected $profitDistributionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->profitDistributionService = Mockery::mock(ProfitDistributionService::class);
        $this->app->instance(ProfitDistributionService::class, $this->profitDistributionService);
        
        Log::spy();
    }

    /** @test */
    public function it_can_be_constructed_with_required_parameters()
    {
        $job = new ProfitDistributionJob(
            distributionType: 'annual',
            totalProfit: 100000.00,
            distributionDate: Carbon::now(),
            createdBy: 1
        );

        $this->assertEquals('annual', $job->distributionType);
        $this->assertEquals(100000.00, $job->totalProfit);
        $this->assertEquals(1, $job->createdBy);
    }

    /** @test */
    public function it_can_be_constructed_with_optional_bonus_pool_percentage()
    {
        $job = new ProfitDistributionJob(
            distributionType: 'quarterly',
            totalProfit: 50000.00,
            distributionDate: Carbon::now(),
            createdBy: 1,
            bonusPoolPercentage: 10.0
        );

        $this->assertEquals(10.0, $job->bonusPoolPercentage);
    }

    /** @test */
    public function it_has_correct_queue_configuration()
    {
        $job = new ProfitDistributionJob(
            distributionType: 'annual',
            totalProfit: 100000.00,
            distributionDate: Carbon::now(),
            createdBy: 1
        );

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(300, $job->timeout);
        $this->assertEquals([60, 120, 300], $job->backoff);
    }

    /** @test */
    public function it_processes_annual_distribution_successfully()
    {
        $distributionDate = Carbon::now();
        $createdBy = 1;
        
        $expectedResult = [
            'success' => true,
            'distribution_id' => 123,
            'total_distributed' => 75000.00,
            'user_count' => 10,
            'processed_shares' => [
                ['user_id' => 1, 'amount' => 7500.00, 'profit_share_id' => 1],
                ['user_id' => 2, 'amount' => 5000.00, 'profit_share_id' => 2]
            ]
        ];

        $this->profitDistributionService
            ->shouldReceive('processAnnualProfitDistribution')
            ->once()
            ->with(100000.00, $distributionDate, $createdBy)
            ->andReturn($expectedResult);

        $job = new ProfitDistributionJob(
            distributionType: 'annual',
            totalProfit: 100000.00,
            distributionDate: $distributionDate,
            createdBy: $createdBy
        );

        $job->handle($this->profitDistributionService);

        Log::shouldHaveReceived('info')
            ->with('Starting annual profit distribution', Mockery::any())
            ->once();
            
        Log::shouldHaveReceived('info')
            ->with('Successfully completed annual profit distribution', $expectedResult)
            ->once();
    }

    /** @test */
    public function it_processes_quarterly_distribution_successfully_with_default_bonus_percentage()
    {
        $distributionDate = Carbon::now();
        $createdBy = 1;
        
        $expectedResult = [
            'success' => true,
            'distribution_id' => 456,
            'quarter' => '2024-Q1',
            'total_distributed' => 37500.00,
            'user_count' => 8,
            'bonus_pool' => 40000.00,
            'remaining_pool' => 2500.00,
            'processed_bonuses' => [
                ['user_id' => 1, 'amount' => 18750.00, 'profit_share_id' => 3],
                ['user_id' => 2, 'amount' => 18750.00, 'profit_share_id' => 4]
            ]
        ];

        $this->profitDistributionService
            ->shouldReceive('processQuarterlyBonusDistribution')
            ->once()
            ->with(500000.00, 7.5, $distributionDate, $createdBy)
            ->andReturn($expectedResult);

        $job = new ProfitDistributionJob(
            distributionType: 'quarterly',
            totalProfit: 500000.00,
            distributionDate: $distributionDate,
            createdBy: $createdBy
        );

        $job->handle($this->profitDistributionService);

        Log::shouldHaveReceived('info')
            ->with('Starting quarterly profit distribution', Mockery::any())
            ->once();
            
        Log::shouldHaveReceived('info')
            ->with('Successfully completed quarterly profit distribution', $expectedResult)
            ->once();
    }

    /** @test */
    public function it_processes_quarterly_distribution_with_custom_bonus_percentage()
    {
        $distributionDate = Carbon::now();
        $createdBy = 1;
        $customBonusPercentage = 10.0;
        
        $expectedResult = [
            'success' => true,
            'distribution_id' => 789,
            'quarter' => '2024-Q2',
            'total_distributed' => 50000.00,
            'user_count' => 5,
            'bonus_pool' => 50000.00,
            'remaining_pool' => 0.00,
            'processed_bonuses' => []
        ];

        $this->profitDistributionService
            ->shouldReceive('processQuarterlyBonusDistribution')
            ->once()
            ->with(500000.00, $customBonusPercentage, $distributionDate, $createdBy)
            ->andReturn($expectedResult);

        $job = new ProfitDistributionJob(
            distributionType: 'quarterly',
            totalProfit: 500000.00,
            distributionDate: $distributionDate,
            createdBy: $createdBy,
            bonusPoolPercentage: $customBonusPercentage
        );

        $job->handle($this->profitDistributionService);
    }

    /** @test */
    public function it_handles_service_errors_and_logs_failures()
    {
        $distributionDate = Carbon::now();
        $errorMessage = 'Database connection failed';
        
        $this->profitDistributionService
            ->shouldReceive('processAnnualProfitDistribution')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => $errorMessage
            ]);

        $job = new ProfitDistributionJob(
            distributionType: 'annual',
            totalProfit: 100000.00,
            distributionDate: $distributionDate,
            createdBy: 1
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($errorMessage);

        $job->handle($this->profitDistributionService);

        Log::shouldHaveReceived('error')
            ->with('Failed to process annual profit distribution', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_handles_invalid_distribution_type()
    {
        $distributionDate = Carbon::now();
        
        $job = new ProfitDistributionJob(
            distributionType: 'invalid_type',
            totalProfit: 100000.00,
            distributionDate: $distributionDate,
            createdBy: 1
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid distribution type: invalid_type');

        $job->handle($this->profitDistributionService);

        Log::shouldHaveReceived('error')
            ->with('Failed to process invalid_type profit distribution', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_handles_service_exceptions_during_processing()
    {
        $distributionDate = Carbon::now();
        $exception = new Exception('Service unavailable');
        
        $this->profitDistributionService
            ->shouldReceive('processAnnualProfitDistribution')
            ->once()
            ->andThrow($exception);

        $job = new ProfitDistributionJob(
            distributionType: 'annual',
            totalProfit: 100000.00,
            distributionDate: $distributionDate,
            createdBy: 1
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service unavailable');

        $job->handle($this->profitDistributionService);

        Log::shouldHaveReceived('error')
            ->with('Failed to process annual profit distribution', Mockery::any())
            ->once();
    }

    /** @test */
    public function it_returns_correct_tags_for_job_identification()
    {
        $distributionDate = Carbon::parse('2024-03-15');
        $createdBy = 42;
        
        $job = new ProfitDistributionJob(
            distributionType: 'quarterly',
            totalProfit: 100000.00,
            distributionDate: $distributionDate,
            createdBy: $createdBy
        );

        $tags = $job->tags();

        $this->assertEquals([
            'profit-distribution',
            'quarterly',
            'user:42',
            'date:2024-03-15'
        ], $tags);
    }

    /** @test */
    public function it_logs_critical_failure_when_job_fails_permanently()
    {
        $distributionDate = Carbon::now();
        $exception = new Exception('Critical system failure');
        
        $job = new ProfitDistributionJob(
            distributionType: 'annual',
            totalProfit: 100000.00,
            distributionDate: $distributionDate,
            createdBy: 1
        );

        // Mock the attempts method
        $job = Mockery::mock($job)->makePartial();
        $job->shouldReceive('attempts')->andReturn(3);

        $job->failed($exception);

        Log::shouldHaveReceived('critical')
            ->with('ProfitDistributionJob failed permanently', Mockery::any())
            ->once();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}