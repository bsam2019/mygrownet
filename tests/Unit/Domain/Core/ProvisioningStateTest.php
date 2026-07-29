<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Enums\ProvisioningState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProvisioningStateTest extends TestCase
{
    #[Test]
    public function all_cases_are_strings()
    {
        $this->assertEquals('installing', ProvisioningState::Installing->value);
        $this->assertEquals('configuring', ProvisioningState::Configuring->value);
        $this->assertEquals('active', ProvisioningState::Active->value);
        $this->assertEquals('disabled', ProvisioningState::Disabled->value);
        $this->assertEquals('failed', ProvisioningState::Failed->value);
    }

    #[Test]
    public function installing_can_transition_to_configuring_or_failed()
    {
        $state = ProvisioningState::Installing;
        $this->assertTrue($state->canTransitionTo(ProvisioningState::Configuring));
        $this->assertTrue($state->canTransitionTo(ProvisioningState::Failed));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Active));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Disabled));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Installing));
    }

    #[Test]
    public function configuring_can_transition_to_active_or_failed()
    {
        $state = ProvisioningState::Configuring;
        $this->assertTrue($state->canTransitionTo(ProvisioningState::Active));
        $this->assertTrue($state->canTransitionTo(ProvisioningState::Failed));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Installing));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Disabled));
    }

    #[Test]
    public function active_can_transition_to_disabled()
    {
        $state = ProvisioningState::Active;
        $this->assertTrue($state->canTransitionTo(ProvisioningState::Disabled));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Installing));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Configuring));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Failed));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Active));
    }

    #[Test]
    public function disabled_can_transition_to_installing()
    {
        $state = ProvisioningState::Disabled;
        $this->assertTrue($state->canTransitionTo(ProvisioningState::Installing));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Configuring));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Active));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Failed));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Disabled));
    }

    #[Test]
    public function failed_can_transition_to_installing()
    {
        $state = ProvisioningState::Failed;
        $this->assertTrue($state->canTransitionTo(ProvisioningState::Installing));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Configuring));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Active));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Disabled));
        $this->assertFalse($state->canTransitionTo(ProvisioningState::Failed));
    }

    #[Test]
    public function labels_are_readable()
    {
        $this->assertEquals('Installing', ProvisioningState::Installing->label());
        $this->assertEquals('Configuring', ProvisioningState::Configuring->label());
        $this->assertEquals('Active', ProvisioningState::Active->label());
        $this->assertEquals('Disabled', ProvisioningState::Disabled->label());
        $this->assertEquals('Failed', ProvisioningState::Failed->label());
    }

    #[Test]
    public function try_from_works()
    {
        $this->assertSame(ProvisioningState::Active, ProvisioningState::tryFrom('active'));
        $this->assertNull(ProvisioningState::tryFrom('unknown'));
    }
}
