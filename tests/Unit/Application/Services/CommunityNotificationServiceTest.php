<?php

namespace Tests\Unit\Application\Services;

use Tests\TestCase;
use App\Application\Services\CommunityNotificationService;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectProfitDistribution;
use App\Models\ProjectVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class CommunityNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommunityNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new CommunityNotificationService();
        $this->createTiers();
    }

    private function createTiers(): void
    {
        InvestmentTier::factory()->create(['name' => 'Bronze Member']);
        InvestmentTier::factory()->create(['name' => 'Gold Member']);
        InvestmentTier::factory()->create(['name' => 'Diamond Member']);
        InvestmentTier::factory()->create(['name' => 'Elite Member']);
    }

    public function test_send_contribution_notifications()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $contributor = User::factory()->create([
            'investment_tier_id' => $goldTier->id,
            'name' => 'John Contributor'
        ]);

        $project = CommunityProject::factory()->create([
            'name' => 'Solar Farm Project',
            'target_amount' => 100000,
            'current_amount' => 25000
        ]);

        // Create existing contributors
        $existingContributor = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        ProjectContribution::factory()->create([
            'user_id' => $existingContributor->id,
            'project_id' => $project->id,
            'amount' => 15000
        ]);

        // Expect log entries for notifications
        Log::shouldReceive('info')
            ->with('Contribution confirmation notification', \Mockery::type('array'))
            ->once();

        Log::shouldReceive('info')
            ->with('Project progress notification', \Mockery::type('array'))
            ->once();

        // Act
        $this->service->sendContributionNotifications($project->id, $contributor->id, 5000);

        // Assert - Verify log calls were made (notifications would be sent in real implementation)
        $this->assertTrue(true); // Test passes if no exceptions thrown
    }

    public function test_send_profit_distribution_notifications()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();

        $project = CommunityProject::factory()->create(['name' => 'Test Project']);
        
        $user1 = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $user2 = User::factory()->create(['investment_tier_id' => $diamondTier->id]);

        $distribution1 = ProjectProfitDistribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user1->id,
            'total_amount' => 1500,
            'distribution_period' => '2024-Q1'
        ]);

        $distribution2 = ProjectProfitDistribution::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user2->id,
            'total_amount' => 2500,
            'distribution_period' => '2024-Q1'
        ]);

        // Expect log entries for each distribution
        Log::shouldReceive('info')
            ->with('Profit distribution notification', \Mockery::type('array'))
            ->twice();

        Log::shouldReceive('info')
            ->with('Distribution summary notification', \Mockery::type('array'))
            ->once();

        // Act
        $this->service->sendProfitDistributionNotifications($project->id, '2024-Q1');

        // Assert - Test passes if no exceptions thrown
        $this->assertTrue(true);
    }

    public function test_send_project_status_notifications()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $project = CommunityProject::factory()->create([
            'name' => 'Status Change Project',
            'status' => 'FUNDING'
        ]);

        // Create contributors and voters (stakeholders)
        $contributor = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $voter = User::factory()->create(['investment_tier_id' => $goldTier->id]);

        ProjectContribution::factory()->create([
            'user_id' => $contributor->id,
            'project_id' => $project->id,
            'amount' => 5000
        ]);

        ProjectVote::factory()->create([
            'user_id' => $voter->id,
            'project_id' => $project->id,
            'vote_type' => 'approve'
        ]);

        // Expect log entries for each stakeholder
        Log::shouldReceive('info')
            ->with('Project status notification', \Mockery::type('array'))
            ->twice(); // Once for contributor, once for voter

        // Act
        $this->service->sendProjectStatusNotifications($project->id, 'FUNDING', 'ACTIVE');

        // Assert - Test passes if no exceptions thrown
        $this->assertTrue(true);
    }

    public function test_send_voting_notifications()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();
        $bronzeTier = InvestmentTier::where('name', 'Bronze Member')->first();

        $project = CommunityProject::factory()->create([
            'name' => 'Voting Project',
            'status' => 'PLANNING'
        ]);

        // Create eligible voters (Gold+ tier)
        $goldUser = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $diamondUser = User::factory()->create(['investment_tier_id' => $diamondTier->id]);
        
        // Create ineligible voter (Bronze tier)
        $bronzeUser = User::factory()->create(['investment_tier_id' => $bronzeTier->id]);

        // Create existing vote for one user
        ProjectVote::factory()->create([
            'user_id' => $goldUser->id,
            'project_id' => $project->id,
            'vote_type' => 'approve'
        ]);

        // Expect log entry only for diamond user (gold user already voted, bronze user ineligible)
        Log::shouldReceive('info')
            ->with('Voting reminder notification', \Mockery::type('array'))
            ->once();

        // Act
        $this->service->sendVotingNotifications($project->id);

        // Assert - Test passes if no exceptions thrown
        $this->assertTrue(true);
    }

    public function test_send_project_update_notifications()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $project = CommunityProject::factory()->create(['name' => 'Update Project']);

        // Create stakeholders
        $contributor = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $voter = User::factory()->create(['investment_tier_id' => $goldTier->id]);

        ProjectContribution::factory()->create([
            'user_id' => $contributor->id,
            'project_id' => $project->id,
            'amount' => 3000
        ]);

        ProjectVote::factory()->create([
            'user_id' => $voter->id,
            'project_id' => $project->id,
            'vote_type' => 'approve'
        ]);

        // Expect log entries for each stakeholder
        Log::shouldReceive('info')
            ->with('Project update notification', \Mockery::type('array'))
            ->twice();

        // Act
        $this->service->sendProjectUpdateNotifications(
            $project->id,
            'Construction Started',
            'We have begun construction on the solar farm facility.'
        );

        // Assert - Test passes if no exceptions thrown
        $this->assertTrue(true);
    }

    public function test_send_monthly_project_summaries()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        
        // Create active projects
        $project1 = CommunityProject::factory()->create([
            'name' => 'Active Project 1',
            'status' => 'ACTIVE'
        ]);

        $project2 = CommunityProject::factory()->create([
            'name' => 'Funding Project 2',
            'status' => 'FUNDING'
        ]);

        // Create completed project (should not be included)
        $project3 = CommunityProject::factory()->create([
            'name' => 'Completed Project 3',
            'status' => 'COMPLETED'
        ]);

        // Create stakeholders for active projects
        $user1 = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $user2 = User::factory()->create(['investment_tier_id' => $goldTier->id]);

        ProjectContribution::factory()->create([
            'user_id' => $user1->id,
            'project_id' => $project1->id,
            'amount' => 5000
        ]);

        ProjectContribution::factory()->create([
            'user_id' => $user2->id,
            'project_id' => $project2->id,
            'amount' => 3000
        ]);

        // Expect log entries for monthly summaries
        Log::shouldReceive('info')
            ->with('Monthly project summary notification', \Mockery::type('array'))
            ->twice(); // One for each active project stakeholder

        // Act
        $results = $this->service->sendMonthlyProjectSummaries();

        // Assert
        $this->assertEquals(2, $results['sent']); // 2 notifications sent
        $this->assertEquals(0, $results['failed']);
        $this->assertEquals(2, $results['projects_processed']); // 2 active projects processed
    }

    public function test_get_user_notification_preferences()
    {
        // Arrange
        $userId = 1;

        // Act
        $preferences = $this->service->getUserNotificationPreferences($userId);

        // Assert
        $expectedPreferences = [
            'project_contributions' => true,
            'profit_distributions' => true,
            'project_status_changes' => true,
            'voting_reminders' => true,
            'project_updates' => true,
            'monthly_summaries' => true,
            'milestone_notifications' => true
        ];

        $this->assertEquals($expectedPreferences, $preferences);
    }

    public function test_update_user_notification_preferences()
    {
        // Arrange
        $userId = 1;
        $newPreferences = [
            'project_contributions' => true,
            'profit_distributions' => true,
            'project_status_changes' => false,
            'voting_reminders' => false,
            'project_updates' => true,
            'monthly_summaries' => false,
            'milestone_notifications' => true
        ];

        // Expect log entry for preference update
        Log::shouldReceive('info')
            ->with('Notification preferences updated', \Mockery::type('array'))
            ->once();

        // Act
        $result = $this->service->updateUserNotificationPreferences($userId, $newPreferences);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals($newPreferences, $result['preferences']);
    }

    public function test_milestone_notifications_check()
    {
        // Arrange
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $project = CommunityProject::factory()->create([
            'name' => 'Milestone Project',
            'target_amount' => 100000,
            'current_amount' => 75000 // 75% funded
        ]);

        $contributor = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        ProjectContribution::factory()->create([
            'user_id' => $contributor->id,
            'project_id' => $project->id,
            'amount' => 75000
        ]);

        // Expect milestone notification logs
        Log::shouldReceive('info')
            ->with('Milestone notification', \Mockery::type('array'))
            ->atLeast()->once();

        Log::shouldReceive('info')
            ->with('Milestone notification recorded', \Mockery::type('array'))
            ->atLeast()->once();

        // Act
        $this->service->sendContributionNotifications($project->id, $contributor->id, 1000);

        // Assert - Test passes if no exceptions thrown
        $this->assertTrue(true);
    }

    public function test_notification_filtering_by_tier()
    {
        // Arrange
        $bronzeTier = InvestmentTier::where('name', 'Bronze Member')->first();
        $goldTier = InvestmentTier::where('name', 'Gold Member')->first();
        $diamondTier = InvestmentTier::where('name', 'Diamond Member')->first();

        $project = CommunityProject::factory()->create([
            'name' => 'Tier Filter Project',
            'status' => 'PLANNING'
        ]);

        // Create users with different tiers
        $bronzeUser = User::factory()->create(['investment_tier_id' => $bronzeTier->id]);
        $goldUser = User::factory()->create(['investment_tier_id' => $goldTier->id]);
        $diamondUser = User::factory()->create(['investment_tier_id' => $diamondTier->id]);

        // Expect notifications only for Gold+ tier users
        Log::shouldReceive('info')
            ->with('Voting reminder notification', \Mockery::type('array'))
            ->twice(); // Gold and Diamond users only

        // Act
        $this->service->sendVotingNotifications($project->id);

        // Assert - Test passes if correct number of notifications sent
        $this->assertTrue(true);
    }
}