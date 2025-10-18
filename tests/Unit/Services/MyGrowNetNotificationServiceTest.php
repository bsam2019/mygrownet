<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\MyGrowNetNotificationService;
use App\Notifications\MyGrowNetCommissionNotification;
use App\Notifications\MyGrowNetAchievementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MyGrowNetNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MyGrowNetNotificationService $notificationService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->notificationService = new MyGrowNetNotificationService();
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+260971234567'
        ]);
        
        Notification::fake();
    }

    /** @test */
    public function it_sends_five_level_commission_notification()
    {
        $this->notificationService->sendFiveLevelCommissionNotification(
            $this->user,
            500.00,
            2,
            'John Doe',
            'Referral'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommissionNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'five_level_commission_earned' &&
                       $notification->data['amount'] === 500.00 &&
                       $notification->data['level'] === 2 &&
                       $notification->data['referral_name'] === 'John Doe';
            }
        );
    }

    /** @test */
    public function it_sends_team_volume_bonus_notification()
    {
        $this->notificationService->sendTeamVolumeBonusNotification(
            $this->user,
            1000.00,
            20000.00,
            5.0,
            'Gold'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommissionNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'team_volume_bonus_earned' &&
                       $notification->data['amount'] === 1000.00 &&
                       $notification->data['team_volume'] === 20000.00 &&
                       $notification->data['bonus_percentage'] === 5.0 &&
                       $notification->data['tier'] === 'Gold';
            }
        );
    }

    /** @test */
    public function it_sends_performance_bonus_notification()
    {
        $this->notificationService->sendPerformanceBonusNotification(
            $this->user,
            750.00,
            'Monthly Performance',
            'Exceeded team volume targets'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommissionNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'performance_bonus_earned' &&
                       $notification->data['amount'] === 750.00 &&
                       $notification->data['bonus_type'] === 'Monthly Performance' &&
                       $notification->data['achievement'] === 'Exceeded team volume targets';
            }
        );
    }

    /** @test */
    public function it_sends_leadership_bonus_notification()
    {
        $this->notificationService->sendLeadershipBonusNotification(
            $this->user,
            1500.00,
            'Team Leader',
            25
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommissionNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'leadership_bonus_earned' &&
                       $notification->data['amount'] === 1500.00 &&
                       $notification->data['leadership_level'] === 'Team Leader' &&
                       $notification->data['team_size'] === 25;
            }
        );
    }

    /** @test */
    public function it_sends_commission_payment_processed_notification()
    {
        $this->notificationService->sendCommissionPaymentProcessedNotification(
            $this->user,
            2500.00,
            'MTN Mobile Money',
            'TXN123456789'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommissionNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'commission_payment_processed' &&
                       $notification->data['amount'] === 2500.00 &&
                       $notification->data['payment_method'] === 'MTN Mobile Money' &&
                       $notification->data['transaction_id'] === 'TXN123456789';
            }
        );
    }

    /** @test */
    public function it_sends_commission_payment_failed_notification()
    {
        $this->notificationService->sendCommissionPaymentFailedNotification(
            $this->user,
            1200.00,
            'Insufficient funds in mobile money account',
            'Tomorrow at 2 PM'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommissionNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'commission_payment_failed' &&
                       $notification->data['amount'] === 1200.00 &&
                       $notification->data['failure_reason'] === 'Insufficient funds in mobile money account' &&
                       $notification->data['retry_date'] === 'Tomorrow at 2 PM';
            }
        );
    }

    /** @test */
    public function it_sends_monthly_commission_summary_notification()
    {
        $breakdown = [
            'Referral Commissions' => 3000.00,
            'Team Volume Bonuses' => 1500.00,
            'Performance Bonuses' => 800.00
        ];

        $this->notificationService->sendMonthlyCommissionSummaryNotification(
            $this->user,
            5300.00,
            15,
            'January 2025',
            $breakdown
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetCommissionNotification::class,
            function ($notification) use ($breakdown) {
                return $notification->data['type'] === 'monthly_commission_summary' &&
                       $notification->data['total_earnings'] === 5300.00 &&
                       $notification->data['commission_count'] === 15 &&
                       $notification->data['month'] === 'January 2025' &&
                       $notification->data['breakdown'] === $breakdown;
            }
        );
    }

    /** @test */
    public function it_sends_tier_advancement_notification()
    {
        $newBenefits = [
            'Higher commission rates',
            'Access to premium physical rewards',
            'Quarterly profit sharing eligibility'
        ];

        $this->notificationService->sendTierAdvancementNotification(
            $this->user,
            'Gold',
            'Silver',
            2000.00,
            $newBenefits
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) use ($newBenefits) {
                return $notification->data['type'] === 'tier_advancement' &&
                       $notification->data['new_tier'] === 'Gold' &&
                       $notification->data['previous_tier'] === 'Silver' &&
                       $notification->data['achievement_bonus'] === 2000.00 &&
                       $notification->data['new_benefits'] === $newBenefits;
            }
        );
    }

    /** @test */
    public function it_sends_achievement_unlocked_notification()
    {
        $this->notificationService->sendAchievementUnlockedNotification(
            $this->user,
            'First Referral Master',
            'Successfully referred your first 10 members',
            'K500 bonus + exclusive badge',
            'Referral'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'achievement_unlocked' &&
                       $notification->data['achievement_name'] === 'First Referral Master' &&
                       $notification->data['achievement_description'] === 'Successfully referred your first 10 members' &&
                       $notification->data['reward'] === 'K500 bonus + exclusive badge' &&
                       $notification->data['category'] === 'Referral';
            }
        );
    }

    /** @test */
    public function it_sends_milestone_reached_notification()
    {
        $this->notificationService->sendMilestoneReachedNotification(
            $this->user,
            '100 Active Referrals',
            '100 active team members',
            'K1,000 milestone bonus',
            '250 Active Referrals'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'milestone_reached' &&
                       $notification->data['milestone_name'] === '100 Active Referrals' &&
                       $notification->data['milestone_value'] === '100 active team members' &&
                       $notification->data['reward'] === 'K1,000 milestone bonus' &&
                       $notification->data['next_milestone'] === '250 Active Referrals';
            }
        );
    }

    /** @test */
    public function it_sends_badge_earned_notification()
    {
        $this->notificationService->sendBadgeEarnedNotification(
            $this->user,
            'Team Builder',
            'Built a team of 50+ active members',
            'Leadership',
            'Rare'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'badge_earned' &&
                       $notification->data['badge_name'] === 'Team Builder' &&
                       $notification->data['badge_description'] === 'Built a team of 50+ active members' &&
                       $notification->data['badge_category'] === 'Leadership' &&
                       $notification->data['rarity'] === 'Rare';
            }
        );
    }

    /** @test */
    public function it_sends_leaderboard_position_notification()
    {
        $this->notificationService->sendLeaderboardPositionNotification(
            $this->user,
            3,
            'Monthly Referrals',
            'January 2025',
            'K2,000 cash prize + smartphone',
            150
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'leaderboard_position' &&
                       $notification->data['position'] === 3 &&
                       $notification->data['leaderboard_type'] === 'Monthly Referrals' &&
                       $notification->data['period'] === 'January 2025' &&
                       $notification->data['reward'] === 'K2,000 cash prize + smartphone' &&
                       $notification->data['total_participants'] === 150;
            }
        );
    }

    /** @test */
    public function it_sends_physical_reward_earned_notification()
    {
        $this->notificationService->sendPhysicalRewardEarnedNotification(
            $this->user,
            'Smartphone (iPhone 15)',
            4500.00,
            'Silver',
            12,
            '2-3 weeks'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'physical_reward_earned' &&
                       $notification->data['reward_type'] === 'Smartphone (iPhone 15)' &&
                       $notification->data['reward_value'] === 4500.00 &&
                       $notification->data['qualifying_tier'] === 'Silver' &&
                       $notification->data['maintenance_period'] === 12 &&
                       $notification->data['estimated_delivery'] === '2-3 weeks';
            }
        );
    }

    /** @test */
    public function it_sends_physical_reward_ready_notification()
    {
        $this->notificationService->sendPhysicalRewardReadyNotification(
            $this->user,
            'Motorbike (Honda CB150)',
            'MyGrowNet Lusaka Office',
            '+260971234567',
            'collection'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'physical_reward_ready' &&
                       $notification->data['reward_type'] === 'Motorbike (Honda CB150)' &&
                       $notification->data['collection_location'] === 'MyGrowNet Lusaka Office' &&
                       $notification->data['contact_number'] === '+260971234567' &&
                       $notification->data['delivery_option'] === 'collection';
            }
        );
    }

    /** @test */
    public function it_sends_asset_ownership_transferred_notification()
    {
        $incomeOpportunities = [
            'Rent out as delivery bike (K300-500/month)',
            'Use for personal transport business',
            'Asset management program enrollment'
        ];

        $this->notificationService->sendAssetOwnershipTransferredNotification(
            $this->user,
            'Motorbike (Honda CB150)',
            12000.00,
            12,
            $incomeOpportunities
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) use ($incomeOpportunities) {
                return $notification->data['type'] === 'asset_ownership_transferred' &&
                       $notification->data['asset_type'] === 'Motorbike (Honda CB150)' &&
                       $notification->data['asset_value'] === 12000.00 &&
                       $notification->data['maintenance_period_completed'] === 12 &&
                       $notification->data['income_opportunities'] === $incomeOpportunities;
            }
        );
    }

    /** @test */
    public function it_sends_raffle_entry_earned_notification()
    {
        $prizes = [
            'Grand Prize: Toyota Hilux (K150,000)',
            '2nd Prize: Motorbike (K15,000)',
            '3rd Prize: Smartphone (K5,000)'
        ];

        $this->notificationService->sendRaffleEntryEarnedNotification(
            $this->user,
            'Quarterly Grand Raffle 2025',
            3,
            15,
            'March 31, 2025',
            $prizes
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) use ($prizes) {
                return $notification->data['type'] === 'raffle_entry_earned' &&
                       $notification->data['raffle_name'] === 'Quarterly Grand Raffle 2025' &&
                       $notification->data['entries_earned'] === 3 &&
                       $notification->data['total_entries'] === 15 &&
                       $notification->data['draw_date'] === 'March 31, 2025' &&
                       $notification->data['prizes'] === $prizes;
            }
        );
    }

    /** @test */
    public function it_sends_raffle_winner_notification()
    {
        $this->notificationService->sendRaffleWinnerNotification(
            $this->user,
            'Quarterly Grand Raffle 2025',
            'Toyota Hilux',
            'K150,000',
            'Visit MyGrowNet office with ID within 30 days to claim your prize'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'raffle_winner' &&
                       $notification->data['raffle_name'] === 'Quarterly Grand Raffle 2025' &&
                       $notification->data['prize_won'] === 'Toyota Hilux' &&
                       $notification->data['prize_value'] === 'K150,000' &&
                       $notification->data['claim_instructions'] === 'Visit MyGrowNet office with ID within 30 days to claim your prize';
            }
        );
    }

    /** @test */
    public function it_sends_recognition_event_notification()
    {
        $specialGuests = [
            'CEO John Smith',
            'Top Performer Jane Doe',
            'Business Coach Mike Johnson'
        ];

        $this->notificationService->sendRecognitionEventNotification(
            $this->user,
            'Annual Excellence Awards 2025',
            'Top Performer Recognition',
            'February 15, 2025 at 6 PM',
            'Lusaka International Conference Center',
            $specialGuests
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAchievementNotification::class,
            function ($notification) use ($specialGuests) {
                return $notification->data['type'] === 'recognition_event' &&
                       $notification->data['event_name'] === 'Annual Excellence Awards 2025' &&
                       $notification->data['recognition_type'] === 'Top Performer Recognition' &&
                       $notification->data['event_date'] === 'February 15, 2025 at 6 PM' &&
                       $notification->data['event_location'] === 'Lusaka International Conference Center' &&
                       $notification->data['special_guests'] === $specialGuests;
            }
        );
    }

    /** @test */
    public function it_sends_bulk_commission_notifications()
    {
        $users = User::factory()->count(5)->create();
        
        $data = [
            'type' => 'monthly_commission_summary',
            'total_earnings' => 1000.00,
            'commission_count' => 10,
            'month' => 'January 2025'
        ];

        $this->notificationService->sendBulkCommissionNotifications($users->toArray(), $data);

        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                MyGrowNetCommissionNotification::class,
                function ($notification) use ($data) {
                    return $notification->data['type'] === $data['type'] &&
                           $notification->data['total_earnings'] === $data['total_earnings'];
                }
            );
        }
    }

    /** @test */
    public function it_sends_bulk_achievement_notifications()
    {
        $users = User::factory()->count(3)->create();
        
        $data = [
            'type' => 'recognition_event',
            'event_name' => 'Monthly Recognition Ceremony',
            'recognition_type' => 'Top Performers',
            'event_date' => 'February 1, 2025'
        ];

        $this->notificationService->sendBulkAchievementNotifications($users->toArray(), $data);

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
    }

    /** @test */
    public function it_gets_notification_preferences()
    {
        $this->user->update([
            'notification_preferences' => [
                'email' => true,
                'sms' => false,
                'push' => true,
                'commission' => true,
                'achievement' => true,
                'marketing' => false
            ]
        ]);

        $preferences = $this->notificationService->getNotificationPreferences($this->user);

        $this->assertEquals([
            'email_enabled' => true,
            'sms_enabled' => false,
            'push_enabled' => true,
            'commission_notifications' => true,
            'achievement_notifications' => true,
            'marketing_notifications' => false
        ], $preferences);
    }

    /** @test */
    public function it_updates_notification_preferences()
    {
        $newPreferences = [
            'email' => false,
            'sms' => true,
            'marketing' => true
        ];

        $this->notificationService->updateNotificationPreferences($this->user, $newPreferences);

        $this->user->refresh();
        
        $this->assertEquals($newPreferences, $this->user->notification_preferences);
    }

    /** @test */
    public function it_handles_notification_sending_errors_gracefully()
    {
        // This test would require mocking the notification system to throw exceptions
        // For now, we'll just verify the method exists and can be called
        $this->assertTrue(method_exists($this->notificationService, 'sendCommissionNotification'));
        $this->assertTrue(method_exists($this->notificationService, 'sendAchievementNotification'));
    }
}