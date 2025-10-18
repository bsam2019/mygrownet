<?php

namespace Tests\Integration;

use App\Models\User;
use App\Notifications\MyGrowNetAssetNotification;
use App\Services\MyGrowNetNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AssetNotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected MyGrowNetNotificationService $notificationService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->notificationService = new MyGrowNetNotificationService();
        $this->user = User::factory()->create([
            'name' => 'Asset Owner',
            'email' => 'asset@example.com',
            'phone' => '+260971234567',
            'notification_preferences' => [
                'email' => true,
                'sms' => true,
                'asset' => true
            ]
        ]);
    }

    /** @test */
    public function asset_notification_delivers_via_correct_channels()
    {
        Notification::fake();

        $this->notificationService->sendAssetAllocationApprovedNotification(
            $this->user,
            'Smartphone (iPhone 15)',
            4500.00,
            'Silver'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification, $channels) {
                // Should include database and mail channels
                $expectedChannels = ['database', 'mail'];
                return count(array_intersect($channels, $expectedChannels)) === count($expectedChannels);
            }
        );
    }

    /** @test */
    public function asset_allocation_approved_email_content_is_accurate()
    {
        Mail::fake();
        
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_allocation_approved',
            'asset_type' => 'Motorbike (Honda CB150)',
            'asset_model' => 'Honda CB150R Sport',
            'asset_value' => 12500.00,
            'qualifying_tier' => 'Gold',
            'maintenance_period' => 12,
            'estimated_delivery' => '3-4 weeks'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test subject line
        $this->assertStringContainsString('Asset Allocation Approved: Motorbike', $mailMessage->subject);
        
        // Test greeting
        $this->assertStringContainsString('Congratulations, Asset Owner!', $mailMessage->greeting);
        
        // Test asset details
        $this->assertStringContainsString('Motorbike (Honda CB150)', $mailMessage->render());
        $this->assertStringContainsString('Honda CB150R Sport', $mailMessage->render());
        $this->assertStringContainsString('K12,500.00', $mailMessage->render());
        $this->assertStringContainsString('Gold', $mailMessage->render());
        $this->assertStringContainsString('12 months', $mailMessage->render());
        $this->assertStringContainsString('3-4 weeks', $mailMessage->render());
    }

    /** @test */
    public function asset_delivery_scheduled_email_includes_all_details()
    {
        Mail::fake();
        
        $deliveryInstructions = [
            'Ensure someone is available to receive delivery',
            'Have ID ready for verification',
            'Clear parking space for motorbike'
        ];
        
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_delivery_scheduled',
            'asset_type' => 'Motorbike (Honda CB150)',
            'asset_model' => 'Honda CB150R',
            'delivery_date' => 'February 15, 2025',
            'delivery_time' => '10:00 AM - 12:00 PM',
            'delivery_address' => '123 Main Street, Lusaka',
            'delivery_instructions' => $deliveryInstructions,
            'contact_number' => '+260971234567'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test delivery details
        $this->assertStringContainsString('February 15, 2025', $mailMessage->render());
        $this->assertStringContainsString('10:00 AM - 12:00 PM', $mailMessage->render());
        $this->assertStringContainsString('123 Main Street, Lusaka', $mailMessage->render());
        $this->assertStringContainsString('+260971234567', $mailMessage->render());
        
        // Test delivery instructions
        foreach ($deliveryInstructions as $instruction) {
            $this->assertStringContainsString($instruction, $mailMessage->render());
        }
    }

    /** @test */
    public function asset_maintenance_warning_email_provides_clear_guidance()
    {
        Mail::fake();
        
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_maintenance_warning',
            'asset_type' => 'Car (Toyota Corolla)',
            'warning_type' => 'Team volume below requirements',
            'current_tier' => 'Silver',
            'required_tier' => 'Gold',
            'current_volume' => 35000.00,
            'required_volume' => 50000.00,
            'grace_period' => '45 days',
            'support_contact' => '+260971234567'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test warning details
        $this->assertStringContainsString('Asset Maintenance Warning', $mailMessage->subject);
        $this->assertStringContainsString('Team volume below requirements', $mailMessage->render());
        $this->assertStringContainsString('Silver', $mailMessage->render());
        $this->assertStringContainsString('Gold', $mailMessage->render());
        $this->assertStringContainsString('K35,000.00', $mailMessage->render());
        $this->assertStringContainsString('K50,000.00', $mailMessage->render());
        $this->assertStringContainsString('45 days', $mailMessage->render());
        
        // Test supportive tone
        $this->assertStringContainsString('Don\'t worry', $mailMessage->render());
        $this->assertStringContainsString('we\'re here to help', $mailMessage->render());
        $this->assertStringContainsString('temporary setback', $mailMessage->render());
    }

    /** @test */
    public function asset_ownership_completed_email_celebrates_achievement()
    {
        Mail::fake();
        
        $incomeOpportunities = [
            'Rent out as delivery vehicle (K500-800/month)',
            'Use for personal transport business',
            'Enroll in asset management program'
        ];
        
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_ownership_completed',
            'asset_type' => 'Car (Toyota Hilux)',
            'asset_value' => 85000.00,
            'transfer_date' => '2025-01-25',
            'income_opportunities' => $incomeOpportunities,
            'asset_management_contact' => '+260971234567'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test celebratory tone
        $this->assertStringContainsString('CONGRATULATIONS', $mailMessage->greeting);
        $this->assertStringContainsString('IT\'S OFFICIAL!', $mailMessage->render());
        $this->assertStringContainsString('🎊', $mailMessage->render());
        
        // Test ownership details
        $this->assertStringContainsString('Toyota Hilux', $mailMessage->render());
        $this->assertStringContainsString('K85,000.00', $mailMessage->render());
        $this->assertStringContainsString('2025-01-25', $mailMessage->render());
        
        // Test income opportunities
        foreach ($incomeOpportunities as $opportunity) {
            $this->assertStringContainsString($opportunity, $mailMessage->render());
        }
    }

    /** @test */
    public function asset_income_report_email_provides_comprehensive_breakdown()
    {
        Mail::fake();
        
        $incomeBreakdown = [
            'Rental Income' => 2500.00,
            'Asset Appreciation' => 400.00,
            'Management Fees' => -250.00
        ];
        
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_income_report',
            'asset_type' => 'Property (Rental House)',
            'report_period' => 'Monthly',
            'income_generated' => 2650.00,
            'total_income' => 15900.00,
            'income_breakdown' => $incomeBreakdown,
            'next_payment_date' => '2025-02-01'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test income summary
        $this->assertStringContainsString('Asset Income Report', $mailMessage->subject);
        $this->assertStringContainsString('K2,650.00', $mailMessage->render());
        $this->assertStringContainsString('K15,900.00', $mailMessage->render());
        $this->assertStringContainsString('2025-02-01', $mailMessage->render());
        
        // Test income breakdown
        foreach ($incomeBreakdown as $source => $amount) {
            $this->assertStringContainsString($source, $mailMessage->render());
            $this->assertStringContainsString('K' . number_format($amount, 2), $mailMessage->render());
        }
    }

    /** @test */
    public function asset_valuation_update_handles_appreciation_and_depreciation()
    {
        Mail::fake();
        
        // Test appreciation
        $appreciationNotification = new MyGrowNetAssetNotification([
            'type' => 'asset_valuation_update',
            'asset_type' => 'Property (Commercial Plot)',
            'previous_value' => 75000.00,
            'current_value' => 82000.00,
            'valuation_date' => '2025-01-15',
            'next_valuation' => 'January 2026'
        ]);

        $appreciationMail = $appreciationNotification->toMail($this->user);
        
        $this->assertStringContainsString('📈', $appreciationMail->subject);
        $this->assertStringContainsString('Great News!', $appreciationMail->render());
        $this->assertStringContainsString('appreciated', $appreciationMail->render());
        $this->assertStringContainsString('K7,000.00', $appreciationMail->render());
        
        // Test depreciation
        $depreciationNotification = new MyGrowNetAssetNotification([
            'type' => 'asset_valuation_update',
            'asset_type' => 'Car (Toyota Corolla)',
            'previous_value' => 65000.00,
            'current_value' => 58000.00,
            'valuation_date' => '2025-01-15',
            'next_valuation' => 'January 2026'
        ]);

        $depreciationMail = $depreciationNotification->toMail($this->user);
        
        $this->assertStringContainsString('📉', $depreciationMail->subject);
        $this->assertStringContainsString('Market Update', $depreciationMail->render());
        $this->assertStringContainsString('decreased', $depreciationMail->render());
        $this->assertStringContainsString('K7,000.00', $depreciationMail->render());
        $this->assertStringContainsString('normal market fluctuation', $depreciationMail->render());
    }

    /** @test */
    public function asset_buyback_offer_email_presents_balanced_options()
    {
        Mail::fake();
        
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_buyback_offer',
            'asset_type' => 'Car (Toyota Corolla)',
            'current_value' => 65000.00,
            'buyback_offer' => 58500.00,
            'reason_for_offer' => 'Fleet expansion opportunity',
            'offer_expiry' => '21 days'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test balanced presentation
        $this->assertStringContainsString('Special offer', $mailMessage->greeting);
        $this->assertStringContainsString('optional opportunity', $mailMessage->render());
        $this->assertStringContainsString('choice is entirely yours', $mailMessage->render());
        
        // Test offer details
        $this->assertStringContainsString('K65,000.00', $mailMessage->render());
        $this->assertStringContainsString('K58,500.00', $mailMessage->render());
        $this->assertStringContainsString('90.0%', $mailMessage->render()); // Percentage calculation
        $this->assertStringContainsString('Fleet expansion opportunity', $mailMessage->render());
        $this->assertStringContainsString('21 days', $mailMessage->render());
        
        // Test both options presented
        $this->assertStringContainsString('Option 1: Accept the Buyback', $mailMessage->render());
        $this->assertStringContainsString('Option 2: Keep Your Asset', $mailMessage->render());
        $this->assertStringContainsString('No Pressure Decision', $mailMessage->render());
    }

    /** @test */
    public function asset_maintenance_violation_email_provides_clear_options()
    {
        Mail::fake();
        
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_maintenance_violation',
            'asset_type' => 'Motorbike (Honda CB150)',
            'violation_type' => 'Maintenance requirements not met for 60 days',
            'violation_date' => '2025-01-15',
            'payment_plan_available' => true,
            'payment_plan_amount' => 800.00,
            'asset_recovery_date' => '2025-02-15',
            'support_contact' => '+260971234567'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test urgent but supportive tone
        $this->assertStringContainsString('🚨', $mailMessage->subject);
        $this->assertStringContainsString('Urgent notice', $mailMessage->greeting);
        $this->assertStringContainsString('regret to inform', $mailMessage->render());
        
        // Test violation details
        $this->assertStringContainsString('Maintenance requirements not met for 60 days', $mailMessage->render());
        $this->assertStringContainsString('2025-01-15', $mailMessage->render());
        $this->assertStringContainsString('2025-02-15', $mailMessage->render());
        
        // Test available options
        $this->assertStringContainsString('Option 1: Payment Plan', $mailMessage->render());
        $this->assertStringContainsString('K800.00', $mailMessage->render());
        $this->assertStringContainsString('Option 2: Asset Recovery', $mailMessage->render());
        
        // Test supportive messaging
        $this->assertStringContainsString('doesn\'t affect your MyGrowNet membership', $mailMessage->render());
        $this->assertStringContainsString('We\'re Still Here for You', $mailMessage->render());
        $this->assertStringContainsString('come back stronger', $mailMessage->render());
    }

    /** @test */
    public function asset_notification_database_storage_includes_correct_metadata()
    {
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_delivered',
            'asset_type' => 'Smartphone (iPhone 15)',
            'delivery_date' => '2025-01-20',
            'maintenance_period' => 12
        ]);

        $databaseData = $notification->toDatabase($this->user);

        // Test database structure
        $this->assertArrayHasKey('type', $databaseData);
        $this->assertArrayHasKey('message', $databaseData);
        $this->assertArrayHasKey('data', $databaseData);
        $this->assertArrayHasKey('priority', $databaseData);

        // Test data content
        $this->assertEquals('asset_delivered', $databaseData['type']);
        $this->assertEquals('medium', $databaseData['priority']);
        $this->assertStringContainsString('Smartphone (iPhone 15)', $databaseData['message']);
        $this->assertStringContainsString('delivered', $databaseData['message']);
    }

    /** @test */
    public function asset_notification_priorities_are_set_correctly()
    {
        // High priority notifications
        $highPriorityNotifications = [
            new MyGrowNetAssetNotification(['type' => 'asset_maintenance_violation']),
            new MyGrowNetAssetNotification(['type' => 'asset_ownership_completed'])
        ];

        foreach ($highPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('high', $databaseData['priority']);
        }

        // Medium priority notifications
        $mediumPriorityNotifications = [
            new MyGrowNetAssetNotification(['type' => 'asset_allocation_approved']),
            new MyGrowNetAssetNotification(['type' => 'asset_delivery_scheduled']),
            new MyGrowNetAssetNotification(['type' => 'asset_delivered']),
            new MyGrowNetAssetNotification(['type' => 'asset_maintenance_warning']),
            new MyGrowNetAssetNotification(['type' => 'asset_ownership_pending']),
            new MyGrowNetAssetNotification(['type' => 'asset_buyback_offer'])
        ];

        foreach ($mediumPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('medium', $databaseData['priority']);
        }

        // Normal priority notifications
        $normalPriorityNotifications = [
            new MyGrowNetAssetNotification(['type' => 'asset_allocation_pending']),
            new MyGrowNetAssetNotification(['type' => 'asset_maintenance_reminder']),
            new MyGrowNetAssetNotification(['type' => 'asset_income_report']),
            new MyGrowNetAssetNotification(['type' => 'asset_valuation_update']),
            new MyGrowNetAssetNotification(['type' => 'asset_management_enrollment'])
        ];

        foreach ($normalPriorityNotifications as $notification) {
            $databaseData = $notification->toDatabase($this->user);
            $this->assertEquals('normal', $databaseData['priority']);
        }
    }

    /** @test */
    public function bulk_asset_notifications_deliver_to_all_recipients()
    {
        Notification::fake();

        $users = User::factory()->count(8)->create();
        
        $data = [
            'type' => 'asset_valuation_update',
            'asset_type' => 'Property Portfolio',
            'previous_value' => 100000.00,
            'current_value' => 108000.00,
            'valuation_date' => '2025-01-15'
        ];

        $this->notificationService->sendBulkAssetNotifications($users->toArray(), $data);

        // Verify all users received the notification
        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                MyGrowNetAssetNotification::class,
                function ($notification) use ($data) {
                    return $notification->data['type'] === $data['type'] &&
                           $notification->data['asset_type'] === $data['asset_type'];
                }
            );
        }

        // Verify correct number of notifications sent
        Notification::assertSentTimes(MyGrowNetAssetNotification::class, 8);
    }

    /** @test */
    public function asset_notification_content_handles_missing_optional_data_gracefully()
    {
        Mail::fake();
        
        // Test with minimal data
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_allocation_approved',
            'asset_type' => 'Basic Asset',
            'asset_value' => 1000.00,
            'qualifying_tier' => 'Bronze'
            // Missing optional fields like asset_model, maintenance_period, etc.
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Should still render without errors
        $this->assertStringContainsString('Basic Asset', $mailMessage->render());
        $this->assertStringContainsString('K1,000.00', $mailMessage->render());
        $this->assertStringContainsString('Bronze', $mailMessage->render());
        
        // Should handle missing optional data gracefully
        $this->assertNotNull($mailMessage->render());
    }

    /** @test */
    public function asset_management_enrollment_email_provides_comprehensive_program_details()
    {
        Mail::fake();
        
        $managementServices = [
            'Professional property management',
            'Tenant screening and management',
            'Maintenance and repairs coordination',
            'Monthly income reporting',
            'Market analysis and optimization'
        ];
        
        $notification = new MyGrowNetAssetNotification([
            'type' => 'asset_management_enrollment',
            'asset_type' => 'Property (Rental House)',
            'management_services' => $managementServices,
            'expected_income' => 2500.00,
            'management_fee' => '12%',
            'enrollment_deadline' => '45 days',
            'contact_person' => 'Property Management Team',
            'contact_number' => '+260971234567'
        ]);

        $mailMessage = $notification->toMail($this->user);

        // Test program overview
        $this->assertStringContainsString('Asset Management Program', $mailMessage->subject);
        $this->assertStringContainsString('maximize income', $mailMessage->render());
        $this->assertStringContainsString('minimizing the time and effort', $mailMessage->render());
        
        // Test financial projections
        $this->assertStringContainsString('K2,500.00', $mailMessage->render());
        $this->assertStringContainsString('12%', $mailMessage->render());
        $this->assertStringContainsString('K2,200.00', $mailMessage->render()); // Net income calculation
        
        // Test services
        foreach ($managementServices as $service) {
            $this->assertStringContainsString($service, $mailMessage->render());
        }
        
        // Test contact information
        $this->assertStringContainsString('45 days', $mailMessage->render());
        $this->assertStringContainsString('Property Management Team', $mailMessage->render());
        $this->assertStringContainsString('+260971234567', $mailMessage->render());
    }
}