<?php

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\ProjectManagementService;
use App\Application\UseCases\Community\ProcessProjectContributionUseCase;
use App\Application\UseCases\Community\ProcessProfitDistributionUseCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectVote;
use App\Models\ProjectUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectManagementService $service;
    private ProcessProjectContributionUseCase $processProjectContributionUseCase;
    private ProcessProfitDistributionUseCase $processProfitDistributionUseCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->processProjectContributionUseCase = $this->createMock(ProcessProjectContributionUseCase::class);
        $this->processProfitDistributionUseCase = $this->createMock(ProcessProfitDistributionUseCase::class);
        
        $this->service = new ProjectManagementService(
            $this->processProjectContributionUseCase,
            $this->processProfitDistributionUseCase
        );

        $this->createTiers();
    }

    private function createTiers(): void
    {
        InvestmentTier::factory()->create(['name' => 'Gold Member']);
        InvestmentTier::factory()->create(['name' => 'Diamond Member']);
        InvestmentTier::factory()->create(['name' => 'Elite Member']);
    }

    public function test_create_project_success()
    {
        // Arrange
        $projectData = [
            'name' => 'Solar Farm Project',
            'description' => 'Community solar energy project',
            'type' => 'RENEWABLE_ENERGY',
            'target_amount' => 100000,
            'expected_roi' => 18.0,
            'duration_months' => 24,
            'minimum_contribution' => 2000
        ];

        // Act
        $result = $this->service->createProject($projectData);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertInstanceOf(CommunityProject::class, $result['project']);
        
        $project = $result['project'];
        $this->assertEquals('Solar Farm Project', $project->name);
        $this->assertEquals('RENEWABLE_ENERGY', $project->type);
        $this->assertEquals(100000, $project->target_amount);
        $this->assertEquals(0, $project->current_amount);
        $this->assertEquals('PLANNING', $project->status);
        $this->assertEquals(18.0, $project->expected_roi);
        $this->assertEquals(24, $project->project_duration_months);
        $this->assertEquals(2000, $project->minimum_contribution);
    }

    public function test_process_contribution_delegates_to_use_case()
    {
        // Arrange
        $userId = 1;
        $projectId = 1;
        $amount = 5000.0;
        
        $expectedResult = [
            'success' => true,
            'contribution_id' => 1,
            'contribution_amount' => $amount,
            'project_current_amount' => 5000,
            'project_target_amount' => 50000,
            'funding_percentage' => 10.0
        ];

        $this->processProjectContributionUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($userId, $projectId, $amount)
            ->willReturn($expectedResult);

        // Act
        $result = $this->service->processContribution($userId, $projectId, $amount);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function test_process_project_vote_success()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $user = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        
        $project = CommunityProject::factory()->create([
            'status' => 'PLANNING',
            'target_amount' => 50000
        ]);

        // Create a contribution to increase voting power
        ProjectContribution::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'amount' => 5000
        ]);

        // Act
        $result = $this->service->processProjectVote($user->id, $project->id, 'approve', 'Great project!');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('vote_id', $result);
        $this->assertArrayHasKey('voting_power', $result);
        $this->assertArrayHasKey('voting_result', $result);
        
        // Verify vote was created
        $this->assertDatabaseHas('project_votes', [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'vote_type' => 'approve',
            'comments' => 'Great project!'
        ]);
    }

    public function test_process_project_vote_insufficient_tier()
    {
        // Arrange
        $bronzeTier = InvestmentTier::factory()->create(['name' => 'Bronze Member']);
        $user = User::factory()->create(['investment_tier_id' => $bronzeTier->id]);
        
        $project = CommunityProject::factory()->create(['status' => 'PLANNING']);

        // Act
        $result = $this->service->processProjectVote($user->id, $project->id, 'approve');

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Voting requires Gold tier or higher', $result['error']);
    }

    public function test_process_project_vote_already_voted()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $user = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        
        $project = CommunityProject::factory()->create(['status' => 'PLANNING']);

        // Create existing vote
        ProjectVote::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'vote_type' => 'approve'
        ]);

        // Act
        $result = $this->service->processProjectVote($user->id, $project->id, 'reject');

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('User has already voted on this project', $result['error']);
    }

    public function test_update_project_status_success()
    {
        // Arrange
        $project = CommunityProject::factory()->create(['status' => 'PLANNING']);

        // Act
        $result = $this->service->updateProjectStatus($project->id, 'FUNDING', 'Community approved');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('PLANNING', $result['old_status']);
        $this->assertEquals('FUNDING', $result['new_status']);
        
        // Verify database update
        $project->refresh();
        $this->assertEquals('FUNDING', $project->status);
        $this->assertNotNull($project->status_updated_at);
        
        // Verify project update was created
        $this->assertDatabaseHas('project_updates', [
            'project_id' => $project->id,
            'title' => 'Status changed from PLANNING to FUNDING',
            'update_type' => 'STATUS_CHANGE'
        ]);
    }

    public function test_update_project_status_invalid_transition()
    {
        // Arrange
        $project = CommunityProject::factory()->create(['status' => 'COMPLETED']);

        // Act
        $result = $this->service->updateProjectStatus($project->id, 'PLANNING');

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContains('Invalid status transition', $result['error']);
    }

    public function test_distribute_profits_delegates_to_use_case()
    {
        // Arrange
        $projectId = 1;
        $profitAmount = 10000.0;
        $distributionPeriod = '2024-Q1';
        
        $expectedResult = [
            'success' => true,
            'project_id' => $projectId,
            'profit_amount' => $profitAmount,
            'distribution_period' => $distributionPeriod,
            'total_contributors' => 5,
            'total_distributed' => 9500.0
        ];

        $this->processProfitDistributionUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($projectId, $profitAmount, $distributionPeriod)
            ->willReturn($expectedResult);

        // Act
        $result = $this->service->distributeProfits($projectId, $profitAmount, $distributionPeriod);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    public function test_get_project_dashboard()
    {
        // Arrange
        $project = CommunityProject::factory()->create([
            'name' => 'Test Project',
            'target_amount' => 50000,
            'current_amount' => 25000,
            'contributor_count' => 10
        ]);

        // Create some contributions
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $user1 = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $user2 = User::factory()->create(['investment_tier_id' => $goldTier->id]);

        ProjectContribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user1->id,
            'amount' => 15000
        ]);

        ProjectContribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user2->id,
            'amount' => 10000
        ]);

        // Create some votes
        ProjectVote::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user1->id,
            'vote_type' => 'approve',
            'voting_power' => 1.5
        ]);

        // Mock profit summary
        $this->processProfitDistributionUseCase
            ->expects($this->once())
            ->method('getProjectProfitSummary')
            ->with($project->id)
            ->willReturn([
                'total_profit_distributed' => 5000,
                'roi_percentage' => 20.0
            ]);

        // Act
        $dashboard = $this->service->getProjectDashboard($project->id);

        // Assert
        $this->assertEquals($project->id, $dashboard['project']['id']);
        $this->assertEquals('Test Project', $dashboard['project']['name']);
        $this->assertEquals(50.0, $dashboard['project']['funding_percentage']); // 25000/50000 * 100
        
        $this->assertArrayHasKey('contribution_summary', $dashboard);
        $this->assertArrayHasKey('voting_summary', $dashboard);
        $this->assertArrayHasKey('profit_summary', $dashboard);
        $this->assertArrayHasKey('recent_updates', $dashboard);
        $this->assertArrayHasKey('timeline', $dashboard);
        
        // Check contribution summary
        $contributionSummary = $dashboard['contribution_summary'];
        $this->assertEquals(25000, $contributionSummary['total_amount']);
        $this->assertEquals(2, $contributionSummary['contributor_count']);
        $this->assertEquals(12500, $contributionSummary['average_contribution']);
        $this->assertEquals(15000, $contributionSummary['largest_contribution']);
        
        // Check voting summary
        $votingSummary = $dashboard['voting_summary'];
        $this->assertEquals(1, $votingSummary['total_votes']);
        $this->assertEquals(1.5, $votingSummary['total_voting_power']);
        $this->assertEquals(1, $votingSummary['approve_votes']);
        $this->assertEquals(100.0, $votingSummary['approval_percentage']);
    }

    public function test_get_active_projects()
    {
        // Arrange
        $fundingProject = CommunityProject::factory()->create([
            'name' => 'Funding Project',
            'status' => 'FUNDING',
            'target_amount' => 50000,
            'current_amount' => 25000
        ]);

        $activeProject = CommunityProject::factory()->create([
            'name' => 'Active Project',
            'status' => 'ACTIVE',
            'target_amount' => 100000,
            'current_amount' => 100000
        ]);

        // Create completed project (should not appear)
        CommunityProject::factory()->create([
            'name' => 'Completed Project',
            'status' => 'COMPLETED'
        ]);

        // Act
        $activeProjects = $this->service->getActiveProjects();

        // Assert
        $this->assertCount(2, $activeProjects);
        
        $projectNames = array_column($activeProjects, 'name');
        $this->assertContains('Funding Project', $projectNames);
        $this->assertContains('Active Project', $projectNames);
        $this->assertNotContains('Completed Project', $projectNames);
        
        // Check funding percentages
        $fundingProjectData = collect($activeProjects)->firstWhere('name', 'Funding Project');
        $this->assertEquals(50.0, $fundingProjectData['funding_percentage']);
        
        $activeProjectData = collect($activeProjects)->firstWhere('name', 'Active Project');
        $this->assertEquals(100.0, $activeProjectData['funding_percentage']);
    }

    public function test_get_user_project_summary()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $user = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        
        $project = CommunityProject::factory()->create(['status' => 'ACTIVE']);

        // Create contribution
        ProjectContribution::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'amount' => 5000
        ]);

        // Create vote
        ProjectVote::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'vote_type' => 'approve',
            'voting_power' => 1.5
        ]);

        // Mock use case responses
        $mockContributionSummary = [
            'total_contributed' => 5000,
            'projects_contributed' => 1,
            'monthly_contributions' => 5000
        ];

        $mockProfitHistory = [
            'total_earned' => 1000,
            'pending_amount' => 500,
            'projects_count' => 1
        ];

        $this->processProjectContributionUseCase
            ->expects($this->once())
            ->method('getUserContributionSummary')
            ->with($user->id)
            ->willReturn($mockContributionSummary);

        $this->processProfitDistributionUseCase
            ->expects($this->once())
            ->method('getUserProfitHistory')
            ->with($user->id)
            ->willReturn($mockProfitHistory);

        // Act
        $summary = $this->service->getUserProjectSummary($user->id);

        // Assert
        $this->assertEquals($user->id, $summary['user_id']);
        $this->assertEquals($mockContributionSummary, $summary['contribution_summary']);
        $this->assertEquals($mockProfitHistory, $summary['profit_history']);
        
        // Check voting summary
        $votingSummary = $summary['voting_summary'];
        $this->assertEquals(1, $votingSummary['total_votes']);
        $this->assertEquals(1, $votingSummary['approve_votes']);
        $this->assertEquals(0, $votingSummary['reject_votes']);
        $this->assertEquals(0, $votingSummary['abstain_votes']);
        $this->assertEquals(1.5, $votingSummary['total_voting_power']);
        
        // Check active projects
        $this->assertCount(1, $summary['active_projects']);
        $this->assertEquals($project->id, $summary['active_projects'][0]['id']);
    }

    public function test_voting_power_calculation()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();
        $eliteTier = InvestmentTier::where('name', 'Elite Member')->first();

        $goldUser = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $diamondUser = User::factory()->create(['investment_tier_id' => $diamondTier->id]);
        $eliteUser = User::factory()->create(['investment_tier_id' => $eliteTier->id]);

        $project = CommunityProject::factory()->create(['status' => 'PLANNING']);

        // Create contributions to boost voting power
        ProjectContribution::factory()->create([
            'user_id' => $goldUser->id,
            'project_id' => $project->id,
            'amount' => 10000 // Should add 1.0 voting power
        ]);

        ProjectContribution::factory()->create([
            'user_id' => $diamondUser->id,
            'project_id' => $project->id,
            'amount' => 20000 // Should add 2.0 voting power (capped at 2.0)
        ]);

        // Act
        $goldResult = $this->service->processProjectVote($goldUser->id, $project->id, 'approve');
        $diamondResult = $this->service->processProjectVote($diamondUser->id, $project->id, 'approve');
        $eliteResult = $this->service->processProjectVote($eliteUser->id, $project->id, 'approve');

        // Assert voting power calculations
        $this->assertEquals(2.0, $goldResult['voting_power']); // 1.0 (base) + 1.0 (contribution)
        $this->assertEquals(4.0, $diamondResult['voting_power']); // 2.0 (base) + 2.0 (contribution)
        $this->assertEquals(3.0, $eliteResult['voting_power']); // 3.0 (base) + 0 (no contribution)
    }
}