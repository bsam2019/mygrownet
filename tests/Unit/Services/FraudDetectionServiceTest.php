<?php

namespace Tests\Unit\Services;

use App\Models\DeviceFingerprint;
use App\Models\Investment;
use App\Models\SuspiciousActivity;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\FraudDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FraudDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private FraudDetectionService $fraudDetectionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudDetectionService = app(FraudDetectionService::class);
    }

    public function test_detects_duplicate_accounts_by_device_fingerprint()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $deviceInfo = [
            'screen' => '1920x1080',
            'timezone' => 'Africa/Lusaka',
            'language' => 'en-US',
            'platform' => 'Win32',
        ];

        $browserInfo = [
            'name' => 'Chrome',
            'version' => '91.0.4472.124',
        ];

        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        // Create fingerprint for first user
        $fingerprint = DeviceFingerprint::generateFingerprint($deviceInfo, $browserInfo, $userAgent);
        DeviceFingerprint::create([
            'user_id' => $user1->id,
            'fingerprint_hash' => $fingerprint,
            'user_agent' => $userAgent,
            'ip_address' => '192.168.1.1',
            'device_info' => $deviceInfo,
            'browser_info' => $browserInfo,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        // Detect duplicate for second user with same fingerprint
        $this->fraudDetectionService->detectDuplicateAccounts($user2, $deviceInfo, $browserInfo, $userAgent);

        $this->assertDatabaseHas('suspicious_activities', [
            'user_id' => $user2->id,
            'activity_type' => SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
        ]);
    }

    public function test_detects_multiple_accounts_from_same_ip()
    {
        $users = User::factory()->count(4)->create(['created_at' => now()->subDays(3)]);
        $ipAddress = '192.168.1.1';

        // Create device fingerprints for all users from same IP
        foreach ($users as $user) {
            DeviceFingerprint::create([
                'user_id' => $user->id,
                'fingerprint_hash' => 'unique_hash_' . $user->id,
                'user_agent' => 'Mozilla/5.0',
                'ip_address' => $ipAddress,
                'device_info' => ['screen' => '1920x1080'],
                'browser_info' => ['name' => 'Chrome'],
                'first_seen_at' => now(),
                'last_seen_at' => now(),
            ]);
        }

        $newUser = User::factory()->create();
        
        $this->fraudDetectionService->detectDuplicateAccounts(
            $newUser,
            ['screen' => '1920x1080'],
            ['name' => 'Chrome'],
            'Mozilla/5.0'
        );

        $this->assertDatabaseHas('suspicious_activities', [
            'user_id' => $newUser->id,
            'activity_type' => SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
            'severity' => SuspiciousActivity::SEVERITY_MEDIUM,
        ]);
    }

    public function test_detects_rapid_investments()
    {
        $user = User::factory()->create();

        // Create 5 investments in the last 24 hours
        Investment::factory()->count(5)->create([
            'user_id' => $user->id,
            'created_at' => now()->subHours(12),
        ]);

        $this->fraudDetectionService->detectRapidInvestments($user, 1000);

        $this->assertDatabaseHas('suspicious_activities', [
            'user_id' => $user->id,
            'activity_type' => SuspiciousActivity::TYPE_RAPID_INVESTMENTS,
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
        ]);
    }

    public function test_detects_unusually_large_investment_amounts()
    {
        $user = User::factory()->create();

        // Create previous investments with average of 1000
        Investment::factory()->count(3)->create([
            'user_id' => $user->id,
            'amount' => 1000,
        ]);

        // Detect investment 10x larger than average
        $this->fraudDetectionService->detectRapidInvestments($user, 10000);

        $this->assertDatabaseHas('suspicious_activities', [
            'user_id' => $user->id,
            'activity_type' => SuspiciousActivity::TYPE_RAPID_INVESTMENTS,
            'severity' => SuspiciousActivity::SEVERITY_MEDIUM,
        ]);
    }

    public function test_detects_rapid_withdrawal_attempts()
    {
        $user = User::factory()->create();

        // Create 3 withdrawal requests in the last 24 hours
        WithdrawalRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'created_at' => now()->subHours(12),
        ]);

        $this->fraudDetectionService->detectUnusualWithdrawals($user, 500);

        $this->assertDatabaseHas('suspicious_activities', [
            'user_id' => $user->id,
            'activity_type' => SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL,
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
        ]);
    }

    public function test_detects_early_full_withdrawal()
    {
        $user = User::factory()->create([
            'total_investment_amount' => 5000,
            'total_profit_earnings' => 500,
        ]);

        // Create recent investment
        Investment::factory()->create([
            'user_id' => $user->id,
            'amount' => 5000,
            'created_at' => now()->subDays(3),
        ]);

        // Attempt to withdraw 90% of total balance
        $this->fraudDetectionService->detectUnusualWithdrawals($user, 4950);

        $this->assertDatabaseHas('suspicious_activities', [
            'user_id' => $user->id,
            'activity_type' => SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL,
            'severity' => SuspiciousActivity::SEVERITY_CRITICAL,
        ]);
    }

    public function test_detects_suspicious_login_patterns()
    {
        $user = User::factory()->create();

        // Create device fingerprints from multiple IPs
        $ips = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '203.0.113.1', '198.51.100.1'];
        foreach ($ips as $ip) {
            DeviceFingerprint::create([
                'user_id' => $user->id,
                'fingerprint_hash' => 'hash_' . $ip,
                'user_agent' => 'Mozilla/5.0',
                'ip_address' => $ip,
                'device_info' => ['screen' => '1920x1080'],
                'browser_info' => ['name' => 'Chrome'],
                'first_seen_at' => now()->subDays(15),
                'last_seen_at' => now()->subDays(1),
            ]);
        }

        $this->fraudDetectionService->detectSuspiciousLogin($user, '203.0.113.1', 'Mozilla/5.0');

        $this->assertDatabaseHas('suspicious_activities', [
            'user_id' => $user->id,
            'activity_type' => SuspiciousActivity::TYPE_SUSPICIOUS_LOGIN,
            'severity' => SuspiciousActivity::SEVERITY_MEDIUM,
        ]);
    }

    public function test_detects_excessive_failed_login_attempts()
    {
        $user = User::factory()->create([
            'failed_login_attempts' => 5,
            'last_failed_login_at' => now()->subMinutes(10),
        ]);

        $this->fraudDetectionService->detectSuspiciousLogin($user, '192.168.1.1', 'Mozilla/5.0');

        $this->assertDatabaseHas('suspicious_activities', [
            'user_id' => $user->id,
            'activity_type' => SuspiciousActivity::TYPE_SUSPICIOUS_LOGIN,
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
        ]);
    }

    public function test_calculates_risk_score_correctly()
    {
        $user = User::factory()->create(['created_at' => now()->subDays(3)]);

        // Create suspicious activities with different severities
        SuspiciousActivity::factory()->create([
            'user_id' => $user->id,
            'severity' => SuspiciousActivity::SEVERITY_CRITICAL,
            'is_resolved' => false,
        ]);

        SuspiciousActivity::factory()->create([
            'user_id' => $user->id,
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
            'is_resolved' => false,
        ]);

        $riskScore = $this->fraudDetectionService->calculateRiskScore($user);

        // Critical (50) + High (35) + New account (20) + No ID verification (15) = 120, capped at 100
        $this->assertEquals(100, $riskScore);
        $this->assertEquals(100, $user->fresh()->risk_score);
    }

    public function test_auto_blocks_high_risk_users()
    {
        $user = User::factory()->create();

        // Create enough suspicious activities to trigger auto-block
        SuspiciousActivity::factory()->count(2)->create([
            'user_id' => $user->id,
            'severity' => SuspiciousActivity::SEVERITY_CRITICAL,
            'is_resolved' => false,
        ]);

        $wasBlocked = $this->fraudDetectionService->autoBlockIfHighRisk($user);

        $this->assertTrue($wasBlocked);
        $this->assertTrue($user->fresh()->is_blocked);
        $this->assertStringContains('high risk score', $user->fresh()->block_reason);
    }

    public function test_does_not_auto_block_low_risk_users()
    {
        $user = User::factory()->create([
            'created_at' => now()->subMonths(6),
            'is_id_verified' => true,
        ]);

        // Create only low severity activities
        SuspiciousActivity::factory()->create([
            'user_id' => $user->id,
            'severity' => SuspiciousActivity::SEVERITY_LOW,
            'is_resolved' => false,
        ]);

        $wasBlocked = $this->fraudDetectionService->autoBlockIfHighRisk($user);

        $this->assertFalse($wasBlocked);
        $this->assertFalse($user->fresh()->is_blocked);
    }

    public function test_blocks_user_manually()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $reason = 'Manual block for testing';

        $this->fraudDetectionService->blockUser($user, $reason, $admin->id);

        $this->assertTrue($user->fresh()->is_blocked);
        $this->assertEquals($reason, $user->fresh()->block_reason);
        $this->assertEquals($admin->id, $user->fresh()->blocked_by);
        $this->assertNotNull($user->fresh()->blocked_at);
    }

    public function test_stores_device_fingerprint_correctly()
    {
        $user = User::factory()->create();
        $deviceInfo = [
            'screen' => '1920x1080',
            'timezone' => 'Africa/Lusaka',
            'language' => 'en-US',
            'platform' => 'Win32',
        ];
        $browserInfo = [
            'name' => 'Chrome',
            'version' => '91.0.4472.124',
        ];
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        $this->fraudDetectionService->detectDuplicateAccounts($user, $deviceInfo, $browserInfo, $userAgent);

        $this->assertDatabaseHas('device_fingerprints', [
            'user_id' => $user->id,
            'user_agent' => $userAgent,
            'ip_address' => '127.0.0.1', // Default test IP
        ]);

        $fingerprint = DeviceFingerprint::where('user_id', $user->id)->first();
        $this->assertEquals($deviceInfo, $fingerprint->device_info);
        $this->assertEquals($browserInfo, $fingerprint->browser_info);
    }

    public function test_updates_existing_device_fingerprint()
    {
        $user = User::factory()->create();
        $deviceInfo = ['screen' => '1920x1080'];
        $browserInfo = ['name' => 'Chrome'];
        $userAgent = 'Mozilla/5.0';

        $fingerprint = DeviceFingerprint::generateFingerprint($deviceInfo, $browserInfo, $userAgent);
        
        // Create initial fingerprint
        $deviceFingerprint = DeviceFingerprint::create([
            'user_id' => $user->id,
            'fingerprint_hash' => $fingerprint,
            'user_agent' => $userAgent,
            'ip_address' => '192.168.1.1',
            'device_info' => $deviceInfo,
            'browser_info' => $browserInfo,
            'first_seen_at' => now()->subDays(1),
            'last_seen_at' => now()->subDays(1),
        ]);

        $originalLastSeen = $deviceFingerprint->last_seen_at;

        // Detect again with same fingerprint
        $this->fraudDetectionService->detectDuplicateAccounts($user, $deviceInfo, $browserInfo, $userAgent);

        $updatedFingerprint = $deviceFingerprint->fresh();
        $this->assertTrue($updatedFingerprint->last_seen_at->isAfter($originalLastSeen));
    }
}