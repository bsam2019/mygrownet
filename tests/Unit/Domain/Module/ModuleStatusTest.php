<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\ModuleStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleStatusTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertEquals('active', ModuleStatus::ACTIVE->value);
        $this->assertEquals('beta', ModuleStatus::BETA->value);
        $this->assertEquals('coming_soon', ModuleStatus::COMING_SOON->value);
        $this->assertEquals('inactive', ModuleStatus::INACTIVE->value);
    }

    #[Test]
    public function fromString_returns_matching_case()
    {
        $this->assertSame(ModuleStatus::ACTIVE, ModuleStatus::fromString('active'));
        $this->assertSame(ModuleStatus::BETA, ModuleStatus::fromString('beta'));
        $this->assertSame(ModuleStatus::COMING_SOON, ModuleStatus::fromString('coming_soon'));
        $this->assertSame(ModuleStatus::INACTIVE, ModuleStatus::fromString('inactive'));
    }

    #[Test]
    public function fromString_defaults_to_active_for_unknown()
    {
        $this->assertSame(ModuleStatus::ACTIVE, ModuleStatus::fromString('unknown'));
    }

    #[Test]
    public function isActive_returns_true_only_for_active()
    {
        $this->assertTrue(ModuleStatus::ACTIVE->isActive());
        $this->assertFalse(ModuleStatus::BETA->isActive());
        $this->assertFalse(ModuleStatus::COMING_SOON->isActive());
        $this->assertFalse(ModuleStatus::INACTIVE->isActive());
    }

    #[Test]
    public function isBeta_returns_true_only_for_beta()
    {
        $this->assertFalse(ModuleStatus::ACTIVE->isBeta());
        $this->assertTrue(ModuleStatus::BETA->isBeta());
        $this->assertFalse(ModuleStatus::COMING_SOON->isBeta());
        $this->assertFalse(ModuleStatus::INACTIVE->isBeta());
    }

    #[Test]
    public function isComingSoon_returns_true_only_for_coming_soon()
    {
        $this->assertFalse(ModuleStatus::ACTIVE->isComingSoon());
        $this->assertFalse(ModuleStatus::BETA->isComingSoon());
        $this->assertTrue(ModuleStatus::COMING_SOON->isComingSoon());
        $this->assertFalse(ModuleStatus::INACTIVE->isComingSoon());
    }

    #[Test]
    public function labels_are_readable()
    {
        $this->assertEquals('Active', ModuleStatus::ACTIVE->label());
        $this->assertEquals('Beta', ModuleStatus::BETA->label());
        $this->assertEquals('Coming Soon', ModuleStatus::COMING_SOON->label());
        $this->assertEquals('Inactive', ModuleStatus::INACTIVE->label());
    }
}
