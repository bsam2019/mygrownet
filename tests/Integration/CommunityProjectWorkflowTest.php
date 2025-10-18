<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Application\Services\ProjectManagementService;
use App\Application\Services\CommunityNotificationService;
use App\Application\UseCases\Community\ProcessProjectContributionUseCase;
use App\Application\UseCases\Community\ProcessProfitDistributionUseCase;
use App\Domain\Community\Repositories\ProjectRepository;
use App\Domain\Community\Repositories\ContributionRepository;
use App\Domain\Community\Repositories\ProfitDistributionRepository;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectVote;
use App\Models\ProjectProfitDistribution;
use App\Models\ProjectUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CommunityProjectWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private ProjectManagementService $projectService;
    private CommunityNotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTiers();
        $this->setupServices();
    }

    private function createTiers(): void
    {
        InvestmentTier::factory()->create(['name' => 'Bronze Member']);
        InvestmentTier::factory()->create(['name' => 'Silver Member']);
        InvestmentTier::factory()->create(['name' => 'Gold Member']);
        InvestmentTier::factory()->create(['name' => 'Diamond Member']);
        InvestmentTier::factory()->create(['name' => 'Elite Member']);
    }

    private function setupServices(): void
    {
        // Create mock repositories
        $projectRepo = $this->createMock(ProjectRepository::class);
        $contributionRepo = $this->createMock(ContributionRepository::class);
        $profitDistributionRepo = $this->createMock(ProfitDistributionRepository::class);

        // Create use cases
        $processProjectContributionUseCase = new ProcessProjectContributionUseCase($projectRepo, $contributionRepo);
        $processProfitDistributionUseCase = new ProcessProfitDistributionUseCase($projectRepo, $profitDistributionRepo);

        // Create services
        $this->projectService = new ProjectManagementService(
            $processProjectContributionUseCase,
            $processProfitDistributionUseCase
        );

        $this->notificationService = new CommunityNotificationService();
    }

    public function test_complete_community_project_lifecycle()
    {
        // Phase 1: Project Creation
        $projectData = [
            'name' => 'Community Solar Farm',
            'description' => 'A 50kW solar installation for community energy needs',
            'type' => 'RENEWABLE_ENERGY',
            'target_amount' => 100000,
            'expected_roi' => 20.0,
            'duration_months' => 18,
            'minimum_contribution' => 2000
        ];

        $createResult = $this->projectService->createProject($projectData);
        $this->assertTrue($createResult['success']);
        
        $project = $createResult['project'];
        $this->assertEquals('PLANNING', $project->status);

        // Phase 2: Community Voting
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();
        $eliteTier = InvestmentTier::where('name', 'Elite Member')->first();

        // Create voters
        $voter1 = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $voter2 = User::factory()->create(['investment_tier_id' => $diamondTier->id]);
        $voter3 = User::factory()->create(['investment_tier_id' => $eliteTier->id]);

        // Process votes (need 60% approval)
        $vote1Result = $this->projectService->processProjectVote($voter1->id, $project->id, 'approve');
        $vote2Result = $this->projectService->processProjectVote($voter2->id, $project->id, 'approve');
        $vote3Result = $this->projectService->processProjectVote($voter3->id, $project->id, 'approve');

        $this->assertTrue($vote1Result['success']);
        $this->assertTrue($vote2Result['success']);
        $this->assertTrue($vote3Result['success']);

        // Check if project moved to FUNDING status after sufficient approval
        $project->refresh();
        $this->assertEquals('FUNDING', $project->status);

        // Phase 3: Funding Phase - Collect Contributions
        $contributor1 = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $contributor2 = User::factory()->create(['investment_tier_id' => $diamondTier->id]);
        $contributor3 = User::factory()->create(['investment_tier_id' => $eliteTier->id]);

        // Process contributions
        $contribution1 = $this->projectService->processContribution($contributor1->id, $project->id, 25000);
        $contribution2 = $this->projectService->processContribution($contributor2->id, $project->id, 35000);
        $contribution3 = $this->projectService->processContribution($contributor3->id, $project->id, 40000);

        $this->assertTrue($contribution1['success']);
        $this->assertTrue($contribution2['success']);
        $this->assertTrue($contribution3['success']);

        // Verify project is fully funded and moved to ACTIVE
        $project->refresh();
        $this->assertEquals(100000, $project->current_amount);
        $this->assertEquals('ACTIVE', $project->status);
        $this->assertEquals(3, $project->contributor_count);

        // Phase 4: Project Execution and Updates
        $statusResult = $this->projectService->updateProjectStatus(
            $project->id, 
            'ACTIVE', 
            'Construction phase completed, solar panels installed'
        );
        $this->assertTrue($statusResult['success']);

        // Verify project update was created
        $this->assertDatabaseHas('project_updates', [
            'project_id' => $project->id,
            'update_type' => 'STATUS_CHANGE'
        ]);

        // Phase 5: Profit Distribution
        $distributionResult = $this->projectService->distributeProfits($project->id, 20000, '2024-Q1');
        $this->assertTrue($distributionResult['success']);
        $this->assertEquals(3, $distributionResult['total_contributors']);

        // Verify profit distributions were created
        $distributions = ProjectProfitDistribution::where('project_id', $project->id)->get();
        $this->assertCount(3, $distributions);

        // Check tier-based profit multipliers
        $goldDistribution = $distributions->where('user_id', $contributor1->id)->first();
        $diamondDistribution = $distributions->where('user_id', $contributor2->id)->first();
        $eliteDistribution = $distributions->where('user_id', $contributor3->id)->first();

        $this->assertEquals(1.0, $goldDistribution->tier_multiplier); // Gold: 1.0x
        $this->assertEquals(1.2, $diamondDistribution->tier_multiplier); // Diamond: 1.2x
        $this->assertEquals(1.5, $eliteDistribution->tier_multiplier); // Elite: 1.5x

        // Phase 6: Project Completion
        $completionResult = $this->projectService->updateProjectStatus($project->id, 'COMPLETED');
        $this->assertTrue($completionResult['success']);

        $project->refresh();
        $this->assertEquals('COMPLETED', $project->status);
    }

    public function test_project_voting_threshold_and_rejection()
    {
        // Arrange: Create project
        $project = CommunityProject::factory()->create([
            'name' => 'Controversial Project',
            'status' => 'PLANNING'
        ]);

        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();

        // Create voters
        $approver1 = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $approver2 = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $rejecter1 = User::factory()->create(['investment_tier_id' => $diamondTier->id]);
        $rejecter2 = User::factory()->create(['investment_tier_id' => $diamondTier->id]);

        // Act: Process votes (more rejection than approval)
        $this->projectService->processProjectVote($approver1->id, $project->id, 'approve');
        $this->projectService->processProjectVote($approver2->id, $project->id, 'approve');
        $this->projectService->processProjectVote($rejecter1->id, $project->id, 'reject');
        $rejectResult = $this->projectService->processProjectVote($rejecter2->id, $project->id, 'reject');

        // Assert: Project should be rejected due to high rejection percentage
        $this->assertTrue($rejectResult['voting_result']['threshold_met']);
        $this->assertEquals('rejected', $rejectResult['voting_result']['result']);

        $project->refresh();
        $this->assertEquals('REJECTED', $project->status);
    }

    public function test_contribution_eligibility_and_limits()
    {
        // Arrange: Create project and users
        $project = CommunityProject::factory()->create([
            'status' => 'FUNDING',
            'target_amount' => 50000,
            'current_amount' => 0
        ]);

        $bronzeTier = InvestmentTier::where('name', 'Bronze Member')->first();
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();

        $bronzeUser = User::factory()->create(['investment_tier_id' => $bronzeTier->id]);
        $goldUser = User::factory()->create(['investment_tier_id' => $goldTier->id]);

        // Act & Assert: Bronze user should be rejected
        $bronzeResult = $this->projectService->processContribution($bronzeUser->id, $project->id, 2000);
        $this->assertFalse($bronzeResult['success']);
        $this->assertStringContains('Gold tier or higher', $bronzeResult['error']);

        // Act & Assert: Gold user with insufficient amount should be rejected
        $lowAmountResult = $this->projectService->processContribution($goldUser->id, $project->id, 500);
        $this->assertFalse($lowAmountResult['success']);
        $this->assertStringContains('Minimum contribution', $lowAmountResult['error']);

        // Act & Assert: Valid contribution should succeed
        $validResult = $this->projectService->processContribution($goldUser->id, $project->id, 2000);
        $this->assertTrue($validResult['success']);

        // Act & Assert: Contribution exceeding target should be rejected
        $excessResult = $this->projectService->processContribution($goldUser->id, $project->id, 50000);
        $this->assertFalse($excessResult['success']);
        $this->assertStringContains('exceeds project funding target', $excessResult['error']);
    }

    public function test_monthly_contribution_limits()
    {
        // Arrange: Create project and Gold user
        $project = CommunityProject::factory()->create([
            'status' => 'FUNDING',
            'target_amount' => 100000
        ]);

        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $goldUser = User::factory()->create(['investment_tier_id' => $goldTier->id]);

        // Act: Make contributions up to monthly limit (K10,000 for Gold)
        $contribution1 = $this->projectService->processContribution($goldUser->id, $project->id, 5000);
        $contribution2 = $this->projectService->processContribution($goldUser->id, $project->id, 4000);
        
        $this->assertTrue($contribution1['success']);
        $this->assertTrue($contribution2['success']);

        // Act & Assert: Exceeding monthly limit should be rejected
        $excessResult = $this->projectService->processContribution($goldUser->id, $project->id, 2000);
        $this->assertFalse($excessResult['success']);
        $this->assertStringContains('Monthly contribution limit exceeded', $excessResult['error']);
    }

    public function test_profit_distribution_with_tier_multipliers()
    {
        // Arrange: Create project with contributions from different tiers
        $project = CommunityProject::factory()->create([
            'status' => 'ACTIVE',
            'target_amount' => 60000,
            'current_amount' => 60000
        ]);

        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();
        $eliteTier = InvestmentTier::where('name', 'Elite Member')->first();

        $goldUser = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $diamondUser = User::factory()->create(['investment_tier_id' => $diamondTier->id]);
        $eliteUser = User::factory()->create(['investment_tier_id' => $eliteTier->id]);

        // Create contributions (equal amounts for easy calculation)
        ProjectContribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $goldUser->id,
            'amount' => 20000,
            'tier_at_contribution' => 'Gold Member'
        ]);

        ProjectContribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $diamondUser->id,
            'amount' => 20000,
            'tier_at_contribution' => 'Diamond Member'
        ]);

        ProjectContribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $eliteUser->id,
            'amount' => 20000,
            'tier_at_contribution' => 'Elite Member'
        ]);

        // Act: Distribute profits
        $distributionResult = $this->projectService->distributeProfits($project->id, 12000, '2024-Q1');

        // Assert: Distribution successful
        $this->assertTrue($distributionResult['success']);
        $this->assertEquals(3, $distributionResult['total_contributors']);

        // Verify tier-based multipliers applied correctly
        $distributions = ProjectProfitDistribution::where('project_id', $project->id)->get();
        
        $goldDistribution = $distributions->where('user_id', $goldUser->id)->first();
        $diamondDistribution = $distributions->where('user_id', $diamondUser->id)->first();
        $eliteDistribution = $distributions->where('user_id', $eliteUser->id)->first();

        // Each user contributed 1/3 of total, so base share is 4000 each
        $this->assertEquals(4000, $goldDistribution->base_amount); // 12000 * (20000/60000)
        $this->assertEquals(4000, $diamondDistribution->base_amount);
        $this->assertEquals(4000, $eliteDistribution->base_amount);

        // Check final amounts with tier multipliers
        $this->assertEquals(4000, $goldDistribution->total_amount); // 4000 * 1.0
        $this->assertEquals(4800, $diamondDistribution->total_amount); // 4000 * 1.2
        $this->assertEquals(6000, $eliteDistribution->total_amount); // 4000 * 1.5
    }

    public function test_project_dashboard_comprehensive_data()
    {
        // Arrange: Create project with full data
        $project = CommunityProject::factory()->create([
            'name' => 'Dashboard Test Project',
            'target_amount' => 80000,
            'current_amount' => 60000,
            'contributor_count' => 4,
            'expected_roi' => 22.0
        ]);

        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();

        // Create contributions
        $user1 = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $user2 = User::factory()->create(['investment_tier_id' => $diamondTier->id]);

        ProjectContribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user1->id,
            'amount' => 25000,
            'tier_at_contribution' => 'Gold Member'
        ]);

        ProjectContribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user2->id,
            'amount' => 35000,
            'tier_at_contribution' => 'Diamond Member'
        ]);

        // Create votes
        ProjectVote::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user1->id,
            'vote_type' => 'approve',
            'voting_power' => 1.5
        ]);

        ProjectVote::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user2->id,
            'vote_type' => 'approve',
            'voting_power' => 2.5
        ]);

        // Create project updates
        ProjectUpdate::factory()->create([
            'project_id' => $project->id,
            'title' => 'Construction Started',
            'update_type' => 'PROGRESS'
        ]);

        // Act: Get dashboard
        $dashboard = $this->projectService->getProjectDashboard($project->id);

        // Assert: Comprehensive dashboard data
        $this->assertEquals($project->id, $dashboard['project']['id']);
        $this->assertEquals('Dashboard Test Project', $dashboard['project']['name']);
        $this->assertEquals(75.0, $dashboard['project']['funding_percentage']); // 60000/80000 * 100

        // Check contribution summary
        $contributionSummary = $dashboard['contribution_summary'];
        $this->assertEquals(60000, $contributionSummary['total_amount']);
        $this->assertEquals(2, $contributionSummary['contributor_count']);
        $this->assertEquals(30000, $contributionSummary['average_contribution']);
        $this->assertEquals(35000, $contributionSummary['largest_contribution']);

        // Check voting summary
        $votingSummary = $dashboard['voting_summary'];
        $this->assertEquals(2, $votingSummary['total_votes']);
        $this->assertEquals(4.0, $votingSummary['total_voting_power']); // 1.5 + 2.5
        $this->assertEquals(2, $votingSummary['approve_votes']);
        $this->assertEquals(100.0, $votingSummary['approval_percentage']);

        // Check other sections exist
        $this->assertArrayHasKey('profit_summary', $dashboard);
        $this->assertArrayHasKey('recent_updates', $dashboard);
        $this->assertArrayHasKey('timeline', $dashboard);
    }

    public function test_user_project_summary_comprehensive()
    {
        // Arrange: Create user with multiple project interactions
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $user = User::factory()->create(['investment_tier_id' => $goldTier->id]);

        $project1 = CommunityProject::factory()->create(['status' => 'ACTIVE']);
        $project2 = CommunityProject::factory()->create(['status' => 'FUNDING']);

        // Create contributions
        ProjectContribution::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project1->id,
            'amount' => 8000
        ]);

        ProjectContribution::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project2->id,
            'amount' => 5000
        ]);

        // Create votes
        ProjectVote::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project1->id,
            'vote_type' => 'approve',
            'voting_power' => 1.8
        ]);

        ProjectVote::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project2->id,
            'vote_type' => 'reject',
            'voting_power' => 1.5
        ]);

        // Create profit distribution
        ProjectProfitDistribution::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project1->id,
            'total_amount' => 1600,
            'status' => 'paid'
        ]);

        // Act: Get user summary
        $summary = $this->projectService->getUserProjectSummary($user->id);

        // Assert: Comprehensive user data
        $this->assertEquals($user->id, $summary['user_id']);

        // Check voting summary
        $votingSummary = $summary['voting_summary'];
        $this->assertEquals(2, $votingSummary['total_votes']);
        $this->assertEquals(1, $votingSummary['approve_votes']);
        $this->assertEquals(1, $votingSummary['reject_votes']);
        $this->assertEquals(0, $votingSummary['abstain_votes']);
        $this->assertEquals(3.3, $votingSummary['total_voting_power']); // 1.8 + 1.5

        // Check active projects
        $this->assertCount(2, $summary['active_projects']);
        
        $activeProjectIds = array_column($summary['active_projects'], 'id');
        $this->assertContains($project1->id, $activeProjectIds);
        $this->assertContains($project2->id, $activeProjectIds);
    }
}