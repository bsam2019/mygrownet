<?php

namespace Tests\Integration;

use App\Models\User;
use App\Notifications\MyGrowNetCommunityNotification;
use App\Services\MyGrowNetNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommunityNotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected MyGrowNetNotificationService $notificationService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->notificationService = new MyGrowNetNotificationService();
        $this->user = User::factory()->create([
            'name' => 'Community Investor',
            'email' => 'investor@example.com',
            'phone' => '+260971234567',
            'notification_preferences' => [
                'email' => true,
                'sms' => true,
                'community' => true
            ]
        ]);
    }

    /** @test */
    public function community_notification_delivers_via_correct_channels()
    {
        Notification::fake();

        $this->notificationService->sendProjectLaunchedNotification(
            $this->user,
            'Community Solar Farm',
            250000.00,
            5000.00
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommunityNotification::class,
            function ($notification, $channels) {
                // Should include database and mail channels
                $expectedChannels = ['database', 'mail'];
                return count(array_intersect($channels, $expectedChannels)) === count($expectedChannels);
            }
        );
    }

    /** @test */
    public function project_launched_email_content_is_accurate()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'project_launched',
            'project_name' => 'Organic Farming Cooperative',
            'project_type' => 'Agriculture',
            'target_amount' => 150000.00,
            'minimum_contribution' => 2500.00,
            'project_description' => 'Establishing a sustainable organic farming cooperative to supply local markets.',
            'expected_returns' => '18-22% annual returns',
            'project_duration' => '24 months'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test subject line
        $this->assertStringContainsString('New Community Project Launched: Organic Farming Cooperative', $mailMessage->subject);
        
        // Test greeting
        $this->assertStringContainsString('Exciting opportunity, Community Investor!', $mailMessage->greeting);
        
        // Test project details
        $this->assertStringContainsString('Organic Farming Cooperative', $mailMessage->render());
        $this->assertStringContainsString('Agriculture', $mailMessage->render());
        $this->assertStringContainsString('K150,000.00', $mailMessage->render());
        $this->assertStringContainsString('K2,500.00', $mailMessage->render());
        $this->assertStringContainsString('Establishing a sustainable organic farming cooperative', $mailMessage->render());
        $this->assertStringContainsString('18-22% annual returns', $mailMessage->render());
        $this->assertStringContainsString('24 months', $mailMessage->render());
    }

    /** @test */
    public function voting_opened_email_includes_all_voting_details()
    {
        Mail::fake();
        
        $votingOptions = [
            'Option A: Expand to second location immediately',
            'Option B: Reinvest profits for equipment upgrade',
            'Option C: Distribute 50% profits to contributors'
        ];
        
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'voting_opened',
            'project_name' => 'Restaurant Chain Project',
            'voting_topic' => 'Profit Utilization Strategy',
            'voting_description' => 'We need community input on how to best utilize our Q4 profits for maximum benefit.',
            'voting_deadline' => 'February 20, 2025 at 11:59 PM',
            'voting_options' => $votingOptions,
            'user_voting_power' => 8.5
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test voting details
        $this->assertStringContainsString('Community Vote: Restaurant Chain Project', $mailMessage->subject);
        $this->assertStringContainsString('Your voice matters, Community Investor!', $mailMessage->greeting);
        $this->assertStringContainsString('Profit Utilization Strategy', $mailMessage->render());
        $this->assertStringContainsString('February 20, 2025 at 11:59 PM', $mailMessage->render());
        $this->assertStringContainsString('8.5%', $mailMessage->render());
        
        // Test voting options
        foreach ($votingOptions as $option) {
            $this->assertStringContainsString($option, $mailMessage->render());
        }
        
        // Test democratic messaging
        $this->assertStringContainsString('Your voice matters', $mailMessage->render());
        $this->assertStringContainsString('Democratic decision-making', $mailMessage->render());
    }

    /** @test */
    public function voting_closing_soon_email_creates_urgency()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'voting_closing_soon',
            'project_name' => 'Tech Startup Investment',
            'voting_topic' => 'Series A Funding Participation',
            'hours_remaining' => 8,
            'current_participation' => 72.5,
            'leading_option' => 'Participate in Series A with additional K50,000'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test urgency in subject and content
        $this->assertStringContainsString('🚨 Final Hours to Vote', $mailMessage->subject);
        $this->assertStringContainsString('Last chance, Community Investor!', $mailMessage->greeting);
        $this->assertStringContainsString('8 hours', $mailMessage->render());
        $this->assertStringContainsString('72.5%', $mailMessage->render());
        $this->assertStringContainsString('Participate in Series A with additional K50,000', $mailMessage->render());
        
        // Test urgent language
        $this->assertStringContainsString('final opportunity', $mailMessage->render());
        $this->assertStringContainsString('Don\'t wait', $mailMessage->render());
        $this->assertStringContainsString('Act Immediately', $mailMessage->render());
    }

    /** @test */
    public function project_completed_email_celebrates_success()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'project_completed',
            'project_name' => 'E-commerce Platform Development',
            'project_duration' => '18 months',
            'total_profit' => 125000.00,
            'user_share' => 6250.00,
            'return_percentage' => 25.0,
            'completion_date' => '2025-01-15',
            'payment_date' => '2025-01-20'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test celebratory tone
        $this->assertStringContainsString('🏆 Project Completed', $mailMessage->subject);
        $this->assertStringContainsString('Success achieved, Community Investor!', $mailMessage->greeting);
        $this->assertStringContainsString('successfully completed', $mailMessage->render());
        
        // Test financial details
        $this->assertStringContainsString('K125,000.00', $mailMessage->render());
        $this->assertStringContainsString('K6,250.00', $mailMessage->render());
        $this->assertStringContainsString('25.0%', $mailMessage->render());
        $this->assertStringContainsString('2025-01-15', $mailMessage->render());
        $this->assertStringContainsString('2025-01-20', $mailMessage->render());
        
        // Test success messaging
        $this->assertStringContainsString('Outstanding Results', $mailMessage->render());
        $this->assertStringContainsString('Community Impact', $mailMessage->render());
        $this->assertStringContainsString('wealth building in action', $mailMessage->render());
    }

    /** @test */
    public function profit_distribution_paid_email_confirms_payment()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'profit_distribution_paid',
            'project_name' => 'Manufacturing Plant Project',
            'distribution_amount' => 4500.00,
            'payment_method' => 'Airtel Money',
            'transaction_id' => 'AM123456789',
            'distribution_period' => 'Quarterly',
            'total_earnings_to_date' => 27000.00
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test payment confirmation
        $this->assertStringContainsString('✅ Profit Paid', $mailMessage->subject);
        $this->assertStringContainsString('Payment confirmed, Community Investor!', $mailMessage->greeting);
        $this->assertStringContainsString('successfully paid', $mailMessage->render());
        
        // Test payment details
        $this->assertStringContainsString('K4,500.00', $mailMessage->render());
        $this->assertStringContainsString('Airtel Money', $mailMessage->render());
        $this->assertStringContainsString('AM123456789', $mailMessage->render());
        $this->assertStringContainsString('Quarterly', $mailMessage->render());
        $this->assertStringContainsString('K27,000.00', $mailMessage->render());
        
        // Test investment success messaging
        $this->assertStringContainsString('Community Investment Success', $mailMessage->render());
        $this->assertStringContainsString('tangible results', $mailMessage->render());
    }

    /** @test */
    public function quarterly_community_report_provides_comprehensive_overview()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'quarterly_community_report',
            'report_period' => 'Q4 2024',
            'total_projects' => 18,
            'completed_projects' => 12,
            'total_community_profit' => 450000.00,
            'user_total_earnings' => 22500.00,
            'active_contributions' => 125000.00,
            'top_performing_project' => 'Solar Energy Initiative'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test report overview
        $this->assertStringContainsString('📈 Community Report: Q4 2024', $mailMessage->subject);
        $this->assertStringContainsString('Community update, Community Investor!', $mailMessage->greeting);
        
        // Test community performance metrics
        $this->assertStringContainsString('18', $mailMessage->render()); // total projects
        $this->assertStringContainsString('12', $mailMessage->render()); // completed projects
        $this->assertStringContainsString('K450,000.00', $mailMessage->render()); // total profit
        $this->assertStringContainsString('Solar Energy Initiative', $mailMessage->render());
        
        // Test personal performance
        $this->assertStringContainsString('K22,500.00', $mailMessage->render()); // user earnings
        $this->assertStringContainsString('K125,000.00', $mailMessage->render()); // active contributions
        
        // Test forward-looking content
        $this->assertStringContainsString('Looking Ahead', $mailMessage->render());
        $this->assertStringContainsString('New project opportunities', $mailMessage->render());
    }

    /** @test */
    public function project_risk_alert_email_maintains_transparency_and_confidence()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'project_risk_alert',
            'project_name' => 'Construction Development Project',
            'risk_type' => 'Material Cost Escalation',
            'risk_description' => 'Construction material costs have increased by 15% due to supply chain disruptions.',
            'mitigation_plan' => 'We are negotiating with alternative suppliers and adjusting project timeline to manage costs effectively.',
            'impact_level' => 'Medium',
            'action_required' => true,
            'voting_scheduled' => true
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test transparent but reassuring tone
        $this->assertStringContainsString('⚠️ Project Alert', $mailMessage->subject);
        $this->assertStringContainsString('Important update, Community Investor', $mailMessage->greeting);
        $this->assertStringContainsString('keep you informed', $mailMessage->render());
        
        // Test risk details
        $this->assertStringContainsString('Material Cost Escalation', $mailMessage->render());
        $this->assertStringContainsString('15% due to supply chain disruptions', $mailMessage->render());
        $this->assertStringContainsString('Medium', $mailMessage->render());
        
        // Test mitigation and confidence
        $this->assertStringContainsString('negotiating with alternative suppliers', $mailMessage->render());
        $this->assertStringContainsString('complete transparency', $mailMessage->render());
        $this->assertStringContainsString('committed to working together', $mailMessage->render());
        $this->assertStringContainsString('Community vote will be scheduled', $mailMessage->render());
    }

    /** @test */
    public function contribution_acknowledged_email_welcomes_and_informs()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'contribution_acknowledged',
            'project_name' => 'Healthcare Clinic Development',
            'contribution_amount' => 18000.00,
            'ownership_percentage' => 7.2,
            'total_raised' => 200000.00,
            'target_amount' => 250000.00,
            'progress_percentage' => 80.0,
            'contributor_rank' => '5th largest'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test welcoming tone
        $this->assertStringContainsString('✅ Contribution Confirmed', $mailMessage->subject);
        $this->assertStringContainsString('Thank you, Community Investor!', $mailMessage->greeting);
        $this->assertStringContainsString('successfully processed and confirmed', $mailMessage->render());
        
        // Test contribution details
        $this->assertStringContainsString('K18,000.00', $mailMessage->render());
        $this->assertStringContainsString('7.2%', $mailMessage->render());
        $this->assertStringContainsString('K200,000.00', $mailMessage->render());
        $this->assertStringContainsString('K250,000.00', $mailMessage->render());
        $this->assertStringContainsString('80.0%', $mailMessage->render());
        $this->assertStringContainsString('5th largest', $mailMessage->render());
        
        // Test engagement messaging
        $this->assertStringContainsString('What Happens Next', $mailMessage->render());
        $this->assertStringContainsString('Stay Engaged', $mailMessage->render());
        $this->assertStringContainsString('Welcome to this community investment', $mailMessage->render());
    }

    /** @test */
    public function community_notification_database_storage_includes_correct_metadata()
    {
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'project_milestone_reached',
            'project_name' => 'Renewable Energy Project',
            'milestone_name' => '75% Completion Milestone',
            'progress_percentage' => 75.0
        ]);

        $databaseData = $notification->toDatabase($this->user);

        // Test database structure
        $this->assertArrayHasKey('type', $databaseData);
        $this->assertArrayHasKey('message', $databaseData);
        $this->assertArrayHasKey('data', $databaseData);
        $this->assertArrayHasKey('priority', $databaseData);

        // Test data content
        $this->assertEquals('project_milestone_reached', $databaseData['type']);
        $this->assertEquals('normal', $databaseData['priority']);
        $this->assertStringContainsString('Renewable Energy Project', $databaseData['message']);
        $this->assertStringContainsString('75% Completion Milestone', $databaseData['message']);
    }

    /** @test */
    public function community_notification_priorities_are_set_correctly()
    {
        // High priority notifications
        $highPriorityNotifications = [
            new MyGrowNetCommunityNotification(['type' => 'project_risk_alert']),
            new MyGrowNetCommunityNotification(['type' => 'voting_closing_soon'])
        ];

        foreach ($highPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('high', $databaseData['priority']);
        }

        // Medium priority notifications
        $mediumPriorityNotifications = [
            new MyGrowNetCommunityNotification(['type' => 'project_completed']),
            new MyGrowNetCommunityNotification(['type' => 'profit_distribution_paid']),
            new MyGrowNetCommunityNotification(['type' => 'voting_opened']),
            new MyGrowNetCommunityNotification(['type' => 'voting_results']),
            new MyGrowNetCommunityNotification(['type' => 'project_funding_target_reached'])
        ];

        foreach ($mediumPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('medium', $databaseData['priority']);
        }

        // Normal priority notifications
        $normalPriorityNotifications = [
            new MyGrowNetCommunityNotification(['type' => 'project_launched']),
            new MyGrowNetCommunityNotification(['type' => 'project_update_posted']),
            new MyGrowNetCommunityNotification(['type' => 'project_milestone_reached']),
            new MyGrowNetCommunityNotification(['type' => 'voting_reminder']),
            new MyGrowNetCommunityNotification(['type' => 'profit_distribution_calculated']),
            new MyGrowNetCommunityNotification(['type' => 'quarterly_community_report']),
            new MyGrowNetCommunityNotification(['type' => 'new_project_opportunity']),
            new MyGrowNetCommunityNotification(['type' => 'contribution_acknowledged'])
        ];

        foreach ($normalPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('normal', $databaseData['priority']);
        }
    }

    /** @test */
    public function bulk_community_notifications_deliver_to_all_recipients()
    {
        Notification::fake();

        $users = User::factory()->count(12)->create();
        
        $data = [
            'type' => 'new_project_opportunity',
            'project_name' => 'Green Energy Initiative',
            'target_amount' => 300000.00,
            'minimum_contribution' => 5000.00,
            'project_type' => 'Renewable Energy'
        ];

        $this->notificationService->sendBulkCommunityNotifications($users->toArray(), $data);

        // Verify all users received the notification
        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                MyGrowNetCommunityNotification::class,
                function ($notification) use ($data) {
                    return $notification->data['type'] === $data['type'] &&
                           $notification->data['project_name'] === $data['project_name'];
                }
            );
        }

        // Verify correct number of notifications sent
        Notification::assertSentTimes(MyGrowNetCommunityNotification::class, 12);
    }

    /** @test */
    public function community_notification_content_handles_missing_optional_data_gracefully()
    {
        Mail::fake();
        
        // Test with minimal data
        $notification = new MyGrowNetCommunityNotification([
            'type' => 'project_launched',
            'project_name' => 'Basic Community Project',
            'target_amount' => 50000.00,
            'minimum_contribution' => 1000.00
            // Missing optional fields like project_description, expected_returns, etc.
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Should still render without errors
        $this->assertStringContainsString('Basic Community Project', $mailMessage->render());
        $this->assertStringContainsString('K50,000.00', $mailMessage->render());
        $this->assertStringContainsString('K1,000.00', $mailMessage->render());
        
        // Should handle missing optional data gracefully
        $this->assertNotNull($mailMessage->render());
    }

    /** @test */
    public function voting_results_email_handles_both_voted_and_non_voted_users()
    {
        Mail::fake();
        
        // Test for user who voted
        $votedNotification = new MyGrowNetCommunityNotification([
            'type' => 'voting_results',
            'project_name' => 'Community Center Project',
            'voting_topic' => 'Facility Design Selection',
            'winning_option' => 'Modern Multi-Purpose Design',
            'winning_percentage' => 68.5,
            'total_participation' => 82.0,
            'user_voted' => true,
            'user_choice' => 'Modern Multi-Purpose Design',
            'implementation_date' => '2025-03-01'
        ]);

        $votedMail = $votedNotification->toMail($this->user);
        
        $this->assertStringContainsString('Your choice won!', $votedMail->render());
        $this->assertStringContainsString('Thank You for Participating', $votedMail->render());
        
        // Test for user who didn't vote
        $nonVotedNotification = new MyGrowNetCommunityNotification([
            'type' => 'voting_results',
            'project_name' => 'Community Center Project',
            'voting_topic' => 'Facility Design Selection',
            'winning_option' => 'Modern Multi-Purpose Design',
            'winning_percentage' => 68.5,
            'total_participation' => 82.0,
            'user_voted' => false,
            'user_choice' => '',
            'implementation_date' => '2025-03-01'
        ]);

        $nonVotedMail = $nonVotedNotification->toMail($this->user);
        
        $this->assertStringContainsString('You did not vote', $nonVotedMail->render());
        $this->assertStringContainsString('Future Participation', $nonVotedMail->render());
        $this->assertStringContainsString('encourage you to participate', $nonVotedMail->render());
    }
}