<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\AnnualProfitDistributionCommand;
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

class AnnualProfitDistributionCommandTest extends TestCase
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
        $distributionData = [
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_fund_profit' => 100000,
            'total_investment_pool' => 50000,
            'total_distributed' => 5000,
            'distribution_count' => 1,
            'distributions' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 30,
                    'investment_amount' => 1000,
                    'tier' => 'Basic',
                    'tier_rate' => 3.0
                ]
            ],
            'distribution_percentage' => 5.0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateAnnualProfitDistribution')
            ->once()
            ->with(100000.0, Mockery::type(Carbon::class))
            ->andReturn($distributionData);

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
            '--dry-run' => true,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Dry run completed', Artisan::output());
    }

    /** @test */
    public function it_validates_year_input()
    {
        $futureYear = now()->addYear()->year;

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => $futureYear,
            '--total-profit' => 100000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Cannot process distribution for future year', Artisan::output());
    }

    /** @test */
    public function it_validates_total_profit_input()
    {
        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => -1000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Total profit must be a positive number', Artisan::output());
    }

    /** @test */
    public function it_validates_admin_id()
    {
        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
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
            'distribution_type' => 'annual',
            'distribution_date' => Carbon::createFromDate(2023, 12, 31)
        ]);

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
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
            'distribution_type' => 'annual',
            'distribution_date' => Carbon::createFromDate(2023, 12, 31)
        ]);

        $distributionData = [
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_fund_profit' => 100000,
            'total_investment_pool' => 50000,
            'total_distributed' => 5000,
            'distribution_count' => 1,
            'distributions' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 30,
                    'investment_amount' => 1000,
                    'tier' => 'Basic',
                    'tier_rate' => 3.0
                ]
            ],
            'distribution_percentage' => 5.0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateAnnualProfitDistribution')
            ->once()
            ->andReturn($distributionData);

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
            '--force' => true,
            '--dry-run' => true,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_processes_successful_distribution()
    {
        $distributionData = [
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_fund_profit' => 100000,
            'total_investment_pool' => 50000,
            'total_distributed' => 5000,
            'distribution_count' => 1,
            'distributions' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 30,
                    'investment_amount' => 1000,
                    'tier' => 'Basic',
                    'tier_rate' => 3.0
                ]
            ],
            'distribution_percentage' => 5.0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateAnnualProfitDistribution')
            ->once()
            ->andReturn($distributionData);

        // Create the distribution record that would be created by the job
        $distribution = ProfitDistribution::factory()->create([
            'distribution_type' => 'annual',
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_amount' => 5000,
            'user_count' => 1,
            'created_by' => $this->adminUser->id,
            'status' => 'processed'
        ]);

        // Mock the service method that would be called by the job
        $this->profitDistributionService
            ->shouldReceive('processAnnualProfitDistribution')
            ->once()
            ->andReturn([
                'success' => true,
                'distribution_id' => $distribution->id,
                'total_distributed' => 5000,
                'user_count' => 1
            ]);

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
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
        $distributionData = [
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_fund_profit' => 100000,
            'total_investment_pool' => 50000,
            'total_distributed' => 5000,
            'distribution_count' => 1,
            'distributions' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 30,
                    'investment_amount' => 1000,
                    'tier' => 'Basic',
                    'tier_rate' => 3.0
                ]
            ],
            'distribution_percentage' => 5.0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateAnnualProfitDistribution')
            ->once()
            ->andReturn($distributionData);

        $this->profitDistributionService
            ->shouldReceive('processAnnualProfitDistribution')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'Database connection failed'
            ]);

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
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
        $distributionData = [
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_fund_profit' => 100000,
            'total_investment_pool' => 0,
            'total_distributed' => 0,
            'distribution_count' => 0,
            'distributions' => [],
            'distribution_percentage' => 0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateAnnualProfitDistribution')
            ->once()
            ->andReturn($distributionData);

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('No eligible users found', Artisan::output());
    }

    /** @test */
    public function it_uses_previous_year_as_default()
    {
        $previousYear = now()->subYear()->year;
        
        $distributionData = [
            'distribution_date' => Carbon::createFromDate($previousYear, 12, 31),
            'total_fund_profit' => 100000,
            'total_investment_pool' => 50000,
            'total_distributed' => 5000,
            'distribution_count' => 1,
            'distributions' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 30,
                    'investment_amount' => 1000,
                    'tier' => 'Basic',
                    'tier_rate' => 3.0
                ]
            ],
            'distribution_percentage' => 5.0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateAnnualProfitDistribution')
            ->once()
            ->with(100000.0, Mockery::on(function ($date) use ($previousYear) {
                return $date->year === $previousYear;
            }))
            ->andReturn($distributionData);

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            '--total-profit' => 100000,
            '--dry-run' => true,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString("year {$previousYear}", Artisan::output());
    }

    /** @test */
    public function it_generates_distribution_report()
    {
        $distribution = ProfitDistribution::factory()->create([
            'distribution_type' => 'annual',
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_amount' => 5000,
            'user_count' => 1,
            'fund_profit' => 100000,
            'status' => 'processed'
        ]);

        $command = new AnnualProfitDistributionCommand($this->profitDistributionService);
        $report = $command->generateDistributionReport(2023);

        $this->assertArrayHasKey('distribution_summary', $report);
        $this->assertArrayHasKey('tier_breakdown', $report);
        $this->assertArrayHasKey('payment_status', $report);
        $this->assertEquals($distribution->id, $report['distribution_summary']['id']);
        $this->assertEquals(2023, $report['distribution_summary']['year']);
    }

    /** @test */
    public function it_handles_missing_distribution_in_report()
    {
        $command = new AnnualProfitDistributionCommand($this->profitDistributionService);
        $report = $command->generateDistributionReport(2023);

        $this->assertArrayHasKey('error', $report);
        $this->assertStringContainsString('No annual distribution found', $report['error']);
    }

    /** @test */
    public function it_logs_command_execution()
    {
        $distributionData = [
            'distribution_date' => Carbon::createFromDate(2023, 12, 31),
            'total_fund_profit' => 100000,
            'total_investment_pool' => 50000,
            'total_distributed' => 5000,
            'distribution_count' => 1,
            'distributions' => [
                [
                    'user_id' => $this->investorUser->id,
                    'user_name' => $this->investorUser->name,
                    'amount' => 30,
                    'investment_amount' => 1000,
                    'tier' => 'Basic',
                    'tier_rate' => 3.0
                ]
            ],
            'distribution_percentage' => 5.0
        ];

        $this->profitDistributionService
            ->shouldReceive('calculateAnnualProfitDistribution')
            ->once()
            ->andReturn($distributionData);

        Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
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
            ->shouldReceive('calculateAnnualProfitDistribution')
            ->once()
            ->andThrow(new \Exception('Service unavailable'));

        $exitCode = Artisan::call('vbif:distribute-annual-profits', [
            'year' => 2023,
            '--total-profit' => 100000,
            '--admin-id' => $this->adminUser->id
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Command failed with exception', Artisan::output());
        
        Log::shouldHaveReceived('error')
            ->once()
            ->with('AnnualProfitDistributionCommand failed', Mockery::type('array'));
    }
}