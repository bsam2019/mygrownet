<?php

namespace Tests\Unit\Models;

use App\Models\ProfitDistribution;
use App\Models\ProfitShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ProfitDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_many_profit_shares(): void
    {
        $distribution = ProfitDistribution::factory()->create();
        
        ProfitShare::factory()->count(3)->create([
            'profit_distribution_id' => $distribution->id,
        ]);
        
        $this->assertCount(3, $distribution->profitShares);
        $this->assertInstanceOf(ProfitShare::class, $distribution->profitShares->first());
    }

    public function test_annual_scope(): void
    {
        ProfitDistribution::factory()->create(['period_type' => 'annual']);
        ProfitDistribution::factory()->create(['period_type' => 'quarterly']);
        ProfitDistribution::factory()->create(['period_type' => 'annual']);
        
        $annualDistributions = ProfitDistribution::annual()->get();
        
        $this->assertCount(2, $annualDistributions);
        $annualDistributions->each(function ($distribution) {
            $this->assertEquals('annual', $distribution->period_type);
        });
    }

    public function test_quarterly_scope(): void
    {
        ProfitDistribution::factory()->create(['period_type' => 'annual']);
        ProfitDistribution::factory()->create(['period_type' => 'quarterly']);
        ProfitDistribution::factory()->create(['period_type' => 'quarterly']);
        
        $quarterlyDistributions = ProfitDistribution::quarterly()->get();
        
        $this->assertCount(2, $quarterlyDistributions);
        $quarterlyDistributions->each(function ($distribution) {
            $this->assertEquals('quarterly', $distribution->period_type);
        });
    }

    public function test_completed_scope(): void
    {
        ProfitDistribution::factory()->create(['status' => 'completed']);
        ProfitDistribution::factory()->create(['status' => 'pending']);
        ProfitDistribution::factory()->create(['status' => 'completed']);
        
        $completedDistributions = ProfitDistribution::completed()->get();
        
        $this->assertCount(2, $completedDistributions);
        $completedDistributions->each(function ($distribution) {
            $this->assertEquals('completed', $distribution->status);
        });
    }

    public function test_pending_scope(): void
    {
        ProfitDistribution::factory()->create(['status' => 'completed']);
        ProfitDistribution::factory()->create(['status' => 'pending']);
        ProfitDistribution::factory()->create(['status' => 'processing']);
        
        $pendingDistributions = ProfitDistribution::pending()->get();
        
        $this->assertCount(1, $pendingDistributions);
        $this->assertEquals('pending', $pendingDistributions->first()->status);
    }

    public function test_for_period_scope(): void
    {
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();
        
        ProfitDistribution::factory()->create([
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);
        
        ProfitDistribution::factory()->create([
            'period_start' => $startDate->copy()->subYear(),
            'period_end' => $endDate->copy()->subYear(),
        ]);
        
        $currentYearDistributions = ProfitDistribution::forPeriod($startDate, $endDate)->get();
        
        $this->assertCount(1, $currentYearDistributions);
        $this->assertTrue($currentYearDistributions->first()->period_start->equalTo($startDate));
    }

    public function test_is_completed_method(): void
    {
        $completedDistribution = ProfitDistribution::factory()->create(['status' => 'completed']);
        $pendingDistribution = ProfitDistribution::factory()->create(['status' => 'pending']);
        
        $this->assertTrue($completedDistribution->isCompleted());
        $this->assertFalse($pendingDistribution->isCompleted());
    }

    public function test_is_pending_method(): void
    {
        $completedDistribution = ProfitDistribution::factory()->create(['status' => 'completed']);
        $pendingDistribution = ProfitDistribution::factory()->create(['status' => 'pending']);
        
        $this->assertFalse($completedDistribution->isPending());
        $this->assertTrue($pendingDistribution->isPending());
    }

    public function test_is_processing_method(): void
    {
        $processingDistribution = ProfitDistribution::factory()->create(['status' => 'processing']);
        $completedDistribution = ProfitDistribution::factory()->create(['status' => 'completed']);
        
        $this->assertTrue($processingDistribution->isProcessing());
        $this->assertFalse($completedDistribution->isProcessing());
    }

    public function test_get_distribution_percentage_method(): void
    {
        $distribution = ProfitDistribution::factory()->create([
            'distribution_percentage' => 75.5,
        ]);
        
        $percentage = $distribution->getDistributionPercentage();
        
        $this->assertEquals(75.5, $percentage);
        $this->assertIsFloat($percentage);
    }

    public function test_get_total_distributed_amount_method(): void
    {
        $distribution = ProfitDistribution::factory()->create();
        
        ProfitShare::factory()->create([
            'profit_distribution_id' => $distribution->id,
            'amount' => 1000,
        ]);
        
        ProfitShare::factory()->create([
            'profit_distribution_id' => $distribution->id,
            'amount' => 1500,
        ]);
        
        $totalDistributed = $distribution->getTotalDistributedAmount();
        
        $this->assertEquals(2500, $totalDistributed);
    }

    public function test_get_participant_count_method(): void
    {
        $distribution = ProfitDistribution::factory()->create();
        
        ProfitShare::factory()->count(5)->create([
            'profit_distribution_id' => $distribution->id,
        ]);
        
        $participantCount = $distribution->getParticipantCount();
        
        $this->assertEquals(5, $participantCount);
    }

    public function test_get_average_share_method(): void
    {
        $distribution = ProfitDistribution::factory()->create();
        
        ProfitShare::factory()->create([
            'profit_distribution_id' => $distribution->id,
            'amount' => 1000,
        ]);
        
        ProfitShare::factory()->create([
            'profit_distribution_id' => $distribution->id,
            'amount' => 2000,
        ]);
        
        ProfitShare::factory()->create([
            'profit_distribution_id' => $distribution->id,
            'amount' => 3000,
        ]);
        
        $averageShare = $distribution->getAverageShare();
        
        $this->assertEquals(2000, $averageShare); // (1000 + 2000 + 3000) / 3
    }

    public function test_get_period_duration_method(): void
    {
        $startDate = Carbon::create(2024, 1, 1);
        $endDate = Carbon::create(2024, 12, 31);
        
        $distribution = ProfitDistribution::factory()->create([
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);
        
        $duration = $distribution->getPeriodDuration();
        
        $this->assertEquals(365, $duration); // Days in 2024 (leap year)
    }

    public function test_mark_as_completed_method(): void
    {
        $distribution = ProfitDistribution::factory()->create(['status' => 'processing']);
        
        $distribution->markAsCompleted();
        
        $this->assertEquals('completed', $distribution->status);
        $this->assertNotNull($distribution->completed_at);
        $this->assertInstanceOf(Carbon::class, $distribution->completed_at);
    }

    public function test_mark_as_processing_method(): void
    {
        $distribution = ProfitDistribution::factory()->create(['status' => 'pending']);
        
        $distribution->markAsProcessing();
        
        $this->assertEquals('processing', $distribution->status);
        $this->assertNotNull($distribution->started_at);
    }

    public function test_mark_as_failed_method(): void
    {
        $distribution = ProfitDistribution::factory()->create(['status' => 'processing']);
        
        $errorMessage = 'Distribution failed due to insufficient funds';
        $distribution->markAsFailed($errorMessage);
        
        $this->assertEquals('failed', $distribution->status);
        $this->assertEquals($errorMessage, $distribution->error_message);
        $this->assertNotNull($distribution->failed_at);
    }

    public function test_get_summary_method(): void
    {
        $distribution = ProfitDistribution::factory()->create([
            'total_profit' => 100000,
            'distribution_percentage' => 80,
        ]);
        
        ProfitShare::factory()->create([
            'profit_distribution_id' => $distribution->id,
            'amount' => 5000,
        ]);
        
        ProfitShare::factory()->create([
            'profit_distribution_id' => $distribution->id,
            'amount' => 7500,
        ]);
        
        $summary = $distribution->getSummary();
        
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_profit', $summary);
        $this->assertArrayHasKey('distribution_percentage', $summary);
        $this->assertArrayHasKey('total_distributed', $summary);
        $this->assertArrayHasKey('participant_count', $summary);
        $this->assertArrayHasKey('average_share', $summary);
        $this->assertArrayHasKey('period_duration_days', $summary);
        
        $this->assertEquals(100000, $summary['total_profit']);
        $this->assertEquals(80, $summary['distribution_percentage']);
        $this->assertEquals(12500, $summary['total_distributed']);
        $this->assertEquals(2, $summary['participant_count']);
        $this->assertEquals(6250, $summary['average_share']);
    }

    public function test_can_be_processed_method(): void
    {
        $pendingDistribution = ProfitDistribution::factory()->create(['status' => 'pending']);
        $completedDistribution = ProfitDistribution::factory()->create(['status' => 'completed']);
        $processingDistribution = ProfitDistribution::factory()->create(['status' => 'processing']);
        
        $this->assertTrue($pendingDistribution->canBeProcessed());
        $this->assertFalse($completedDistribution->canBeProcessed());
        $this->assertFalse($processingDistribution->canBeProcessed());
    }

    public function test_fillable_attributes(): void
    {
        $fillable = [
            'period_type',
            'period_start',
            'period_end',
            'total_profit',
            'distribution_percentage',
            'total_distributed',
            'status',
            'started_at',
            'completed_at',
            'failed_at',
            'error_message',
            'notes',
        ];
        
        $distribution = new ProfitDistribution();
        
        $this->assertEquals($fillable, $distribution->getFillable());
    }

    public function test_casts_attributes_correctly(): void
    {
        $distribution = ProfitDistribution::factory()->create([
            'period_start' => '2024-01-01',
            'period_end' => '2024-12-31',
            'total_profit' => '100000.50',
            'distribution_percentage' => '75.25',
        ]);
        
        $this->assertInstanceOf(Carbon::class, $distribution->period_start);
        $this->assertInstanceOf(Carbon::class, $distribution->period_end);
        $this->assertIsFloat($distribution->total_profit);
        $this->assertIsFloat($distribution->distribution_percentage);
    }

    public function test_validates_period_type_enum(): void
    {
        $validTypes = ['annual', 'quarterly'];
        
        foreach ($validTypes as $type) {
            $distribution = ProfitDistribution::factory()->create(['period_type' => $type]);
            $this->assertEquals($type, $distribution->period_type);
        }
    }

    public function test_validates_status_enum(): void
    {
        $validStatuses = ['pending', 'processing', 'completed', 'failed'];
        
        foreach ($validStatuses as $status) {
            $distribution = ProfitDistribution::factory()->create(['status' => $status]);
            $this->assertEquals($status, $distribution->status);
        }
    }
}