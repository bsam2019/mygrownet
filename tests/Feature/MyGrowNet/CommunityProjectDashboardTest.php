<?php

namespace Tests\Feature\MyGrowNet;

use Tests\TestCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectProfitDistribution;
use App\Models\ProjectVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CommunityProjectDashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private InvestmentTier $tier;
    private CommunityProject $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->tier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'minimum_investment' => 1000,
            'team_volume_requirement' => 3000
        ]);
        
        $this->project = CommunityProject::factory()->create([
            'name' => 'Agricultural Development Project',
            'type' => 'agriculture',
            'status' => 'funding',
            'target_amount' => 50000,
            'current_amount' => 25000,
            'expected_annual_return' => 12.5,
            'minimum_contribution' => 500,
            'funding_start_date' => now()->subDays(10),
            'funding_end_date' => now()->addDays(20),
            'requires_voting' => true
        ]);
        
        $this->user->currentMembershipTier()->associate($this->tier);
        $this->user->save();
    }

    public function test_dashboard_displays_community_project_data()
    {
        // Create project contribution
        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 2000,
            'status' => 'confirmed',
            'contributed_at' => now()->subDays(5)
        ]);

        // Create profit distribution
        ProjectProfitDistribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'distribution_amount' => 150,
            'status' => 'paid',
            'distribution_date' => now()->subDays(2)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('MyGrowNet/Dashboard')
                ->has('communityProjectData.portfolio_summary')
                ->has('communityProjectData.recent_contributions')
                ->has('communityProjectData.recent_distributions')
                ->where('communityProjectData.portfolio_summary.total_projects', 1)
                ->where('communityProjectData.portfolio_summary.total_contributed', 2000)
                ->where('communityProjectData.portfolio_summary.total_returns_received', 150)
            );
    }

    public function test_dashboard_shows_available_projects_for_user_tier()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $communityProjectData = $response->viewData('page')['props']['communityProjectData'];
        
        $this->assertArrayHasKey('available_projects', $communityProjectData);
        $this->assertNotEmpty($communityProjectData['available_projects']);
        
        $availableProject = $communityProjectData['available_projects'][0];
        $this->assertEquals('Agricultural Development Project', $availableProject['name']);
        $this->assertEquals(12.5, $availableProject['expected_annual_return']);
        $this->assertTrue($availableProject['user_can_contribute']);
    }

    public function test_dashboard_displays_pending_votes()
    {
        // Create contribution so user can vote
        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 1000,
            'status' => 'confirmed'
        ]);

        // Create a voting session
        ProjectVote::factory()->create([
            'user_id' => $this->project->created_by,
            'community_project_id' => $this->project->id,
            'vote_type' => 'milestone_approval',
            'vote_subject' => 'Approve Phase 1 Completion',
            'vote_session_id' => 'session-123',
            'vote_deadline' => now()->addDays(5)->toDateString()
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $communityProjectData = $response->viewData('page')['props']['communityProjectData'];
        
        $this->assertArrayHasKey('pending_votes', $communityProjectData);
        $this->assertNotEmpty($communityProjectData['pending_votes']);
        
        $pendingVote = $communityProjectData['pending_votes'][0];
        $this->assertEquals('Approve Phase 1 Completion', $pendingVote['vote_subject']);
        $this->assertEquals('milestone_approval', $pendingVote['vote_type']);
    }

    public function test_dashboard_shows_investment_trends()
    {
        // Create contributions over multiple months
        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 1000,
            'status' => 'confirmed',
            'contributed_at' => now()->subMonths(2)
        ]);

        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 1500,
            'status' => 'confirmed',
            'contributed_at' => now()->subMonth()
        ]);

        // Create distributions
        ProjectProfitDistribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'distribution_amount' => 100,
            'status' => 'paid',
            'distribution_date' => now()->subMonth()
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $communityProjectData = $response->viewData('page')['props']['communityProjectData'];
        
        $this->assertArrayHasKey('investment_trends', $communityProjectData);
        $this->assertCount(6, $communityProjectData['investment_trends']); // 6 months of data
        
        // Verify trend data structure
        foreach ($communityProjectData['investment_trends'] as $trend) {
            $this->assertArrayHasKey('month', $trend);
            $this->assertArrayHasKey('contributions', $trend);
            $this->assertArrayHasKey('returns', $trend);
            $this->assertArrayHasKey('net_flow', $trend);
        }
    }

    public function test_community_project_data_api_endpoint()
    {
        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 1000,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.community-project-data'));

        $response->assertOk()
            ->assertJsonStructure([
                'portfolio_summary' => [
                    'total_contributed',
                    'total_returns_received',
                    'net_roi',
                    'active_projects',
                    'total_projects'
                ],
                'recent_contributions',
                'recent_distributions',
                'available_projects',
                'pending_votes',
                'investment_trends',
                'voting_opportunities',
                'project_alerts'
            ]);
    }

    public function test_contribute_to_project_api()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('mygrownet.api.project-contribute', $this->project), [
                'amount' => 1000,
                'payment_method' => 'internal_balance',
                'payment_details' => []
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Contribution processed successfully'
            ]);

        // Verify contribution was created
        $this->assertDatabaseHas('project_contributions', [
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 1000
        ]);
    }

    public function test_cast_project_vote_api()
    {
        // Create contribution so user can vote
        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 1000,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('mygrownet.api.project-vote', $this->project), [
                'session_id' => 'session-123',
                'vote' => 'yes',
                'comments' => 'I support this milestone'
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Vote cast successfully'
            ]);

        // Verify vote was recorded
        $this->assertDatabaseHas('project_votes', [
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'vote_session_id' => 'session-123',
            'vote' => 'yes'
        ]);
    }

    public function test_project_analytics_api_requires_contribution()
    {
        // Test without contribution - should be denied
        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.project-analytics', $this->project));

        $response->assertStatus(403);

        // Create contribution and test again
        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 1000,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('mygrownet.api.project-analytics', $this->project));

        $response->assertOk()
            ->assertJsonStructure([
                'funding_statistics',
                'timeline_statistics',
                'financial_performance',
                'community_engagement'
            ]);
    }

    public function test_dashboard_shows_project_alerts()
    {
        // Create contribution to project nearing deadline
        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 1000,
            'status' => 'confirmed'
        ]);

        // Update project to have deadline in 3 days
        $this->project->update([
            'funding_end_date' => now()->addDays(3)
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $communityProjectData = $response->viewData('page')['props']['communityProjectData'];
        
        $this->assertArrayHasKey('project_alerts', $communityProjectData);
        $this->assertNotEmpty($communityProjectData['project_alerts']);
        
        $alert = $communityProjectData['project_alerts'][0];
        $this->assertEquals('funding_deadline', $alert['type']);
        $this->assertEquals('high', $alert['priority']);
    }

    public function test_dashboard_handles_users_without_project_investments()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $communityProjectData = $response->viewData('page')['props']['communityProjectData'];
        
        $this->assertEquals(0, $communityProjectData['portfolio_summary']['total_projects']);
        $this->assertEquals(0, $communityProjectData['portfolio_summary']['total_contributed']);
        $this->assertEquals(0, $communityProjectData['portfolio_summary']['total_returns_received']);
        $this->assertEmpty($communityProjectData['recent_contributions']);
        $this->assertEmpty($communityProjectData['recent_distributions']);
    }

    public function test_dashboard_calculates_roi_correctly()
    {
        // Create contribution
        ProjectContribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'amount' => 2000,
            'status' => 'confirmed'
        ]);

        // Create profit distribution
        ProjectProfitDistribution::factory()->create([
            'user_id' => $this->user->id,
            'community_project_id' => $this->project->id,
            'distribution_amount' => 300,
            'status' => 'paid'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mygrownet.dashboard'));

        $response->assertOk();
        
        $communityProjectData = $response->viewData('page')['props']['communityProjectData'];
        
        // ROI should be (300 / 2000) * 100 = 15%
        $this->assertEquals(15.0, $communityProjectData['portfolio_summary']['net_roi']);
    }
}