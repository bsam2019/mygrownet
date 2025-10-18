<?php

namespace Tests\Feature\MyGrowNet;

use Tests\TestCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use App\Models\Achievement;
use App\Models\CommunityProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class EnhancedDashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private InvestmentTier $tier;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->tier = InvestmentTier::factory()->create([
            'name' => 'Bronze',
            'minimum_investment' => 500,
            'team_volume_requirement' => 1000
        ]);
        
        $this->user->currentMembershipTier()->associate($this->tier);
        $this->user->save();
    }

    /** @test */
    public function it_displays_enhanced_dashboard_with_mlm_features()
    {
        $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('MyGrowNet/Dashboard')
                ->has('referralStats.levels')
                ->has('teamVolumeData.current_month')
                ->has('networkData.direct_referrals')
                ->has('stats.total_earnings')
            );
    }

    /** @test */
    public function it_shows_five_level_commission_tracking()
    {
        // Create referral commissions for different levels
        for ($level = 1; $level <= 5; $level++) {
            ReferralCommission::factory()->create([
                'user_id' => $this->user->id,
                'level' => $level,
                'amount' => 100 * $level,
                'status' => 'paid'
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $referralStats = $response->viewData('page')['props']['referralStats'];
        
        $this->assertArrayHasKey('levels', $referralStats);
        $this->assertCount(5, $referralStats['levels']);
        
        foreach ($referralStats['levels'] as $level => $data) {
            $this->assertEquals($level, $data['level']);
            $this->assertArrayHasKey('total_earnings', $data);
            $this->assertArrayHasKey('count', $data);
        }
    }

    /** @test */
    public function it_displays_team_volume_visualization()
    {
        TeamVolume::factory()->create([
            'user_id' => $this->user->id,
            'personal_volume' => 500,
            'team_volume' => 2000,
            'left_leg_volume' => 800,
            'right_leg_volume' => 1200,
            'total_volume' => 2500
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $teamVolumeData = $response->viewData('page')['props']['teamVolumeData'];
        
        $this->assertArrayHasKey('current_month', $teamVolumeData);
        $this->assertEquals(500, $teamVolumeData['current_month']['personal_volume']);
        $this->assertEquals(2000, $teamVolumeData['current_month']['team_volume']);
        $this->assertEquals(2500, $teamVolumeData['current_month']['total_volume']);
    }

    /** @test */
    public function it_shows_network_structure_with_multilevel_display()
    {
        // Create direct referrals
        $referral1 = User::factory()->create(['referrer_id' => $this->user->id]);
        $referral2 = User::factory()->create(['referrer_id' => $this->user->id]);
        
        // Create network entries
        UserNetwork::factory()->create([
            'user_id' => $referral1->id,
            'sponsor_id' => $this->user->id,
            'level' => 1
        ]);
        
        UserNetwork::factory()->create([
            'user_id' => $referral2->id,
            'sponsor_id' => $this->user->id,
            'level' => 1
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $networkData = $response->viewData('page')['props']['networkData'];
        
        $this->assertArrayHasKey('direct_referrals', $networkData);
        $this->assertArrayHasKey('total_network_size', $networkData);
        $this->assertArrayHasKey('network_depth', $networkData);
        $this->assertCount(2, $networkData['direct_referrals']);
    }

    /** @test */
    public function it_provides_dashboard_stats_api_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.dashboard-stats'));

        $response->assertOk()
            ->assertJsonStructure([
                'total_earnings',
                'this_month_earnings',
                'team_size',
                'active_team_members',
                'current_tier',
                'tier_progress',
                'commission_levels',
                'team_volume'
            ]);
    }

    /** @test */
    public function it_provides_five_level_commission_data_api()
    {
        // Create commissions for different levels
        for ($level = 1; $level <= 5; $level++) {
            ReferralCommission::factory()->create([
                'user_id' => $this->user->id,
                'level' => $level,
                'amount' => 50 * $level,
                'status' => 'paid'
            ]);
        }

        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.five-level-commission-data'));

        $response->assertOk()
            ->assertJsonStructure([
                'referral_stats' => [
                    'levels'
                ],
                'commission_breakdown',
                'level_performance'
            ]);
            
        $data = $response->json();
        $this->assertCount(5, $data['referral_stats']['levels']);
        $this->assertCount(5, $data['level_performance']);
    }

    /** @test */
    public function it_provides_network_structure_api_with_depth_control()
    {
        // Create multi-level network
        $level1User = User::factory()->create(['referrer_id' => $this->user->id]);
        $level2User = User::factory()->create(['referrer_id' => $level1User->id]);
        
        UserNetwork::factory()->create([
            'user_id' => $level1User->id,
            'sponsor_id' => $this->user->id,
            'level' => 1
        ]);
        
        UserNetwork::factory()->create([
            'user_id' => $level2User->id,
            'sponsor_id' => $this->user->id,
            'level' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.network-structure', ['depth' => 2]));

        $response->assertOk()
            ->assertJsonStructure([
                'network_structure',
                'level_breakdown',
                'growth_metrics' => [
                    'current_month_growth',
                    'growth_rate_percentage',
                    'total_network_size',
                    'active_percentage'
                ]
            ]);
    }

    /** @test */
    public function it_handles_users_without_team_volume_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $teamVolumeData = $response->viewData('page')['props']['teamVolumeData'];
        
        // Should have default values when no team volume data exists
        $this->assertEquals(0, $teamVolumeData['current_month']['personal_volume']);
        $this->assertEquals(0, $teamVolumeData['current_month']['team_volume']);
        $this->assertEquals(0, $teamVolumeData['current_month']['total_volume']);
    }

    /** @test */
    public function it_shows_membership_progress_with_tier_requirements()
    {
        $nextTier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'minimum_investment' => 1000,
            'team_volume_requirement' => 2000,
            'order' => $this->tier->order + 1
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $membershipProgress = $response->viewData('page')['props']['membershipProgress'];
        
        $this->assertEquals('Bronze', $membershipProgress['current_tier']['name']);
        $this->assertEquals('Silver', $membershipProgress['next_tier']['name']);
        $this->assertArrayHasKey('progress_percentage', $membershipProgress);
    }

    /** @test */
    public function it_displays_notifications_for_important_events()
    {
        // Create a new community project to trigger notification
        CommunityProject::factory()->create([
            'status' => 'funding',
            'created_at' => now()->subDays(3)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $notifications = $response->viewData('page')['props']['notifications'];
        
        $this->assertNotEmpty($notifications);
        
        $projectNotification = collect($notifications)->firstWhere('type', 'new_projects');
        $this->assertNotNull($projectNotification);
        $this->assertArrayHasKey('title', $projectNotification);
        $this->assertArrayHasKey('message', $projectNotification);
    }
}