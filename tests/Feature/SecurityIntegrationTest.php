<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DeviceFingerprint;
use App\Models\IdVerification;
use App\Models\SuspiciousActivity;
use App\Models\User;
use App\Services\AuditService;
use App\Services\FraudDetectionService;
use App\Services\IdVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_fingerprint_generation_works()
    {
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

        $hash1 = DeviceFingerprint::generateFingerprint($deviceInfo, $browserInfo, $userAgent);
        $hash2 = DeviceFingerprint::generateFingerprint($deviceInfo, $browserInfo, $userAgent);

        $this->assertEquals($hash1, $hash2);
        $this->assertEquals(64, strlen($hash1)); // SHA256 produces 64 character hex string
    }

    public function test_suspicious_activity_creation_works()
    {
        $activity = SuspiciousActivity::create([
            'user_id' => null,
            'activity_type' => SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'activity_data' => ['test' => 'data'],
            'detection_rules' => ['duplicate_device_fingerprint'],
        ]);

        $this->assertDatabaseHas('suspicious_activities', [
            'activity_type' => SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
        ]);

        $this->assertTrue($activity->isCritical() === false);
        $this->assertEquals('orange', $activity->getSeverityColor());
    }

    public function test_audit_log_creation_works()
    {
        $auditLog = AuditLog::create([
            'user_id' => null,
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'auditable_type' => null,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => ['amount' => 5000],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'amount' => 5000,
            'transaction_reference' => 'INV-123',
            'metadata' => ['tier_id' => 2],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'amount' => 5000,
            'transaction_reference' => 'INV-123',
        ]);

        $this->assertTrue($auditLog->isFinancialTransaction());
        $this->assertEquals('K5,000.00', $auditLog->formatted_amount);
    }

    public function test_id_verification_document_types_available()
    {
        $types = IdVerification::getDocumentTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey(IdVerification::DOCUMENT_NATIONAL_ID, $types);
        $this->assertArrayHasKey(IdVerification::DOCUMENT_PASSPORT, $types);
        $this->assertArrayHasKey(IdVerification::DOCUMENT_DRIVERS_LICENSE, $types);
        $this->assertEquals('National ID', $types[IdVerification::DOCUMENT_NATIONAL_ID]);
    }

    public function test_database_tables_exist()
    {
        // Test that all security-related tables exist by inserting basic records
        
        // Device fingerprints table
        $this->assertDatabaseCount('device_fingerprints', 0);
        
        // Suspicious activities table
        $this->assertDatabaseCount('suspicious_activities', 0);
        
        // Audit logs table
        $this->assertDatabaseCount('audit_logs', 0);
        
        // ID verifications table
        $this->assertDatabaseCount('id_verifications', 0);
        
        // Check users table has security fields
        $this->assertTrue(\Schema::hasColumn('users', 'is_blocked'));
        $this->assertTrue(\Schema::hasColumn('users', 'requires_id_verification'));
        $this->assertTrue(\Schema::hasColumn('users', 'risk_score'));
    }

    public function test_audit_service_can_log_events()
    {
        $auditService = app(AuditService::class);
        
        // Test logging a login attempt
        $auditService->logLoginAttempt('test@example.com', true, null);
        
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => AuditLog::EVENT_LOGIN_ATTEMPT,
        ]);
        
        $auditLog = AuditLog::where('event_type', AuditLog::EVENT_LOGIN_ATTEMPT)->first();
        $this->assertEquals('test@example.com', $auditLog->metadata['email']);
        $this->assertTrue($auditLog->metadata['successful']);
    }

    public function test_id_verification_service_validates_document_formats()
    {
        $idVerificationService = app(IdVerificationService::class);
        
        // Test national ID validation
        $this->assertTrue($idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_NATIONAL_ID,
            '123456/78/9'
        ));
        
        $this->assertFalse($idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_NATIONAL_ID,
            '12345678'
        ));
        
        // Test passport validation
        $this->assertTrue($idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_PASSPORT,
            'AB123456'
        ));
        
        $this->assertFalse($idVerificationService->validateDocumentNumber(
            IdVerification::DOCUMENT_PASSPORT,
            '123456'
        ));
    }

    public function test_fraud_detection_service_can_calculate_risk_scores()
    {
        $fraudDetectionService = app(FraudDetectionService::class);
        
        // Create a user with some basic data
        $user = User::factory()->create([
            'created_at' => now()->subDays(3),
            'is_id_verified' => false,
            'risk_score' => 0,
        ]);
        
        // Create some suspicious activities
        SuspiciousActivity::create([
            'user_id' => $user->id,
            'activity_type' => SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'activity_data' => ['test' => 'data'],
            'detection_rules' => ['duplicate_device_fingerprint'],
            'is_resolved' => false,
        ]);
        
        $riskScore = $fraudDetectionService->calculateRiskScore($user);
        
        // Should have some risk score due to new account + no ID verification + suspicious activity
        $this->assertGreaterThan(0, $riskScore);
        $this->assertLessThanOrEqual(100, $riskScore);
        
        $user->refresh();
        $this->assertEquals($riskScore, $user->risk_score);
    }
}