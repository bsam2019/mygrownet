<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\MyGrowNetNotificationService;
use App\Notifications\MyGrowNetCommunityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MyGrowNetCommunityNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MyGrowNetNotificationService $notificationService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->notificationService = new MyGrowNetNotificationService();
        $this->user = User::factory()->create([
            'name' => 'Community Member',
            'email' => 'community@example.com',
            'phone' => '+260971234567'
        ]);
        
        Notification::fake();
    }

    /** @test */
    public function it_sends_project_launched_notification()
    {
        $this->notificationService->sendProjectLaunchedNotification(
            $this->user,
            'Lusaka Shopping Mall Development',
            500000.00,
            5000.00,
            'Real Estate',
            'Development of a modern shopping mall in Lusaka CBD',
            '15-20% annual returns',
            '18 months'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_launched' &&
                       $notification->data['project_name'] === 'Lusaka Shopping Mall Development' &&
                       $notification->data['target_amount'] === 500000.00 &&
                       $notification->data['minimum_contribution'] === 5000.00 &&
                       $notification->data['project_type'] === 'Real Estate' &&
                       $notification->data['project_description'] === 'Development of a modern shopping mall in Lusaka CBD' &&
                       $notification->data['expected_returns'] === '15-20% annual returns' &&
                       $notification->data['project_duration'] === '18 months';
            }
        );
    }

    /** @test */
    public function it_sends_project_update_posted_notification()
    {
        $this->notificationService->sendProjectUpdatePostedNotification(
            $this->user,
            'Agricultural Cooperative Project',
            'Foundation Construction Completed',
            'The foundation work has been completed ahead of schedule with excellent quality.',
            35.5,
            177500.00,
            500000.00,
            '2025-01-15'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_update_posted' &&
                       $notification->data['project_name'] === 'Agricultural Cooperative Project' &&
                       $notification->data['update_title'] === 'Foundation Construction Completed' &&
                       $notification->data['update_summary'] === 'The foundation work has been completed ahead of schedule with excellent quality.' &&
                       $notification->data['progress_percentage'] === 35.5 &&
                       $notification->data['current_amount'] === 177500.00 &&
                       $notification->data['target_amount'] === 500000.00 &&
                       $notification->data['update_date'] === '2025-01-15';
            }
        );
    }

    /** @test */
    public function it_sends_project_milestone_reached_notification()
    {
        $this->notificationService->sendProjectMilestoneReachedNotification(
            $this->user,
            'Tech Startup Incubator',
            '50% Funding Milestone',
            'We have successfully reached 50% of our funding target with strong community support.',
            50.0,
            '75% Funding Milestone',
            'K500 bonus for early contributors'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_milestone_reached' &&
                       $notification->data['project_name'] === 'Tech Startup Incubator' &&
                       $notification->data['milestone_name'] === '50% Funding Milestone' &&
                       $notification->data['milestone_description'] === 'We have successfully reached 50% of our funding target with strong community support.' &&
                       $notification->data['progress_percentage'] === 50.0 &&
                       $notification->data['next_milestone'] === '75% Funding Milestone' &&
                       $notification->data['celebration_bonus'] === 'K500 bonus for early contributors';
            }
        );
    }

    /** @test */
    public function it_sends_project_funding_target_reached_notification()
    {
        $this->notificationService->sendProjectFundingTargetReachedNotification(
            $this->user,
            'Community Solar Farm',
            250000.00,
            45,
            12500.00,
            5.0,
            '2025-02-01'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_funding_target_reached' &&
                       $notification->data['project_name'] === 'Community Solar Farm' &&
                       $notification->data['target_amount'] === 250000.00 &&
                       $notification->data['total_contributors'] === 45 &&
                       $notification->data['user_contribution'] === 12500.00 &&
                       $notification->data['user_percentage'] === 5.0 &&
                       $notification->data['project_start_date'] === '2025-02-01';
            }
        );
    }

    /** @test */
    public function it_sends_project_completed_notification()
    {
        $this->notificationService->sendProjectCompletedNotification(
            $this->user,
            'Organic Farming Initiative',
            75000.00,
            3750.00,
            18.5,
            '12 months',
            '2025-01-20',
            '2025-01-25'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_completed' &&
                       $notification->data['project_name'] === 'Organic Farming Initiative' &&
                       $notification->data['total_profit'] === 75000.00 &&
                       $notification->data['user_share'] === 3750.00 &&
                       $notification->data['return_percentage'] === 18.5 &&
                       $notification->data['project_duration'] === '12 months' &&
                       $notification->data['completion_date'] === '2025-01-20' &&
                       $notification->data['payment_date'] === '2025-01-25';
            }
        );
    }

    /** @test */
    public function it_sends_voting_opened_notification()
    {
        $votingOptions = [
            'Option A: Expand to second location',
            'Option B: Invest in equipment upgrade',
            'Option C: Distribute profits to contributors'
        ];

        $this->notificationService->sendVotingOpenedNotification(
            $this->user,
            'Restaurant Chain Project',
            'Next Phase Investment Decision',
            'We need to decide how to use the profits from our successful first restaurant.',
            '2025-02-15 23:59',
            $votingOptions,
            7.5
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) use ($votingOptions) {
                return $notification->data['type'] === 'voting_opened' &&
                       $notification->data['project_name'] === 'Restaurant Chain Project' &&
                       $notification->data['voting_topic'] === 'Next Phase Investment Decision' &&
                       $notification->data['voting_description'] === 'We need to decide how to use the profits from our successful first restaurant.' &&
                       $notification->data['voting_deadline'] === '2025-02-15 23:59' &&
                       $notification->data['voting_options'] === $votingOptions &&
                       $notification->data['user_voting_power'] === 7.5;
            }
        );
    }

    /** @test */
    public function it_sends_voting_reminder_notification()
    {
        $this->notificationService->sendVotingReminderNotification(
            $this->user,
            'E-commerce Platform Development',
            'Technology Stack Selection',
            '2025-02-10 18:00',
            '3 days',
            65.5
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'voting_reminder' &&
                       $notification->data['project_name'] === 'E-commerce Platform Development' &&
                       $notification->data['voting_topic'] === 'Technology Stack Selection' &&
                       $notification->data['voting_deadline'] === '2025-02-10 18:00' &&
                       $notification->data['time_remaining'] === '3 days' &&
                       $notification->data['current_participation'] === 65.5;
            }
        );
    }

    /** @test */
    public function it_sends_voting_closing_soon_notification()
    {
        $this->notificationService->sendVotingClosingSoonNotification(
            $this->user,
            'Manufacturing Plant Project',
            'Equipment Supplier Selection',
            6,
            78.2,
            'Supplier B: Advanced Technology Solutions'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'voting_closing_soon' &&
                       $notification->data['project_name'] === 'Manufacturing Plant Project' &&
                       $notification->data['voting_topic'] === 'Equipment Supplier Selection' &&
                       $notification->data['hours_remaining'] === 6 &&
                       $notification->data['current_participation'] === 78.2 &&
                       $notification->data['leading_option'] === 'Supplier B: Advanced Technology Solutions';
            }
        );
    }

    /** @test */
    public function it_sends_voting_results_notification()
    {
        $this->notificationService->sendVotingResultsNotification(
            $this->user,
            'Transportation Service Project',
            'Fleet Expansion Strategy',
            'Gradual expansion with quality focus',
            67.8,
            85.5,
            true,
            'Gradual expansion with quality focus',
            '2025-03-01'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'voting_results' &&
                       $notification->data['project_name'] === 'Transportation Service Project' &&
                       $notification->data['voting_topic'] === 'Fleet Expansion Strategy' &&
                       $notification->data['winning_option'] === 'Gradual expansion with quality focus' &&
                       $notification->data['winning_percentage'] === 67.8 &&
                       $notification->data['total_participation'] === 85.5 &&
                       $notification->data['user_voted'] === true &&
                       $notification->data['user_choice'] === 'Gradual expansion with quality focus' &&
                       $notification->data['implementation_date'] === '2025-03-01';
            }
        );
    }

    /** @test */
    public function it_sends_profit_distribution_calculated_notification()
    {
        $this->notificationService->sendProfitDistributionCalculatedNotification(
            $this->user,
            'Digital Marketing Agency',
            45000.00,
            2250.00,
            5.0,
            'Quarterly',
            '2025-02-01',
            'Exceeded expectations by 15%'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'profit_distribution_calculated' &&
                       $notification->data['project_name'] === 'Digital Marketing Agency' &&
                       $notification->data['total_profit'] === 45000.00 &&
                       $notification->data['user_share'] === 2250.00 &&
                       $notification->data['user_percentage'] === 5.0 &&
                       $notification->data['distribution_period'] === 'Quarterly' &&
                       $notification->data['payment_date'] === '2025-02-01' &&
                       $notification->data['project_performance'] === 'Exceeded expectations by 15%';
            }
        );
    }

    /** @test */
    public function it_sends_profit_distribution_paid_notification()
    {
        $this->notificationService->sendProfitDistributionPaidNotification(
            $this->user,
            'Logistics Company',
            3200.00,
            'MTN Mobile Money',
            'TXN987654321',
            'Monthly',
            19200.00
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'profit_distribution_paid' &&
                       $notification->data['project_name'] === 'Logistics Company' &&
                       $notification->data['distribution_amount'] === 3200.00 &&
                       $notification->data['payment_method'] === 'MTN Mobile Money' &&
                       $notification->data['transaction_id'] === 'TXN987654321' &&
                       $notification->data['distribution_period'] === 'Monthly' &&
                       $notification->data['total_earnings_to_date'] === 19200.00;
            }
        );
    }

    /** @test */
    public function it_sends_quarterly_community_report_notification()
    {
        $this->notificationService->sendQuarterlyCommunityReportNotification(
            $this->user,
            'Q4 2024',
            12,
            8,
            285000.00,
            14250.00,
            75000.00,
            'Renewable Energy Project'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'quarterly_community_report' &&
                       $notification->data['report_period'] === 'Q4 2024' &&
                       $notification->data['total_projects'] === 12 &&
                       $notification->data['completed_projects'] === 8 &&
                       $notification->data['total_community_profit'] === 285000.00 &&
                       $notification->data['user_total_earnings'] === 14250.00 &&
                       $notification->data['active_contributions'] === 75000.00 &&
                       $notification->data['top_performing_project'] === 'Renewable Energy Project';
            }
        );
    }

    /** @test */
    public function it_sends_new_project_opportunity_notification()
    {
        $this->notificationService->sendNewProjectOpportunityNotification(
            $this->user,
            'Fintech Startup Investment',
            150000.00,
            2500.00,
            'Technology',
            '25-35% annual returns',
            '24 months',
            '10% bonus for first 20 contributors',
            '2025-02-20'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'new_project_opportunity' &&
                       $notification->data['project_name'] === 'Fintech Startup Investment' &&
                       $notification->data['target_amount'] === 150000.00 &&
                       $notification->data['minimum_contribution'] === 2500.00 &&
                       $notification->data['project_type'] === 'Technology' &&
                       $notification->data['expected_returns'] === '25-35% annual returns' &&
                       $notification->data['project_duration'] === '24 months' &&
                       $notification->data['early_bird_bonus'] === '10% bonus for first 20 contributors' &&
                       $notification->data['launch_date'] === '2025-02-20';
            }
        );
    }

    /** @test */
    public function it_sends_contribution_acknowledged_notification()
    {
        $this->notificationService->sendContributionAcknowledgedNotification(
            $this->user,
            'Healthcare Clinic Project',
            15000.00,
            6.0,
            180000.00,
            250000.00,
            72.0,
            '8th largest'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'contribution_acknowledged' &&
                       $notification->data['project_name'] === 'Healthcare Clinic Project' &&
                       $notification->data['contribution_amount'] === 15000.00 &&
                       $notification->data['ownership_percentage'] === 6.0 &&
                       $notification->data['total_raised'] === 180000.00 &&
                       $notification->data['target_amount'] === 250000.00 &&
                       $notification->data['progress_percentage'] === 72.0 &&
                       $notification->data['contributor_rank'] === '8th largest';
            }
        );
    }

    /** @test */
    public function it_sends_project_risk_alert_notification()
    {
        $this->notificationService->sendProjectRiskAlertNotification(
            $this->user,
            'Construction Project Alpha',
            'Material Cost Increase',
            'Steel prices have increased by 20% due to market conditions.',
            'Negotiate with suppliers and consider alternative materials.',
            'Medium',
            true,
            true
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_risk_alert' &&
                       $notification->data['project_name'] === 'Construction Project Alpha' &&
                       $notification->data['risk_type'] === 'Material Cost Increase' &&
                       $notification->data['risk_description'] === 'Steel prices have increased by 20% due to market conditions.' &&
                       $notification->data['mitigation_plan'] === 'Negotiate with suppliers and consider alternative materials.' &&
                       $notification->data['impact_level'] === 'Medium' &&
                       $notification->data['action_required'] === true &&
                       $notification->data['voting_scheduled'] === true;
            }
        );
    }

    /** @test */
    public function it_sends_bulk_community_notifications()
    {
        $users = User::factory()->count(6)->create();
        
        $data = [
            'type' => 'quarterly_community_report',
            'report_period' => 'Q1 2025',
            'total_projects' => 15,
            'completed_projects' => 10,
            'total_community_profit' => 350000.00
        ];

        $this->notificationService->sendBulkCommunityNotifications($users->toArray(), $data);

        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                MyGrowNetCommunityNotification::class,
                function ($notification) use ($data) {
                    return $notification->data['type'] === $data['type'] &&
                           $notification->data['report_period'] === $data['report_period'] &&
                           $notification->data['total_projects'] === $data['total_projects'];
                }
            );
        }
    }

    /** @test */
    public function it_handles_community_notification_sending_errors_gracefully()
    {
        // This test verifies the method exists and can be called
        $this->assertTrue(method_exists($this->notificationService, 'sendCommunityNotification'));
        
        // Test that the service can handle empty or invalid data
        $this->notificationService->sendCommunityNotification($this->user, [
            'type' => 'invalid_type'
        ]);
        
        // Should not throw an exception
        $this->assertTrue(true);
    }

    /** @test */
    public function community_notifications_use_correct_default_values()
    {
        // Test with minimal required data
        $this->notificationService->sendProjectLaunchedNotification(
            $this->user,
            'Basic Project',
            10000.00,
            500.00
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_launched' &&
                       $notification->data['project_type'] === 'Community Project' &&
                       $notification->data['project_description'] === '' &&
                       $notification->data['expected_returns'] === '' &&
                       $notification->data['project_duration'] === '';
            }
        );
    }

    /** @test */
    public function project_update_notification_uses_current_date_as_default()
    {
        $this->notificationService->sendProjectUpdatePostedNotification(
            $this->user,
            'Test Project',
            'Test Update'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_update_posted' &&
                       $notification->data['update_date'] === date('Y-m-d');
            }
        );
    }

    /** @test */
    public function project_completed_notification_uses_current_date_as_default()
    {
        $this->notificationService->sendProjectCompletedNotification(
            $this->user,
            'Test Project',
            10000.00,
            500.00,
            15.0
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'project_completed' &&
                       $notification->data['completion_date'] === date('Y-m-d');
            }
        );
    }
}