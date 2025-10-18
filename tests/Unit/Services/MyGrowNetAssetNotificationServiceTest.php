<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\MyGrowNetNotificationService;
use App\Notifications\MyGrowNetAssetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MyGrowNetAssetNotificationServiceTest extends TestCase
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
    public function it_sends_asset_allocation_approved_notification()
    {
        $this->notificationService->sendAssetAllocationApprovedNotification(
            $this->user,
            'Smartphone (iPhone 15)',
            4500.00,
            'Silver',
            'iPhone 15 Pro 128GB',
            12,
            '2-3 weeks'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_allocation_approved' &&
                       $notification->data['asset_type'] === 'Smartphone (iPhone 15)' &&
                       $notification->data['asset_value'] === 4500.00 &&
                       $notification->data['qualifying_tier'] === 'Silver' &&
                       $notification->data['asset_model'] === 'iPhone 15 Pro 128GB' &&
                       $notification->data['maintenance_period'] === 12 &&
                       $notification->data['estimated_delivery'] === '2-3 weeks';
            }
        );
    }

    /** @test */
    public function it_sends_asset_allocation_pending_notification()
    {
        $this->notificationService->sendAssetAllocationPendingNotification(
            $this->user,
            'Motorbike (Honda CB150)',
            'Bronze',
            'Silver',
            12000.00,
            15000.00,
            '2 months'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_allocation_pending' &&
                       $notification->data['asset_type'] === 'Motorbike (Honda CB150)' &&
                       $notification->data['current_tier'] === 'Bronze' &&
                       $notification->data['required_tier'] === 'Silver' &&
                       $notification->data['current_volume'] === 12000.00 &&
                       $notification->data['required_volume'] === 15000.00 &&
                       $notification->data['time_remaining'] === '2 months';
            }
        );
    }

    /** @test */
    public function it_sends_asset_delivery_scheduled_notification()
    {
        $deliveryInstructions = [
            'Ensure someone is available to receive delivery',
            'Have ID ready for verification',
            'Clear parking space for motorbike'
        ];

        $this->notificationService->sendAssetDeliveryScheduledNotification(
            $this->user,
            'Motorbike (Honda CB150)',
            '2025-02-15',
            'Honda CB150R',
            '10:00 AM - 12:00 PM',
            '123 Main Street, Lusaka',
            $deliveryInstructions
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) use ($deliveryInstructions) {
                return $notification->data['type'] === 'asset_delivery_scheduled' &&
                       $notification->data['asset_type'] === 'Motorbike (Honda CB150)' &&
                       $notification->data['delivery_date'] === '2025-02-15' &&
                       $notification->data['asset_model'] === 'Honda CB150R' &&
                       $notification->data['delivery_time'] === '10:00 AM - 12:00 PM' &&
                       $notification->data['delivery_address'] === '123 Main Street, Lusaka' &&
                       $notification->data['delivery_instructions'] === $deliveryInstructions;
            }
        );
    }

    /** @test */
    public function it_sends_asset_delivered_notification()
    {
        $this->notificationService->sendAssetDeliveredNotification(
            $this->user,
            'Smartphone (Samsung Galaxy S24)',
            '2025-01-20',
            'Samsung Galaxy S24 Ultra',
            12,
            '2025-01-20'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_delivered' &&
                       $notification->data['asset_type'] === 'Smartphone (Samsung Galaxy S24)' &&
                       $notification->data['delivery_date'] === '2025-01-20' &&
                       $notification->data['asset_model'] === 'Samsung Galaxy S24 Ultra' &&
                       $notification->data['maintenance_period'] === 12 &&
                       $notification->data['maintenance_start_date'] === '2025-01-20';
            }
        );
    }

    /** @test */
    public function it_sends_asset_maintenance_reminder_notification()
    {
        $this->notificationService->sendAssetMaintenanceReminderNotification(
            $this->user,
            'Car (Toyota Corolla)',
            6,
            12,
            'Gold',
            'Gold',
            45000.00,
            50000.00
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_maintenance_reminder' &&
                       $notification->data['asset_type'] === 'Car (Toyota Corolla)' &&
                       $notification->data['maintenance_month'] === 6 &&
                       $notification->data['total_months'] === 12 &&
                       $notification->data['current_tier'] === 'Gold' &&
                       $notification->data['required_tier'] === 'Gold' &&
                       $notification->data['current_volume'] === 45000.00 &&
                       $notification->data['required_volume'] === 50000.00;
            }
        );
    }

    /** @test */
    public function it_sends_asset_maintenance_warning_notification()
    {
        $this->notificationService->sendAssetMaintenanceWarningNotification(
            $this->user,
            'Property (Plot in Lusaka)',
            'Team volume below requirements',
            'Silver',
            'Gold',
            35000.00,
            50000.00,
            '45 days'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_maintenance_warning' &&
                       $notification->data['asset_type'] === 'Property (Plot in Lusaka)' &&
                       $notification->data['warning_type'] === 'Team volume below requirements' &&
                       $notification->data['current_tier'] === 'Silver' &&
                       $notification->data['required_tier'] === 'Gold' &&
                       $notification->data['current_volume'] === 35000.00 &&
                       $notification->data['required_volume'] === 50000.00 &&
                       $notification->data['grace_period'] === '45 days';
            }
        );
    }

    /** @test */
    public function it_sends_asset_maintenance_violation_notification()
    {
        $this->notificationService->sendAssetMaintenanceViolationNotification(
            $this->user,
            'Motorbike (Honda CB150)',
            'Maintenance requirements not met for 60 days',
            '2025-01-15',
            true,
            800.00,
            '2025-02-15'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_maintenance_violation' &&
                       $notification->data['asset_type'] === 'Motorbike (Honda CB150)' &&
                       $notification->data['violation_type'] === 'Maintenance requirements not met for 60 days' &&
                       $notification->data['violation_date'] === '2025-01-15' &&
                       $notification->data['payment_plan_available'] === true &&
                       $notification->data['payment_plan_amount'] === 800.00 &&
                       $notification->data['asset_recovery_date'] === '2025-02-15';
            }
        );
    }

    /** @test */
    public function it_sends_asset_ownership_pending_notification()
    {
        $finalRequirements = [
            'Complete final documentation',
            'Verify identity documents',
            'Sign ownership transfer agreement'
        ];

        $this->notificationService->sendAssetOwnershipPendingNotification(
            $this->user,
            'Car (Toyota Hilux)',
            12,
            '2025-01-10',
            '2025-01-20',
            $finalRequirements
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) use ($finalRequirements) {
                return $notification->data['type'] === 'asset_ownership_pending' &&
                       $notification->data['asset_type'] === 'Car (Toyota Hilux)' &&
                       $notification->data['maintenance_period'] === 12 &&
                       $notification->data['completion_date'] === '2025-01-10' &&
                       $notification->data['ownership_transfer_date'] === '2025-01-20' &&
                       $notification->data['final_requirements'] === $finalRequirements;
            }
        );
    }

    /** @test */
    public function it_sends_asset_ownership_completed_notification()
    {
        $incomeOpportunities = [
            'Rent out as delivery vehicle (K500-800/month)',
            'Use for personal transport business',
            'Enroll in asset management program'
        ];

        $this->notificationService->sendAssetOwnershipCompletedNotification(
            $this->user,
            'Car (Toyota Hilux)',
            85000.00,
            '2025-01-25',
            $incomeOpportunities
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) use ($incomeOpportunities) {
                return $notification->data['type'] === 'asset_ownership_completed' &&
                       $notification->data['asset_type'] === 'Car (Toyota Hilux)' &&
                       $notification->data['asset_value'] === 85000.00 &&
                       $notification->data['transfer_date'] === '2025-01-25' &&
                       $notification->data['income_opportunities'] === $incomeOpportunities;
            }
        );
    }

    /** @test */
    public function it_sends_asset_income_report_notification()
    {
        $incomeBreakdown = [
            'Rental Income' => 1200.00,
            'Asset Appreciation' => 300.00,
            'Management Fees' => -150.00
        ];

        $this->notificationService->sendAssetIncomeReportNotification(
            $this->user,
            'Property (Rental House)',
            1350.00,
            8100.00,
            'Monthly',
            $incomeBreakdown,
            '2025-02-01'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) use ($incomeBreakdown) {
                return $notification->data['type'] === 'asset_income_report' &&
                       $notification->data['asset_type'] === 'Property (Rental House)' &&
                       $notification->data['income_generated'] === 1350.00 &&
                       $notification->data['total_income'] === 8100.00 &&
                       $notification->data['report_period'] === 'Monthly' &&
                       $notification->data['income_breakdown'] === $incomeBreakdown &&
                       $notification->data['next_payment_date'] === '2025-02-01';
            }
        );
    }

    /** @test */
    public function it_sends_asset_valuation_update_notification()
    {
        $this->notificationService->sendAssetValuationUpdateNotification(
            $this->user,
            'Property (Commercial Plot)',
            75000.00,
            82000.00,
            '2025-01-15',
            'January 2026'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_valuation_update' &&
                       $notification->data['asset_type'] === 'Property (Commercial Plot)' &&
                       $notification->data['previous_value'] === 75000.00 &&
                       $notification->data['current_value'] === 82000.00 &&
                       $notification->data['valuation_date'] === '2025-01-15' &&
                       $notification->data['next_valuation'] === 'January 2026';
            }
        );
    }

    /** @test */
    public function it_sends_asset_buyback_offer_notification()
    {
        $this->notificationService->sendAssetBuybackOfferNotification(
            $this->user,
            'Car (Toyota Corolla)',
            65000.00,
            58500.00,
            'Fleet expansion opportunity',
            '21 days'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_buyback_offer' &&
                       $notification->data['asset_type'] === 'Car (Toyota Corolla)' &&
                       $notification->data['current_value'] === 65000.00 &&
                       $notification->data['buyback_offer'] === 58500.00 &&
                       $notification->data['reason_for_offer'] === 'Fleet expansion opportunity' &&
                       $notification->data['offer_expiry'] === '21 days';
            }
        );
    }

    /** @test */
    public function it_sends_asset_management_enrollment_notification()
    {
        $managementServices = [
            'Professional property management',
            'Tenant screening and management',
            'Maintenance and repairs coordination',
            'Monthly income reporting',
            'Market analysis and optimization'
        ];

        $this->notificationService->sendAssetManagementEnrollmentNotification(
            $this->user,
            'Property (Rental House)',
            2500.00,
            $managementServices,
            '12%',
            '45 days',
            'Property Management Team'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) use ($managementServices) {
                return $notification->data['type'] === 'asset_management_enrollment' &&
                       $notification->data['asset_type'] === 'Property (Rental House)' &&
                       $notification->data['expected_income'] === 2500.00 &&
                       $notification->data['management_services'] === $managementServices &&
                       $notification->data['management_fee'] === '12%' &&
                       $notification->data['enrollment_deadline'] === '45 days' &&
                       $notification->data['contact_person'] === 'Property Management Team';
            }
        );
    }

    /** @test */
    public function it_sends_bulk_asset_notifications()
    {
        $users = User::factory()->count(5)->create();
        
        $data = [
            'type' => 'asset_valuation_update',
            'asset_type' => 'Property Portfolio',
            'previous_value' => 100000.00,
            'current_value' => 108000.00,
            'valuation_date' => '2025-01-15'
        ];

        $this->notificationService->sendBulkAssetNotifications($users->toArray(), $data);

        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                MyGrowNetAssetNotification::class,
                function ($notification) use ($data) {
                    return $notification->data['type'] === $data['type'] &&
                           $notification->data['asset_type'] === $data['asset_type'] &&
                           $notification->data['previous_value'] === $data['previous_value'] &&
                           $notification->data['current_value'] === $data['current_value'];
                }
            );
        }
    }

    /** @test */
    public function it_handles_asset_notification_sending_errors_gracefully()
    {
        // This test verifies the method exists and can be called
        $this->assertTrue(method_exists($this->notificationService, 'sendAssetNotification'));
        
        // Test that the service can handle empty or invalid data
        $this->notificationService->sendAssetNotification($this->user, [
            'type' => 'invalid_type'
        ]);
        
        // Should not throw an exception
        $this->assertTrue(true);
    }

    /** @test */
    public function asset_notifications_use_correct_default_values()
    {
        // Test with minimal required data
        $this->notificationService->sendAssetAllocationApprovedNotification(
            $this->user,
            'Basic Asset',
            1000.00,
            'Bronze'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_allocation_approved' &&
                       $notification->data['asset_model'] === '' &&
                       $notification->data['maintenance_period'] === 12 &&
                       $notification->data['estimated_delivery'] === '2-4 weeks';
            }
        );
    }

    /** @test */
    public function asset_delivered_notification_uses_current_date_as_default()
    {
        $this->notificationService->sendAssetDeliveredNotification(
            $this->user,
            'Test Asset',
            '2025-01-20'
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_delivered' &&
                       $notification->data['maintenance_start_date'] === date('Y-m-d');
            }
        );
    }

    /** @test */
    public function asset_ownership_completed_notification_uses_current_date_as_default()
    {
        $this->notificationService->sendAssetOwnershipCompletedNotification(
            $this->user,
            'Test Asset',
            50000.00
        );

        Notification::assertSentTo(
            $this->user,
            MyGrowNetAssetNotification::class,
            function ($notification) {
                return $notification->data['type'] === 'asset_ownership_completed' &&
                       $notification->data['transfer_date'] === date('Y-m-d');
            }
        );
    }
}