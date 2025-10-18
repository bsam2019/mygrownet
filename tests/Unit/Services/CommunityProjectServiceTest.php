<?php

namespace Tests\Unit\Services;

use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectVote;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Services\CommunityProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommunityProjectService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CommunityProjectService();
    }

    public function test_get_projects_for_tier_returns_accessible_projects(): void
    {
        // Create projects for different tiers
        $bronzeProject = CommunityProject::factory()->create([
            'required_membership_tiers' => ['Bronze'],
            'status' => 'funding'
        ]);
        
        $goldProject = CommunityProject::factory()->create([
            'required_membership_tiers' => ['Gold', 'Diamond', 'Elite'],
            'status' => 'funding'
        ]);
        
        $allTiersProject = CommunityProject::factory()->create([
            'required_membership_tiers' => [],
            'status' => 'funding'
        ]);

        // Bronze tier should see Bronze and all-tiers projects
        $bronzeProjects = $this->service->getProjectsForTier('Bronze');
        $this->assertCount(2, $bronzeProjects);
        $this->assertTrue($bronzeProjects->contains($bronzeProject));
        $this->assertTrue($bronzeProjects->contains($allTiersProject));
        $this->assertFalse($bronzeProjects->contains($goldProject));
    }

    public function test_create_project_creates_new_community_project(): void
    {
        $creator = User::factory()->create();
        
        $projectData = [
            'name' => 'Test Community Project',
            'description' => 'A test project for the community',
            'type' => 'sme',
            'category' => 'business_venture',
            'target_amount' => 100000,
            'minimum_contribution' => 1000,
            'expected_annual_return' => 12.5,
            'project_duration_months' => 24,
            'funding_start_date' => now()->addDays(7),
            'funding_end_date' => now()->addDays(37),
            'risk_level' => 'medium'
        ];

        $project = $this->service->createProject($creator, $projectData);

        $this->assertInstanceOf(CommunityProject::class, $project);
        $this->assertEquals('Test Community Project', $project->name);
        $this->assertEquals('test-community-project', $project->slug);
        $this->assertEquals($creator->id, $project->created_by);
        $this->assertEquals('planning', $project->status);
        $this->assertEquals(100000, $project->target_amount);
    }

    public function test_process_contribution_creates_contribution_and_transaction(): void
    {
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver']);
        $user = User::factory()->create(['current_investment_tier_id' => $silverTier->id]);
        
        $project = CommunityProject::factory()->create([
            'status' => 'funding',
            'funding_start_date' => now()->subDays(1),
            'funding_end_date' => now()->addDays(30),
            'minimum_contribution' => 500,
            'required_membership_tiers' => ['Silver', 'Gold']
        ]);

        $contribution = $this->service->processContribution($user, $project, 5000);

        $this->assertInstanceOf(ProjectContribution::class, $contribution);
        $this->assertEquals($user->id, $contribution->user_id);
        $this->assertEquals($project->id, $contribution->community_project_id);
        $this->assertEquals(5000, $contribution->amount);
        $this->assertEquals('Silver', $contribution->tier_at_contribution);
        
        // Check that transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 5000,
            'transaction_type' => 'project_contribution'
        ]);
    }

    public function test_cannot_contribute_to_inaccessible_project(): void
    {
        $bronzeTier = InvestmentTier::factory()->create(['name' => 'Bronze']);
        $user = User::factory()->create(['current_investment_tier_id' => $bronzeTier->id]);
        
        $project = CommunityProject::factory()->create([
            'status' => 'funding',
            'required_membership_tiers' => ['Gold', 'Diamond', 'Elite']
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User cannot contribute to this project at this time.');

        $this->service->processContribution($user, $project, 5000);
    }

    public function test_create_voting_session_creates_vote_session(): void
    {
        $project = CommunityProject::factory()->create();
        
        $sessionId = $this->service->createVotingSession(
            $project,
            'milestone',
            'Approve Phase 1 Completion',
            'Vote to approve the completion of Phase 1 milestones',
            7
        );

        $this->assertNotEmpty($sessionId);
        $this->assertStringStartsWith('VOTE-', $sessionId);
        
        // Check that initial vote record was created
        $this->assertDatabaseHas('project_votes', [
            'community_project_id' => $project->id,
            'vote_session_id' => $sessionId,
            'vote_type' => 'milestone',
            'vote_subject' => 'Approve Phase 1 Completion'
        ]);
    }

    public function test_cast_vote_creates_user_vote(): void
    {
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver']);
        $user = User::factory()->create(['current_investment_tier_id' => $silverTier->id]);
        
        $project = CommunityProject::factory()->create([
            'required_membership_tiers' => ['Silver']
        ]);

        // Create a contribution so user can vote
        ProjectContribution::factory()->create([
            'user_id' => $user->id,
            'community_project_id' => $project->id,
            'amount' => 5000,
            'status' => 'confirmed'
        ]);

        $sessionId = $this->service->createVotingSession($project, 'approval', 'Project Approval');
        
        $vote = $this->service->castVote($user, $project, $sessionId, 'yes', 'I support this project');

        $this->assertInstanceOf(ProjectVote::class, $vote);
        $this->assertEquals($user->id, $vote->user_id);
        $this->assertEquals($project->id, $vote->community_project_id);
        $this->assertEquals('yes', $vote->vote);
        $this->assertEquals('I support this project', $vote->voter_comments);
        $this->assertEquals(5000, $vote->contribution_amount);
    }

    public function test_calculate_profit_distributions_creates_distributions(): void
    {
        $project = CommunityProject::factory()->create([
            'status' => 'active',
            'current_amount' => 50000,
            'expected_annual_return' => 15
        ]);

        // Create some contributions
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        ProjectContribution::factory()->create([
            'user_id' => $user1->id,
            'community_project_id' => $project->id,
            'amount' => 30000,
            'status' => 'confirmed'
        ]);
        
        ProjectContribution::factory()->create([
            'user_id' => $user2->id,
            'community_project_id' => $project->id,
            'amount' => 20000,
            'status' => 'confirmed'
        ]);

        $distributions = $this->service->calculateProfitDistributions($project, 10000, 'quarterly');

        $this->assertCount(2, $distributions);
        
        // User1 should get 60% of distributions (30k/50k)
        $user1Distribution = collect($distributions)->firstWhere('user_id', $user1->id);
        $user2Distribution = collect($distributions)->firstWhere('user_id', $user2->id);
        
        $this->assertEquals(6000, $user1Distribution->distribution_amount); // 60% of 10000
        $this->assertEquals(4000, $user2Distribution->distribution_amount); // 40% of 10000
    }

    public function test_get_user_project_portfolio_returns_portfolio_summary(): void
    {
        $user = User::factory()->create();
        
        $project1 = CommunityProject::factory()->create(['status' => 'active']);
        $project2 = CommunityProject::factory()->create(['status' => 'completed']);
        
        // Create contributions
        ProjectContribution::factory()->create([
            'user_id' => $user->id,
            'community_project_id' => $project1->id,
            'amount' => 10000,
            'status' => 'confirmed',
            'total_returns_received' => 1500
        ]);
        
        ProjectContribution::factory()->create([
            'user_id' => $user->id,
            'community_project_id' => $project2->id,
            'amount' => 5000,
            'status' => 'confirmed',
            'total_returns_received' => 1000
        ]);

        $portfolio = $this->service->getUserProjectPortfolio($user);

        $this->assertEquals(15000, $portfolio['total_contributed']);
        $this->assertEquals(2500, $portfolio['total_returns_received']);
        $this->assertEquals(16.67, round($portfolio['net_roi'], 2)); // (2500/15000)*100
        $this->assertEquals(1, $portfolio['active_projects']);
        $this->assertEquals(1, $portfolio['completed_projects']);
        $this->assertEquals(2, $portfolio['total_projects']);
    }

    public function test_get_project_analytics_returns_comprehensive_statistics(): void
    {
        $project = CommunityProject::factory()->create([
            'target_amount' => 100000,
            'current_amount' => 75000,
            'total_contributors' => 5,
            'expected_annual_return' => 12
        ]);

        // Create some contributions
        ProjectContribution::factory()->count(5)->create([
            'community_project_id' => $project->id,
            'status' => 'confirmed'
        ]);

        $analytics = $this->service->getProjectAnalytics($project);

        $this->assertArrayHasKey('funding_statistics', $analytics);
        $this->assertArrayHasKey('timeline_statistics', $analytics);
        $this->assertArrayHasKey('financial_performance', $analytics);
        $this->assertArrayHasKey('community_engagement', $analytics);

        $this->assertEquals(100000, $analytics['funding_statistics']['target_amount']);
        $this->assertEquals(75000, $analytics['funding_statistics']['current_amount']);
        $this->assertEquals(75, $analytics['funding_statistics']['funding_progress']);
        $this->assertEquals(5, $analytics['funding_statistics']['total_contributors']);
    }
}