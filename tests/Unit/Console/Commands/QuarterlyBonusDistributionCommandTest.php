<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\QuarterlyBonusDistributionCommand;
use App\Domain\Financial\Services\ProfitDistributionService;
use App\Models\User;
use App\Models\ProfitDistribution;
use App\Models\InvestmentTier;
use App\Notifications\ProfitDistributionNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class QuarterlyBonusDistributionCommandTest extends TestCase
{
    use RefreshDatabase;

    protected $profitDistributionService;
    protected $adminUser;
    protected $investorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profitDistributionService = Mockery::mock(ProfitDistributionService::class);
        $this->app->instance(ProfitDistributionService::class, $this->profitDistributionService);

        // Create admin role
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);

        // Create test users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $tier = InvestmentTier::factory()->create([
            'name' => 'Basic',
            'minimum_amount' => 500,
            'fixed_profit_rate' => 3.0
        ]);

        $this->investorUser = User::factory()->create([
            'current_investment_tier_id' => $tier->id,
            'total_investment_amount' => 1000
        ]);

        Notification::fake();
        Log::spy();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_run_dry_run_mode()
    {
        $bonusData = [
            'quarter' => '2023-4',
            'quarter_start' => Carbon::createFromDate(2023, 10, 1),
            'quarter_end' => Carbon::createFromDate(2023, 12, 31),
            'total_quarterly_profit' => 50000,
            'bonus_pool_percentage' => 7.5,
            'bonus_pool' => 3750,
            'total_investment_pool' => 10000,
            'total_allocated' => 3750,
            'allocation_count' => 1,
            'allocations' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 375,
                    'investment_amount' => 1000,
                    'pool_percentage' => 10.0,
                    'tier' => 'Basic',
                    'eligibility_status' => 'eligible'
                ]
            ],
            'remaining_pool' => 0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->with(50000.0, 7.5, Mockery::type(Carbon::class))
            ->andReturn($bonusData);

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--bonus-percentage' => 7.5,
            '--dry-run' => true,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Dry run completed', Artisan::output());
    }

    /** @test */
    public function it_validates_quarter_format()
    {
        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-5', // Invalid quarter number
            '--quarterly-profit' => 50000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Invalid quarter format', Artisan::output());
    }

    /** @test */
    public function it_validates_future_quarter()
    {
        $futureQuarter = now()->addQuarter()->format('Y') . '-' . now()->addQuarter()->quarter;

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => $futureQuarter,
            '--quarterly-profit' => 50000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Cannot process distribution for future quarter', Artisan::output());
    }

    /** @test */
    public function it_validates_quarterly_profit_input()
    {
        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => -1000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Quarterly profit must be a positive number', Artisan::output());
    }

    /** @test */
    public function it_validates_bonus_percentage_range()
    {
        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--bonus-percentage' => 15, // Above maximum
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Bonus percentage must be between 5% and 10%', Artisan::output());
    }

    /** @test */
    public function it_validates_admin_id()
    {
        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--admin-id' => 99999
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Admin user not found', Artisan::output());
    }

    /** @test */
    public function it_prevents_duplicate_distributions()
    {
        // Create existing distribution
        ProfitDistribution::factory()->create([
            'distribution_type' => 'quarterly_bonus',
            'distribution_date' => Carbon::createFromDate(2023, 12, 31)
        ]);

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('already exists', Artisan::output());
    }

    /** @test */
    public function it_allows_force_override_of_existing_distribution()
    {
        // Create existing distribution
        ProfitDistribution::factory()->create([
            'distribution_type' => 'quarterly_bonus',
            'distribution_date' => Carbon::createFromDate(2023, 12, 31)
        ]);

        $bonusData = [
            'quarter' => '2023-4',
            'quarter_start' => Carbon::createFromDate(2023, 10, 1),
            'quarter_end' => Carbon::createFromDate(2023, 12, 31),
            'total_quarterly_profit' => 50000,
            'bonus_pool_percentage' => 7.5,
            'bonus_pool' => 3750,
            'total_investment_pool' => 10000,
            'total_allocated' => 3750,
            'allocation_count' => 1,
            'allocations' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 375,
                    'investment_amount' => 1000,
                    'pool_percentage' => 10.0,
                    'tier' => 'Basic',
                    'eligibility_status' => 'eligible'
                ]
            ],
            'remaining_pool' => 0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->andReturn($bonusData);

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--force' => true,
            '--dry-run' => true,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_processes_successful_distribution()
    {
        $bonusData = [
            'quarter' => '2023-4',
            'quarter_start' => Carbon::createFromDate(2023, 10, 1),
            'quarter_end' => Carbon::createFromDate(2023, 12, 31),
            'total_quarterly_profit' => 50000,
            'bonus_pool_percentage' => 7.5,
            'bonus_pool' => 3750,
            'total_investment_pool' => 10000,
            'total_allocated' => 3750,
            'allocation_count' => 1,
            'allocations' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 375,
                    'investment_amount' => 1000,
                    'pool_percentage' => 10.0,
                    'tier' => 'Basic',
                    'eligibility_status' => 'eligible'
                ]
            ],
            'remaining_pool' => 0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->andReturn($bonusData);

        // Create the distribution record that would be created by the job
        $distribution = ProfitDistribution::factory()->create([
            'distribution_type' => 'quarterly_bonus',
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_amount' => 3750,
            'user_count' => 1,
            'created_by' => $this->adminUser->id,
            'status' => 'processed',
            'calculation_details' => [
                'bonus_pool' => 3750,
                'remaining_pool' => 0
            ]
        ]);

        // Mock the service method that would be called by the job
        $this->profitDistributionService
            ->shouldReceive('processQuarterlyBonusDistribution')
            ->once()
            ->andReturn([
                'success' => true,
                'distribution_id' => $distribution->id,
                'quarter' => '2023-4',
                'total_distributed' => 3750,
                'user_count' => 1,
                'bonus_pool' => 3750,
                'remaining_pool' => 0
            ]);

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--admin-id' => $this->adminUser->id
        ], ['yes']); // Simulate user confirmation

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Distribution Completed Successfully', Artisan::output());
        
        // Verify admin notifications were sent
        Notification::assertSentTo(
            User::role('admin')->get(),
            ProfitDistributionNotification::class
        );
    }

    /** @test */
    public function it_handles_processing_errors()
    {
        $bonusData = [
            'quarter' => '2023-4',
            'quarter_start' => Carbon::createFromDate(2023, 10, 1),
            'quarter_end' => Carbon::createFromDate(2023, 12, 31),
            'total_quarterly_profit' => 50000,
            'bonus_pool_percentage' => 7.5,
            'bonus_pool' => 3750,
            'total_investment_pool' => 10000,
            'total_allocated' => 3750,
            'allocation_count' => 1,
            'allocations' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 375,
                    'investment_amount' => 1000,
                    'pool_percentage' => 10.0,
                    'tier' => 'Basic',
                    'eligibility_status' => 'eligible'
                ]
            ],
            'remaining_pool' => 0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->andReturn($bonusData);

        $this->profitDistributionService
            ->shouldReceive('processQuarterlyBonusDistribution')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'Database connection failed'
            ]);

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--admin-id' => $this->adminUser->id
        ], ['yes']); // Simulate user confirmation

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Distribution Failed', Artisan::output());
        
        // Verify error notifications were sent
        Notification::assertSentTo(
            User::role('admin')->get(),
            ProfitDistributionNotification::class
        );
    }

    /** @test */
    public function it_handles_no_eligible_users()
    {
        $bonusData = [
            'quarter' => '2023-4',
            'quarter_start' => Carbon::createFromDate(2023, 10, 1),
            'quarter_end' => Carbon::createFromDate(2023, 12, 31),
            'total_quarterly_profit' => 50000,
            'bonus_pool_percentage' => 7.5,
            'bonus_pool' => 3750,
            'total_investment_pool' => 0,
            'total_allocated' => 0,
            'allocation_count' => 0,
            'allocations' => [],
            'remaining_pool' => 3750
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->andReturn($bonusData);

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('No eligible users found', Artisan::output());
    }

    /** @test */
    public function it_uses_previous_quarter_as_default()
    {
        $now = now();
        $currentQuarter = $now->quarter;
        
        if ($currentQuarter === 1) {
            $expectedYear = $now->subYear()->year;
            $expectedQuarter = 4;
        } else {
            $expectedYear = $now->year;
            $expectedQuarter = $currentQuarter - 1;
        }
        
        $expectedQuarterString = "{$expectedYear}-{$expectedQuarter}";
        
        $bonusData = [
            'quarter' => $expectedQuarterString,
            'quarter_start' => Carbon::createFromDate($expectedYear, ($expectedQuarter - 1) * 3 + 1, 1),
            'quarter_end' => Carbon::createFromDate($expectedYear, $expectedQuarter * 3, 1)->endOfMonth(),
            'total_quarterly_profit' => 50000,
            'bonus_pool_percentage' => 7.5,
            'bonus_pool' => 3750,
            'total_investment_pool' => 10000,
            'total_allocated' => 3750,
            'allocation_count' => 1,
            'allocations' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 375,
                    'investment_amount' => 1000,
                    'pool_percentage' => 10.0,
                    'tier' => 'Basic',
                    'eligibility_status' => 'eligible'
                ]
            ],
            'remaining_pool' => 0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->andReturn($bonusData);

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            '--quarterly-profit' => 50000,
            '--dry-run' => true,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString($expectedQuarterString, Artisan::output());
    }

    /** @test */
    public function it_generates_quarterly_distribution_report()
    {
        $distribution = ProfitDistribution::factory()->create([
            'distribution_type' => 'quarterly_bonus',
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_amount' => 3750,
            'user_count' => 1,
            'fund_profit' => 50000,
            'status' => 'processed',
            'calculation_details' => [
                'bonus_pool' => 3750,
                'bonus_pool_percentage' => 7.5,
                'remaining_pool' => 0
            ]
        ]);

        $command = new QuarterlyBonusDistributionCommand($this->profitDistributionService);
        $report = $command->generateQuarterlyDistributionReport('2023-4');

        $this->assertArrayHasKey('distribution_summary', $report);
        $this->assertArrayHasKey('tier_breakdown', $report);
        $this->assertArrayHasKey('payment_status', $report);
        $this->assertArrayHasKey('performance_metrics', $report);
        $this->assertEquals($distribution->id, $report['distribution_summary']['id']);
        $this->assertEquals('2023-4', $report['distribution_summary']['quarter']);
    }

    /** @test */
    public function it_handles_missing_distribution_in_report()
    {
        $command = new QuarterlyBonusDistributionCommand($this->profitDistributionService);
        $report = $command->generateQuarterlyDistributionReport('2023-4');

        $this->assertArrayHasKey('error', $report);
        $this->assertStringContainsString('No quarterly bonus distribution found', $report['error']);
    }

    /** @test */
    public function it_displays_performance_metrics()
    {
        $bonusData = [
            'quarter' => '2023-4',
            'quarter_start' => Carbon::createFromDate(2023, 10, 1),
            'quarter_end' => Carbon::createFromDate(2023, 12, 31),
            'total_quarterly_profit' => 50000,
            'bonus_pool_percentage' => 7.5,
            'bonus_pool' => 3750,
            'total_investment_pool' => 10000,
            'total_allocated' => 3750,
            'allocation_count' => 1,
            'allocations' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 375,
                    'investment_amount' => 1000,
                    'pool_percentage' => 10.0,
                    'tier' => 'Basic',
                    'eligibility_status' => 'eligible'
                ]
            ],
            'remaining_pool' => 0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->andReturn($bonusData);

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--dry-run' => true,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Bonus Pool Percentage', Artisan::output());
        $this->assertStringContainsString('Total Bonus Pool', Artisan::output());
        $this->assertStringContainsString('Number of Recipients', Artisan::output());
    }

    /** @test */
    public function it_logs_command_execution()
    {
        $bonusData = [
            'quarter' => '2023-4',
            'quarter_start' => Carbon::createFromDate(2023, 10, 1),
            'quarter_end' => Carbon::createFromDate(2023, 12, 31),
            'total_quarterly_profit' => 50000,
            'bonus_pool_percentage' => 7.5,
            'bonus_pool' => 3750,
            'total_investment_pool' => 10000,
            'total_allocated' => 3750,
            'allocation_count' => 1,
            'allocations' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 375,
                    'investment_amount' => 1000,
                    'pool_percentage' => 10.0,
                    'tier' => 'Basic',
                    'eligibility_status' => 'eligible'
                ]
            ],
            'remaining_pool' => 0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->andReturn($bonusData);

        Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--dry-run' => true,
            '--admin-id' => $this->adminUser->id
        ]);

        // Verify no error logs were created for successful dry run
        Log::shouldNotHaveReceived('error');
    }

    /** @test */
    public function it_handles_command_exceptions()
    {
        $this->profitDistributionService
            ->shouldReceive('calculateQuarterlyBonusPool')
            ->once()
            ->andThrow(new \Exception('Service unavailable'));

        $exitCode = Artisan::call('vbif:distribute-quarterly-bonuses', [
            'quarter' => '2023-4',
            '--quarterly-profit' => 50000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Command failed with exception', Artisan::output());
        
        Log::shouldHaveReceived('error')
            ->once()
            ->with('QuarterlyBonusDistributionCommand failed', Mockery::type('array'));
    }
}