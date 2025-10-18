<?php

namespace Tests\Unit\Models;

use App\Models\SuspiciousActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspiciousActivityTest extends TestCase
{
    use RefreshDatabase;
    public function test_resolves_activity_correctly()
    {
        $activity = SuspiciousActivity::factory()->create([
            'is_resolved' => false,
            'resolution_action' => null,
            'admin_notes' => null,
            'resolved_at' => null,
            'resolved_by' => null,
        ]);

        $activity->resolve(SuspiciousActivity::RESOLUTION_BLOCKED, 'Test notes', 1);

        $this->assertTrue($activity->is_resolved);
        $this->assertEquals(SuspiciousActivity::RESOLUTION_BLOCKED, $activity->resolution_action);
        $this->assertEquals('Test notes', $activity->admin_notes);
        $this->assertEquals(1, $activity->resolved_by);
        $this->assertNotNull($activity->resolved_at);
    }

    public function test_checks_if_critical()
    {
        $criticalActivity = new SuspiciousActivity([
            'severity' => SuspiciousActivity::SEVERITY_CRITICAL,
        ]);

        $lowActivity = new SuspiciousActivity([
            'severity' => SuspiciousActivity::SEVERITY_LOW,
        ]);

        $this->assertTrue($criticalActivity->isCritical());
        $this->assertFalse($lowActivity->isCritical());
    }

    public function test_gets_severity_color()
    {
        $activities = [
            SuspiciousActivity::SEVERITY_LOW => 'green',
            SuspiciousActivity::SEVERITY_MEDIUM => 'yellow',
            SuspiciousActivity::SEVERITY_HIGH => 'orange',
            SuspiciousActivity::SEVERITY_CRITICAL => 'red',
        ];

        foreach ($activities as $severity => $expectedColor) {
            $activity = new SuspiciousActivity(['severity' => $severity]);
            $this->assertEquals($expectedColor, $activity->getSeverityColor());
        }
    }
}