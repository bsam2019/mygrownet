<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\Investment;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auditService = app(AuditService::class);
    }

    public function test_logs_investment_created()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'amount' => 5000,
            'reference' => 'INV-123',
        ]);

        $this->auditService->logInvestmentCreated($investment, 5000);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'auditable_type' => Investment::class,
            'auditable_id' => $investment->id,
            'amount' => 5000,
            'transaction_reference' => 'INV-123',
        ]);
    }

    public function test_logs_investment_updated()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $investment = Investment::factory()->create(['user_id' => $user->id]);
        $oldValues = ['status' => 'pending'];
        $newValues = ['status' => 'active'];

        $this->auditService->logInvestmentUpdated($investment, $oldValues, $newValues);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_INVESTMENT_UPDATED,
            'auditable_type' => Investment::class,
            'auditable_id' => $investment->id,
        ]);

        $auditLog = AuditLog::where('auditable_id', $investment->id)->first();
        $this->assertEquals($oldValues, $auditLog->old_values);
        $this->assertEquals($newValues, $auditLog->new_values);
    }

    public function test_logs_withdrawal_requested()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 2000,
            'net_amount' => 1800,
            'penalty_amount' => 200,
            'type' => 'partial',
            'reference' => 'WD-456',
        ]);

        $this->auditService->logWithdrawalRequested($withdrawal, 2000);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            'auditable_type' => WithdrawalRequest::class,
            'auditable_id' => $withdrawal->id,
            'amount' => 2000,
            'transaction_reference' => 'WD-456',
        ]);

        $auditLog = AuditLog::where('auditable_id', $withdrawal->id)->first();
        $metadata = $auditLog->metadata;
        $this->assertEquals('partial', $metadata['type']);
        $this->assertEquals(200, $metadata['penalty_amount']);
        $this->assertEquals(1800, $metadata['net_amount']);
    }

    public function test_logs_withdrawal_approved()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'net_amount' => 1800,
            'reference' => 'WD-456',
        ]);

        $this->auditService->logWithdrawalApproved($withdrawal, $admin->id);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event_type' => AuditLog::EVENT_WITHDRAWAL_APPROVED,
            'auditable_type' => WithdrawalRequest::class,
            'auditable_id' => $withdrawal->id,
            'amount' => 1800,
        ]);

        $auditLog = AuditLog::where('event_type', AuditLog::EVENT_WITHDRAWAL_APPROVED)->first();
        $this->assertEquals(['status' => 'pending'], $auditLog->old_values);
        $this->assertEquals(['status' => 'approved'], $auditLog->new_values);
    }

    public function test_logs_withdrawal_rejected()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $withdrawal = WithdrawalRequest::factory()->create([
            'user_id' => $user->id,
            'amount' => 2000,
        ]);

        $reason = 'Insufficient documentation';
        $this->auditService->logWithdrawalRejected($withdrawal, $admin->id, $reason);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event_type' => AuditLog::EVENT_WITHDRAWAL_REJECTED,
            'auditable_type' => WithdrawalRequest::class,
            'auditable_id' => $withdrawal->id,
            'amount' => 2000,
        ]);

        $auditLog = AuditLog::where('event_type', AuditLog::EVENT_WITHDRAWAL_REJECTED)->first();
        $this->assertEquals($reason, $auditLog->metadata['rejection_reason']);
    }

    public function test_logs_login_attempt()
    {
        $user = User::factory()->create();
        $email = 'test@example.com';

        $this->auditService->logLoginAttempt($email, true, $user->id);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_LOGIN_ATTEMPT,
        ]);

        $auditLog = AuditLog::where('event_type', AuditLog::EVENT_LOGIN_ATTEMPT)->first();
        $metadata = $auditLog->metadata;
        $this->assertEquals($email, $metadata['email']);
        $this->assertTrue($metadata['successful']);
    }

    public function test_logs_failed_login_attempt()
    {
        $email = 'test@example.com';

        $this->auditService->logLoginAttempt($email, false);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'event_type' => AuditLog::EVENT_LOGIN_ATTEMPT,
        ]);

        $auditLog = AuditLog::where('event_type', AuditLog::EVENT_LOGIN_ATTEMPT)->first();
        $metadata = $auditLog->metadata;
        $this->assertEquals($email, $metadata['email']);
        $this->assertFalse($metadata['successful']);
    }

    public function test_logs_password_changed()
    {
        $user = User::factory()->create();

        $this->auditService->logPasswordChanged($user->id);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_PASSWORD_CHANGED,
        ]);
    }

    public function test_gets_financial_audit_trail()
    {
        $user = User::factory()->create();

        // Create financial audit logs
        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'amount' => 5000,
            'created_at' => now()->subDays(5),
        ]);

        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            'amount' => 2000,
            'created_at' => now()->subDays(3),
        ]);

        // Create non-financial audit log
        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_LOGIN_ATTEMPT,
            'amount' => null,
            'created_at' => now()->subDays(2),
        ]);

        $auditTrail = $this->auditService->getFinancialAuditTrail($user->id);

        $this->assertCount(2, $auditTrail);
        // Should be ordered by created_at desc (newest first)
        $this->assertEquals(AuditLog::EVENT_WITHDRAWAL_REQUESTED, $auditTrail[0]['event_type']);
        $this->assertEquals(AuditLog::EVENT_INVESTMENT_CREATED, $auditTrail[1]['event_type']);
    }

    public function test_gets_financial_audit_trail_with_date_range()
    {
        $user = User::factory()->create();

        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'amount' => 5000,
            'created_at' => now()->subDays(10),
        ]);

        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            'amount' => 2000,
            'created_at' => now()->subDays(3),
        ]);

        $startDate = now()->subDays(5)->toDateString();
        $endDate = now()->toDateString();

        $auditTrail = $this->auditService->getFinancialAuditTrail($user->id, $startDate, $endDate);

        $this->assertCount(1, $auditTrail);
        $this->assertEquals(AuditLog::EVENT_WITHDRAWAL_REQUESTED, $auditTrail[0]['event_type']);
    }

    public function test_gets_audit_summary()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create various audit logs
        AuditLog::factory()->create([
            'user_id' => $user1->id,
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'amount' => 5000,
        ]);

        AuditLog::factory()->create([
            'user_id' => $user2->id,
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'amount' => 3000,
        ]);

        AuditLog::factory()->create([
            'user_id' => $user1->id,
            'event_type' => AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            'amount' => 2000,
        ]);

        AuditLog::factory()->create([
            'user_id' => $user1->id,
            'event_type' => AuditLog::EVENT_LOGIN_ATTEMPT,
            'amount' => null,
        ]);

        $summary = $this->auditService->getAuditSummary();

        $this->assertEquals(4, $summary['total_events']);
        $this->assertEquals(3, $summary['financial_events']);
        $this->assertEquals(10000, $summary['total_amount']);
        $this->assertEquals(2, $summary['events_by_type'][AuditLog::EVENT_INVESTMENT_CREATED]);
        $this->assertEquals(1, $summary['events_by_type'][AuditLog::EVENT_WITHDRAWAL_REQUESTED]);
        $this->assertEquals(1, $summary['events_by_type'][AuditLog::EVENT_LOGIN_ATTEMPT]);
    }

    public function test_gets_audit_summary_with_date_range()
    {
        $user = User::factory()->create();

        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'amount' => 5000,
            'created_at' => now()->subDays(10),
        ]);

        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            'amount' => 2000,
            'created_at' => now()->subDays(3),
        ]);

        $startDate = now()->subDays(5)->toDateString();
        $endDate = now()->toDateString();

        $summary = $this->auditService->getAuditSummary($startDate, $endDate);

        $this->assertEquals(1, $summary['total_events']);
        $this->assertEquals(1, $summary['financial_events']);
        $this->assertEquals(2000, $summary['total_amount']);
    }
}