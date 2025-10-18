<?php

namespace Tests\Unit\Models;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;
    public function test_logs_event_correctly()
    {
        $auditLog = AuditLog::logEvent(
            AuditLog::EVENT_INVESTMENT_CREATED,
            null,
            1,
            null,
            ['amount' => 5000],
            5000,
            'INV-123',
            ['tier_id' => 2]
        );

        $this->assertEquals(AuditLog::EVENT_INVESTMENT_CREATED, $auditLog->event_type);
        $this->assertEquals(1, $auditLog->user_id);
        $this->assertEquals(['amount' => 5000], $auditLog->new_values);
        $this->assertEquals(5000, $auditLog->amount);
        $this->assertEquals('INV-123', $auditLog->transaction_reference);
        $this->assertEquals(['tier_id' => 2], $auditLog->metadata);
    }

    public function test_checks_if_financial_transaction()
    {
        $financialLog = new AuditLog(['amount' => 1000]);
        $nonFinancialLog = new AuditLog(['amount' => null]);

        $this->assertTrue($financialLog->isFinancialTransaction());
        $this->assertFalse($nonFinancialLog->isFinancialTransaction());
    }

    public function test_formats_amount_correctly()
    {
        $auditLog = new AuditLog(['amount' => 1234.56]);
        $this->assertEquals('K1,234.56', $auditLog->formatted_amount);

        $auditLogNoAmount = new AuditLog(['amount' => null]);
        $this->assertEquals('', $auditLogNoAmount->formatted_amount);
    }
}