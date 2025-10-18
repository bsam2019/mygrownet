<?php

namespace Tests\Integration\Repositories;

use Tests\TestCase;
use App\Infrastructure\Persistence\Repositories\EloquentProjectRepository;
use App\Domain\Community\ValueObjects\ProjectId;
use App\Domain\Community\ValueObjects\ProjectStatus;
use App\Domain\Community\ValueObjects\ContributionAmount;
use App\Domain\MLM\ValueObjects\UserId;
use App\Models\User;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\InvestmentTier;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentProjectRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentProjectRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentProjectRepository();
    }

    public function test_can_find_project_by_id()
    {
        $project = CommunityProject::create([
            'name' => 'Test Project',
            'description' => 'A test community project',
            'type' => 'real_estate',
            'target_amount' => 100000.00,
            'current_amount' => 25000.00,
            'minimum_contribution' => 1000.00,
            'expected_annual_return' => 12.0,
            'project_duration_months' => 24,
            'funding_start_date' => now(),
            'funding_end_date' => now()->addMonths(3),
            'status' => 'funding',
            'risk_level' => 'medium',
            'is_featured' => true,
            'required_membership_tiers' => ['Gold', 'Diamond', 'Elite'],
            'created_by' => 1
        ]);

        $found = $this->repository->findById(ProjectId::fromInt($project->id));

        $this->assertNotNull($found);
        $this->assertEquals($project->id, $found['id']);
        $this->assertEquals('Test Project', $found['name']);
        $this->assertEquals('real_estate', $found['type']);
        $this->assertEquals(100000.00, $found['target_amount']);
        $this->assertEquals(25000.00, $found['current_amount']);
        $this->assertEquals(25.0, $found['funding_progress']);
        $this->assertTrue($found['is_featured']);
    }

    public function test_can_find_projects_by_status()
    {
        CommunityProject::create([
            'name' => 'Funding Project',
            'type' => 'agriculture',
            'target_amount' => 50000.00,
            'status' => 'funding',
            'funding_start_date' => now(),
            'funding_end_date' => now()->addMonths(2),
            'created_by' => 1
        ]);

        CommunityProject::create([
            'name' => 'Active Project',
            'type' => 'sme',
            'target_amount' => 75000.00,
            'status' => 'active',
            'created_by' => 1
        ]);

        $fundingProjects = $this->repository->findByStatus(ProjectStatus::funding());

        $this->assertCount(1, $fundingProjects);
        $this->assertEquals('Funding Project', $fundingProjects[0]['name']);
        $this->assertEquals('funding', $fundingProjects[0]['status']);
    }

    public function test_can_find_active_funding_projects()
    {
        // Active funding project
        CommunityProject::create([
            'name' => 'Active Funding',
            'type' => 'digital',
            'target_amount' => 30000.00,
            'current_amount' => 10000.00,
            'status' => 'funding',
            'funding_start_date' => now()->subDays(10),
            'funding_end_date' => now()->addDays(20),
            'created_by' => 1
        ]);

        // Expired funding project
        CommunityProject::create([
            'name' => 'Expired Funding',
            'type' => 'agriculture',
            'target_amount' => 40000.00,
            'status' => 'funding',
            'funding_start_date' => now()->subMonths(2),
            'funding_end_date' => now()->subDays(5),
            'created_by' => 1
        ]);

        $activeFunding = $this->repository->findActiveFundingProjects();

        $this->assertCount(1, $activeFunding);
        $this->assertEquals('Active Funding', $activeFunding[0]['name']);
        $this->assertTrue($activeFunding[0]['is_funding_active']);
    }

    public function test_can_find_projects_for_tier()
    {
        // Project accessible to Gold tier
        CommunityProject::create([
            'name' => 'Gold Project',
            'type' => 'real_estate',
            'target_amount' => 100000.00,
            'status' => 'funding',
            'required_membership_tiers' => ['Gold', 'Diamond', 'Elite'],
            'created_by' => 1
        ]);

        // Project accessible to all tiers
        CommunityProject::create([
            'name' => 'All Tiers Project',
            'type' => 'sme',
            'target_amount' => 25000.00,
            'status' => 'funding',
            'required_membership_tiers' => [],
            'created_by' => 1
        ]);

        // Project not accessible to Bronze
        CommunityProject::create([
            'name' => 'Elite Only Project',
            'type' => 'infrastructure',
            'target_amount' => 500000.00,
            'status' => 'funding',
            'required_membership_tiers' => ['Elite'],
            'created_by' => 1
        ]);

        $goldProjects = $this->repository->findProjectsForTier('Gold');

        $this->assertCount(2, $goldProjects); // Gold Project + All Tiers Project
        
        $projectNames = array_column($goldProjects, 'name');
        $this->assertContains('Gold Project', $projectNames);
        $this->assertContains('All Tiers Project', $projectNames);
        $this->assertNotContains('Elite Only Project', $projectNames);
    }

    public function test_can_check_user_contribution_eligibility()
    {
        $tier = InvestmentTier::create([
            'name' => 'Gold',
            'minimum_amount' => 500,
            'monthly_fee' => 500.00,
            'is_active' => true
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'current_investment_tier_id' => $tier->id
        ]);

        $project = CommunityProject::create([
            'name' => 'Eligible Project',
            'type' => 'real_estate',
            'target_amount' => 100000.00,
            'current_amount' => 50000.00,
            'minimum_contribution' => 1000.00,
            'maximum_contribution' => 10000.00,
            'status' => 'funding',
            'funding_start_date' => now()->subDays(5),
            'funding_end_date' => now()->addDays(25),
            'required_membership_tiers' => ['Gold', 'Diamond'],
            'tier_contribution_limits' => ['Gold' => 15000.00],
            'created_by' => 1
        ]);

        $canContribute = $this->repository->canUserContribute(
            UserId::fromInt($user->id),
            ProjectId::fromInt($project->id)
        );

        $this->assertTrue($canContribute);
    }

    public function test_can_get_project_funding_statistics()
    {
        $project = CommunityProject::create([
            'name' => 'Stats Project',
            'type' => 'agriculture',
            'target_amount' => 50000.00,
            'current_amount' => 20000.00,
            'total_contributors' => 8,
            'status' => 'funding',
            'funding_start_date' => now()->subDays(10),
            'funding_end_date' => now()->addDays(20),
            'created_by' => 1
        ]);

        $user1 = User::create(['name' => 'User 1', 'email' => 'user1@test.com', 'password' => 'password']);
        $user2 = User::create(['name' => 'User 2', 'email' => 'user2@test.com', 'password' => 'password']);

        ProjectContribution::create([
            'user_id' => $user1->id,
            'community_project_id' => $project->id,
            'amount' => 5000.00,
            'status' => 'confirmed',
            'contributed_at' => now()->subDays(5)
        ]);

        ProjectContribution::create([
            'user_id' => $user2->id,
            'community_project_id' => $project->id,
            'amount' => 3000.00,
            'status' => 'confirmed',
            'contributed_at' => now()->subDays(3)
        ]);

        $stats = $this->repository->getProjectFundingStats(ProjectId::fromInt($project->id));

        $this->assertEquals($project->id, $stats['project_id']);
        $this->assertEquals(50000.00, $stats['target_amount']);
        $this->assertEquals(20000.00, $stats['current_amount']);
        $this->assertEquals(30000.00, $stats['remaining_amount']);
        $this->assertEquals(40.0, $stats['funding_progress']);
        $this->assertEquals(8, $stats['total_contributors']);
        $this->assertEquals(4000.00, $stats['average_contribution']); // (5000 + 3000) / 2
        $this->assertEquals(5000.00, $stats['largest_contribution']);
        $this->assertEquals(3000.00, $stats['smallest_contribution']);
        $this->assertEquals(20, $stats['days_remaining']);
    }

    public function test_can_find_user_projects()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);

        $project1 = CommunityProject::create([
            'name' => 'User Project 1',
            'type' => 'sme',
            'target_amount' => 25000.00,
            'status' => 'active',
            'created_by' => 1
        ]);

        $project2 = CommunityProject::create([
            'name' => 'User Project 2',
            'type' => 'agriculture',
            'target_amount' => 35000.00,
            'status' => 'funding',
            'created_by' => 1
        ]);

        $project3 = CommunityProject::create([
            'name' => 'Other Project',
            'type' => 'digital',
            'target_amount' => 15000.00,
            'status' => 'funding',
            'created_by' => 1
        ]);

        // User contributes to project1 and project2
        ProjectContribution::create([
            'user_id' => $user->id,
            'community_project_id' => $project1->id,
            'amount' => 2000.00,
            'status' => 'confirmed',
            'contributed_at' => now()
        ]);

        ProjectContribution::create([
            'user_id' => $user->id,
            'community_project_id' => $project2->id,
            'amount' => 1500.00,
            'status' => 'confirmed',
            'contributed_at' => now()
        ]);

        $userProjects = $this->repository->findUserProjects(UserId::fromInt($user->id));

        $this->assertCount(2, $userProjects);
        
        $projectNames = array_column($userProjects, 'name');
        $this->assertContains('User Project 1', $projectNames);
        $this->assertContains('User Project 2', $projectNames);
        $this->assertNotContains('Other Project', $projectNames);
    }

    public function test_can_find_projects_by_funding_progress()
    {
        CommunityProject::create([
            'name' => 'Low Progress',
            'target_amount' => 100000.00,
            'current_amount' => 15000.00, // 15%
            'status' => 'funding',
            'created_by' => 1
        ]);

        CommunityProject::create([
            'name' => 'Medium Progress',
            'target_amount' => 50000.00,
            'current_amount' => 30000.00, // 60%
            'status' => 'funding',
            'created_by' => 1
        ]);

        CommunityProject::create([
            'name' => 'High Progress',
            'target_amount' => 25000.00,
            'current_amount' => 22500.00, // 90%
            'status' => 'funding',
            'created_by' => 1
        ]);

        $mediumProgressProjects = $this->repository->findProjectsByFundingProgress(50.0, 80.0);

        $this->assertCount(1, $mediumProgressProjects);
        $this->assertEquals('Medium Progress', $mediumProgressProjects[0]['name']);
        $this->assertEquals(60.0, $mediumProgressProjects[0]['funding_progress']);
    }

    public function test_can_get_project_category_statistics()
    {
        CommunityProject::create([
            'name' => 'Real Estate 1',
            'type' => 'real_estate',
            'target_amount' => 100000.00,
            'current_amount' => 75000.00,
            'expected_annual_return' => 15.0,
            'community_approval_rating' => 4.5,
            'created_by' => 1
        ]);

        CommunityProject::create([
            'name' => 'Real Estate 2',
            'type' => 'real_estate',
            'target_amount' => 150000.00,
            'current_amount' => 100000.00,
            'expected_annual_return' => 12.0,
            'community_approval_rating' => 4.2,
            'created_by' => 1
        ]);

        CommunityProject::create([
            'name' => 'Agriculture 1',
            'type' => 'agriculture',
            'target_amount' => 50000.00,
            'current_amount' => 30000.00,
            'expected_annual_return' => 10.0,
            'community_approval_rating' => 4.0,
            'created_by' => 1
        ]);

        $categoryStats = $this->repository->getProjectCategoryStats();

        $this->assertArrayHasKey('real_estate', $categoryStats);
        $this->assertArrayHasKey('agriculture', $categoryStats);
        
        $realEstateStats = $categoryStats['real_estate'];
        $this->assertEquals(2, $realEstateStats['project_count']);
        $this->assertEquals(250000.00, $realEstateStats['total_target']);
        $this->assertEquals(175000.00, $realEstateStats['total_raised']);
        $this->assertEquals(70.0, $realEstateStats['funding_rate']); // 175000/250000 * 100
        $this->assertEquals(13.5, $realEstateStats['avg_return']); // (15+12)/2
        $this->assertEquals(4.35, $realEstateStats['avg_rating']); // (4.5+4.2)/2
        
        $agricultureStats = $categoryStats['agriculture'];
        $this->assertEquals(1, $agricultureStats['project_count']);
        $this->assertEquals(50000.00, $agricultureStats['total_target']);
        $this->assertEquals(30000.00, $agricultureStats['total_raised']);
        $this->assertEquals(60.0, $agricultureStats['funding_rate']);
    }

    public function test_repository_performance_with_large_dataset()
    {
        // Create 100 projects
        for ($i = 1; $i <= 100; $i++) {
            CommunityProject::create([
                'name' => "Project {$i}",
                'type' => ['real_estate', 'agriculture', 'sme', 'digital'][($i - 1) % 4],
                'target_amount' => rand(25000, 500000),
                'current_amount' => rand(5000, 100000),
                'status' => ['funding', 'active', 'completed'][($i - 1) % 3],
                'expected_annual_return' => rand(8, 20),
                'risk_level' => ['low', 'medium', 'high'][($i - 1) % 3],
                'is_featured' => ($i % 10) === 0,
                'created_by' => 1
            ]);
        }

        $startTime = microtime(true);
        
        $fundingProjects = $this->repository->findByStatus(ProjectStatus::funding());
        $featuredProjects = $this->repository->findFeaturedProjects();
        $categoryStats = $this->repository->getProjectCategoryStats();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertNotEmpty($fundingProjects);
        $this->assertNotEmpty($featuredProjects);
        $this->assertNotEmpty($categoryStats);
        $this->assertLessThan(2.0, $executionTime, 'Project repository queries should complete within 2 seconds');
    }
}