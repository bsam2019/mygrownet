<?php

namespace Tests\Integration;

use App\Models\User;
use App\Notifications\MyGrowNetCommissionNotification;
use App\Notifications\MyGrowNetAchievementNotification;
use App\Services\MyGrowNetNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected MyGrowNetNotificationService $notificationService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->notificationService = new MyGrowNetNotificationService();
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+260971234567',
            'notification_preferences' => [
                'email' => true,
                'sms' => true,
                'commission' => true,
                'achievement' => true
            ]
        ]);
    }

    /** @test */
    public function commission_notification_delivers_via_correct_channels()
    {
        Notification::fake();

        $this->notificationService->sendFiveLevelCommissionNotification(
            $this->user,
            500.00,
            2,
            'Jane Smith'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommissionNotification::class,
            function ($notification, $channels) {
                // Should include database and mail channels
                $expectedChannels = ['database', 'mail'];
                return count(array_intersect($channels, $expectedChannels)) === count($expectedChannels);
            }
        );
    }

    /** @test */
    public function achievement_notification_delivers_via_correct_channels()
    {
        Notification::fake();

        $this->notificationService->sendTierAdvancementNotification(
            $this->user,
            'Gold',
            'Silver',
            2000.00
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification, $channels) {
                // Should include database and mail channels
                $expectedChannels = ['database', 'mail'];
                return count(array_intersect($channels, $expectedChannels)) === count($expectedChannels);
            }
        );
    }

    /** @test */
    public function commission_notification_email_content_is_accurate()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommissionNotification([
            'type' => 'five_level_commission_earned',
            'amount' => 750.00,
            'level' => 3,
            'referral_name' => 'Alice Johnson',
            'commission_type' => 'Team Volume'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test subject line
        $this->assertStringContainsString('Level 3 Commission Earned', $mailMessage->subject);
        
        // Test greeting
        $this->assertStringContainsString('Great news, John Doe!', $mailMessage->greeting);
        
        // Test amount formatting
        $this->assertStringContainsString('K750.00', $mailMessage->render());
        
        // Test level information
        $this->assertStringContainsString('Level 3', $mailMessage->render());
        
        // Test referral name
        $this->assertStringContainsString('Alice Johnson', $mailMessage->render());
        
        // Test commission type
        $this->assertStringContainsString('Team Volume', $mailMessage->render());
    }

    /** @test */
    public function tier_advancement_notification_email_content_is_accurate()
    {
        Mail::fake();
        
        $notification = new MyGrowNetAchievementNotification([
            'type' => 'tier_advancement',
            'new_tier' => 'Diamond',
            'previous_tier' => 'Gold',
            'achievement_bonus' => 5000.00,
            'new_benefits' => [
                'Higher commission rates',
                'Quarterly profit sharing',
                'Premium physical rewards'
            ]
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test subject line
        $this->assertStringContainsString('Advanced to Diamond Tier', $mailMessage->subject);
        
        // Test greeting
        $this->assertStringContainsString('Amazing achievement, John Doe!', $mailMessage->greeting);
        
        // Test tier information
        $this->assertStringContainsString('Diamond', $mailMessage->render());
        $this->assertStringContainsString('Gold', $mailMessage->render());
        
        // Test achievement bonus
        $this->assertStringContainsString('K5,000.00', $mailMessage->render());
        
        // Test benefits
        $this->assertStringContainsString('Higher commission rates', $mailMessage->render());
        $this->assertStringContainsString('Quarterly profit sharing', $mailMessage->render());
    }

    /** @test */
    public function physical_reward_notification_email_content_is_accurate()
    {
        Mail::fake();
        
        $notification = new MyGrowNetAchievementNotification([
            'type' => 'physical_reward_earned',
            'reward_type' => 'Smartphone (Samsung Galaxy S24)',
            'reward_value' => 6500.00,
            'qualifying_tier' => 'Gold',
            'maintenance_period' => 12,
            'estimated_delivery' => '3-4 weeks'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test subject line
        $this->assertStringContainsString('Physical Reward Earned: Smartphone', $mailMessage->subject);
        
        // Test reward details
        $this->assertStringContainsString('Samsung Galaxy S24', $mailMessage->render());
        $this->assertStringContainsString('K6,500.00', $mailMessage->render());
        $this->assertStringContainsString('Gold', $mailMessage->render());
        $this->assertStringContainsString('12 months', $mailMessage->render());
        $this->assertStringContainsString('3-4 weeks', $mailMessage->render());
    }

    /** @test */
    public function raffle_winner_notification_email_content_is_accurate()
    {
        Mail::fake();
        
        $notification = new MyGrowNetAchievementNotification([
            'type' => 'raffle_winner',
            'raffle_name' => 'Monthly Grand Prize Draw',
            'prize_won' => 'Toyota Corolla',
            'prize_value' => 'K85,000',
            'claim_instructions' => 'Visit our office within 30 days with valid ID'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test subject line
        $this->assertStringContainsString('WINNER! You Won: Toyota Corolla', $mailMessage->subject);
        
        // Test winner celebration
        $this->assertStringContainsString('CONGRATULATIONS, John Doe!', $mailMessage->greeting);
        $this->assertStringContainsString('YOU\'RE A WINNER!', $mailMessage->render());
        
        // Test prize details
        $this->assertStringContainsString('Toyota Corolla', $mailMessage->render());
        $this->assertStringContainsString('K85,000', $mailMessage->render());
        $this->assertStringContainsString('Monthly Grand Prize Draw', $mailMessage->render());
        
        // Test claim instructions
        $this->assertStringContainsString('Visit our office within 30 days', $mailMessage->render());
    }

    /** @test */
    public function commission_payment_failed_notification_provides_clear_information()
    {
        Mail::fake();
        
        $notification = new MyGrowNetCommissionNotification([
            'type' => 'commission_payment_failed',
            'amount' => 1500.00,
            'failure_reason' => 'Mobile money account temporarily unavailable',
            'retry_date' => 'Tomorrow at 10 AM'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test subject line indicates issue
        $this->assertStringContainsString('Payment Issue', $mailMessage->subject);
        
        // Test reassuring tone
        $this->assertStringContainsString('don\'t worry', $mailMessage->render());
        
        // Test failure details
        $this->assertStringContainsString('K1,500.00', $mailMessage->render());
        $this->assertStringContainsString('Mobile money account temporarily unavailable', $mailMessage->render());
        $this->assertStringContainsString('Tomorrow at 10 AM', $mailMessage->render());
        
        // Test next steps
        $this->assertStringContainsString('automatically retry', $mailMessage->render());
        $this->assertStringContainsString('No action needed', $mailMessage->render());
    }

    /** @test */
    public function monthly_summary_notification_includes_comprehensive_breakdown()
    {
        Mail::fake();
        
        $breakdown = [
            'Referral Commissions' => 2500.00,
            'Team Volume Bonuses' => 1800.00,
            'Performance Bonuses' => 1200.00,
            'Leadership Bonuses' => 800.00
        ];
        
        $notification = new MyGrowNetCommissionNotification([
            'type' => 'monthly_commission_summary',
            'total_earnings' => 6300.00,
            'commission_count' => 28,
            'month' => 'January 2025',
            'breakdown' => $breakdown
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test subject line
        $this->assertStringContainsString('Monthly Summary - January 2025', $mailMessage->subject);
        
        // Test total earnings
        $this->assertStringContainsString('K6,300.00', $mailMessage->render());
        $this->assertStringContainsString('28', $mailMessage->render());
        
        // Test breakdown details
        foreach ($breakdown as $type => $amount) {
            $this->assertStringContainsString($type, $mailMessage->render());
            $this->assertStringContainsString('K' . number_format($amount, 2), $mailMessage->render());
        }
    }

    /** @test */
    public function notification_database_storage_includes_correct_metadata()
    {
        $notification = new MyGrowNetCommissionNotification([
            'type' => 'team_volume_bonus_earned',
            'amount' => 1200.00,
            'team_volume' => 25000.00,
            'bonus_percentage' => 5.0,
            'tier' => 'Gold'
        ]);

        $databaseData = $notification->toDatabase($this->user);

        // Test database structure
        $this->assertArrayHasKey('type', $databaseData);
        $this->assertArrayHasKey('message', $databaseData);
        $this->assertArrayHasKey('data', $databaseData);
        $this->assertArrayHasKey('priority', $databaseData);

        // Test data content
        $this->assertEquals('team_volume_bonus_earned', $databaseData['type']);
        $this->assertEquals('medium', $databaseData['priority']);
        $this->assertStringContainsString('K1,200.00', $databaseData['message']);
        $this->assertStringContainsString('K25,000.00', $databaseData['message']);
    }

    /** @test */
    public function notification_priorities_are_set_correctly()
    {
        // High priority notifications
        $highPriorityNotifications = [
            new MyGrowNetCommissionNotification(['type' => 'commission_payment_failed']),
            new MyGrowNetAchievementNotification(['type' => 'tier_advancement']),
            new MyGrowNetAchievementNotification(['type' => 'raffle_winner']),
            new MyGrowNetAchievementNotification(['type' => 'asset_ownership_transferred'])
        ];

        foreach ($highPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('high', $databaseData['priority']);
        }

        // Medium priority notifications
        $mediumPriorityNotifications = [
            new MyGrowNetCommissionNotification(['type' => 'five_level_commission_earned']),
            new MyGrowNetCommissionNotification(['type' => 'team_volume_bonus_earned']),
            new MyGrowNetAchievementNotification(['type' => 'physical_reward_earned']),
            new MyGrowNetAchievementNotification(['type' => 'recognition_event'])
        ];

        foreach ($mediumPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('medium', $databaseData['priority']);
        }

        // Normal priority notifications
        $normalPriorityNotifications = [
            new MyGrowNetCommissionNotification(['type' => 'commission_payment_processed']),
            new MyGrowNetCommissionNotification(['type' => 'monthly_commission_summary']),
            new MyGrowNetAchievementNotification(['type' => 'achievement_unlocked']),
            new MyGrowNetAchievementNotification(['type' => 'badge_earned'])
        ];

        foreach ($normalPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('normal', $databaseData['priority']);
        }
    }

    /** @test */
    public function notifications_respect_user_preferences()
    {
        // User with email disabled
        $userNoEmail = User::factory()->create([
            'notification_preferences' => [
                'email' => false,
                'sms' => true,
                'commission' => true
            ]
        ]);

        $notification = new MyGrowNetCommissionNotification([
            'type' => 'five_level_commission_earned'
        ]);

        // Mock the config to return user preferences
        config(['mygrownet.notifications.email_enabled' => false]);

        $channels = $notification->via($userNoEmail);

        // Should not include mail channel
        $this->assertNotContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    /** @test */
    public function bulk_notifications_deliver_to_all_recipients()
    {
        Notification::fake();

        $users = User::factory()->count(10)->create();
        
        $data = [
            'type' => 'recognition_event',
            'event_name' => 'Annual Awards Ceremony',
            'event_date' => 'March 15, 2025'
        ];

        $this->notificationService->sendBulkAchievementNotifications($users->toArray(), $data);

        // Verify all users received the notification
        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                MyGrowNetAchievementNotification::class,
                function ($notification) use ($data) {
                    return $notification->data['type'] === $data['type'] &&
                           $notification->data['event_name'] === $data['event_name'];
                }
            );
        }

        // Verify correct number of notifications sent
        Notification::assertSentTimes(MyGrowNetAchievementNotification::class, 10);
    }

    /** @test */
    public function notification_content_handles_missing_optional_data_gracefully()
    {
        Mail::fake();
        
        // Test with minimal data
        $notification = new MyGrowNetCommissionNotification([
            'type' => 'five_level_commission_earned',
            'amount' => 500.00,
            'level' => 1
            // Missing referral_name and commission_type
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Should still render without errors
        $this->assertStringContainsString('K500.00', $mailMessage->render());
        $this->assertStringContainsString('Level 1', $mailMessage->render());
        
        // Should use default values for missing data
        $this->assertStringContainsString('Team Member', $mailMessage->render()); // Default referral name
        $this->assertStringContainsString('Referral', $mailMessage->render()); // Default commission type
    }
}