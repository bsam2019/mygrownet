<?php

namespace Tests\Feature\MyGrowNet;

use Tests\TestCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AssetTrackingDashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private InvestmentTier $tier;
    private PhysicalReward $reward;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->tier = InvestmentTier::factory()->create([
            'name' => 'Gold',
            'minimum_investment' => 2000,
            'team_volume_requirement' => 5000
        ]);
        
        $this->reward = PhysicalReward::factory()->create([
            'name' => 'Business Vehicle',
            'category' => 'vehicle',
            'estimated_value' => 15000,
            'income_generating' => true,
            'estimated_monthly_income' => 500,
            'requires_performance_maintenance' => true,
            'maintenance_period_months' => 12
        ]);
        
        $this->user->currentMembershipTier()->associate($this->tier);
        $this->user->save();
    }

    public function test_dashboard_displays_asset_tracking_data()
    {
        // Create asset allocation
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'tier_id' => $this->tier->id,
            'status' => 'delivered',
            'total_income_generated' => 2500,
            'monthly_income_average' => 450,
            'maintenance_compliant' => true,
            'delivered_at' => now()->subMonths(6)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('MyGrowNet/Dashboard')
                ->has('assetData.summary')
                ->has('assetData.assets')
                ->where('assetData.summary.total_assets', 1)
                ->where('assetData.summary.active_assets', 1)
                ->where('assetData.summary.total_income_generated', 2500)
            );
    }

    public function test_dashboard_shows_asset_income_visualization()
    {
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'status' => 'delivered',
            'total_income_generated' => 1500,
            'monthly_income_average' => 300,
            'delivered_at' => now()->subMonths(3)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $assetData = $response->viewData('page')['props']['assetData'];
        
        $this->assertArrayHasKey('income_trends', $assetData);
        $this->assertArrayHasKey('summary', $assetData);
        $this->assertEquals(1500, $assetData['summary']['total_income_generated']);
        $this->assertEquals(300, $assetData['summary']['monthly_income_average']);
    }

    public function test_dashboard_displays_maintenance_alerts()
    {
        // Create allocation with maintenance due
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'status' => 'delivered',
            'maintenance_compliant' => true,
            'last_maintenance_check' => now()->subMonths(2), // Overdue
            'delivered_at' => now()->subMonths(6)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $assetData = $response->viewData('page')['props']['assetData'];
        
        $this->assertArrayHasKey('maintenance_alerts', $assetData);
        $this->assertNotEmpty($assetData['maintenance_alerts']);
        
        $alert = $assetData['maintenance_alerts'][0];
        $this->assertEquals('maintenance_due', $alert['type']);
        $this->assertEquals('Business Vehicle', $alert['asset_name']);
    }

    public function test_dashboard_shows_ownership_opportunities()
    {
        // Create allocation eligible for ownership transfer
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'status' => 'delivered',
            'maintenance_compliant' => true,
            'maintenance_months_completed' => 12, // Completed required months
            'delivered_at' => now()->subMonths(12)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $assetData = $response->viewData('page')['props']['assetData'];
        
        $this->assertArrayHasKey('ownership_opportunities', $assetData);
        $this->assertNotEmpty($assetData['ownership_opportunities']);
        
        $opportunity = $assetData['ownership_opportunities'][0];
        $this->assertEquals('Business Vehicle', $opportunity['asset_name']);
        $this->assertEquals(15000, $opportunity['estimated_value']);
    }

    public function test_asset_tracking_api_endpoint()
    {
        PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'status' => 'delivered',
            'total_income_generated' => 1000,
            'monthly_income_average' => 200
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.asset-tracking-data'));

        $response->assertOk()
            ->assertJsonStructure([
                'summary' => [
                    'total_assets',
                    'active_assets',
                    'total_income_generated',
                    'monthly_income_average',
                    'assets_pending_ownership'
                ],
                'assets',
                'income_breakdown',
                'performance_metrics',
                'maintenance_alerts',
                'ownership_opportunities',
                'recommendations'
            ]);
    }

    public function test_asset_performance_analytics_api()
    {
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'status' => 'delivered',
            'total_income_generated' => 2000,
            'monthly_income_average' => 400,
            'delivered_at' => now()->subMonths(5)
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.asset-performance', $allocation));

        $response->assertOk()
            ->assertJsonStructure([
                'asset_info' => [
                    'name',
                    'category',
                    'estimated_value',
                    'estimated_monthly_income'
                ],
                'performance' => [
                    'months_active',
                    'expected_income',
                    'actual_income',
                    'performance_ratio',
                    'status'
                ],
                'maintenance',
                'income_history',
                'projections'
            ]);
    }

    public function test_record_asset_income_api()
    {
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'status' => 'delivered',
            'total_income_generated' => 1000
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('mygrownet.api.record-asset-income', $allocation), [
                'amount' => 250,
                'source' => 'Monthly rental income',
                'date' => now()->toDateString()
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Income recorded successfully'
            ]);

        // Verify income was recorded
        $allocation->refresh();
        $this->assertEquals(1250, $allocation->total_income_generated);
    }

    public function test_update_asset_maintenance_api()
    {
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'status' => 'delivered',
            'maintenance_compliant' => false,
            'maintenance_months_completed' => 5
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson(route('mygrownet.api.update-asset-maintenance', $allocation), [
                'compliant' => true,
                'notes' => 'All maintenance requirements met this month'
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Maintenance status updated successfully'
            ]);

        // Verify maintenance was updated
        $allocation->refresh();
        $this->assertTrue($allocation->maintenance_compliant);
        $this->assertEquals(6, $allocation->maintenance_months_completed);
    }

    public function test_unauthorized_access_to_other_user_assets()
    {
        $otherUser = User::factory()->create();
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $otherUser->id,
            'physical_reward_id' => $this->reward->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.asset-performance', $allocation));

        $response->assertStatus(403);
    }

    public function test_dashboard_handles_users_without_assets()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $assetData = $response->viewData('page')['props']['assetData'];
        
        $this->assertEquals(0, $assetData['summary']['total_assets']);
        $this->assertEquals(0, $assetData['summary']['active_assets']);
        $this->assertEquals(0, $assetData['summary']['total_income_generated']);
        $this->assertEmpty($assetData['assets']);
    }

    public function test_asset_income_trends_calculation()
    {
        // Create allocation with income history
        $allocation = PhysicalRewardAllocation::factory()->create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'status' => 'delivered',
            'monthly_income_average' => 300,
            'delivered_at' => now()->subMonths(8)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $assetData = $response->viewData('page')['props']['assetData'];
        
        $this->assertArrayHasKey('income_trends', $assetData);
        $this->assertCount(6, $assetData['income_trends']); // 6 months of data
        
        // Verify trend data structure
        foreach ($assetData['income_trends'] as $trend) {
            $this->assertArrayHasKey('month', $trend);
            $this->assertArrayHasKey('income', $trend);
            $this->assertArrayHasKey('asset_count', $trend);
        }
    }
}