<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Enums\HealthStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HealthStatusTest extends TestCase
{
    #[Test]
    public function all_cases_are_strings()
    {
        $this->assertEquals('healthy', HealthStatus::Healthy->value);
        $this->assertEquals('degraded', HealthStatus::Degraded->value);
        $this->assertEquals('maintenance', HealthStatus::Maintenance->value);
        $this->assertEquals('unavailable', HealthStatus::Unavailable->value);
        $this->assertEquals('offline', HealthStatus::Offline->value);
    }

    #[Test]
    public function healthy_and_degraded_are_operational()
    {
        $this->assertTrue(HealthStatus::Healthy->isOperational());
        $this->assertTrue(HealthStatus::Degraded->isOperational());
    }

    #[Test]
    public function other_statuses_are_not_operational()
    {
        $this->assertFalse(HealthStatus::Maintenance->isOperational());
        $this->assertFalse(HealthStatus::Unavailable->isOperational());
        $this->assertFalse(HealthStatus::Offline->isOperational());
    }

    #[Test]
    public function labels_are_readable()
    {
        $this->assertEquals('Healthy', HealthStatus::Healthy->label());
        $this->assertEquals('Degraded', HealthStatus::Degraded->label());
        $this->assertEquals('Maintenance', HealthStatus::Maintenance->label());
        $this->assertEquals('Unavailable', HealthStatus::Unavailable->label());
        $this->assertEquals('Offline', HealthStatus::Offline->label());
    }

    #[Test]
    public function severity_increases_with_worsening_status()
    {
        $this->assertEquals(0, HealthStatus::Healthy->severity());
        $this->assertEquals(1, HealthStatus::Degraded->severity());
        $this->assertEquals(2, HealthStatus::Maintenance->severity());
        $this->assertEquals(3, HealthStatus::Unavailable->severity());
        $this->assertEquals(4, HealthStatus::Offline->severity());
    }

    #[Test]
    public function try_from_works_with_valid_values()
    {
        $this->assertSame(HealthStatus::Healthy, HealthStatus::tryFrom('healthy'));
        $this->assertSame(HealthStatus::Offline, HealthStatus::tryFrom('offline'));
    }

    #[Test]
    public function try_from_returns_null_for_invalid()
    {
        $this->assertNull(HealthStatus::tryFrom('unknown'));
    }
}
