<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\PeriodStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PeriodStatusTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('open', PeriodStatus::OPEN->value);
        $this->assertSame('closed', PeriodStatus::CLOSED->value);
        $this->assertSame('locked', PeriodStatus::LOCKED->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(PeriodStatus::OPEN, PeriodStatus::from('open'));
        $this->assertSame(PeriodStatus::CLOSED, PeriodStatus::from('closed'));
        $this->assertSame(PeriodStatus::LOCKED, PeriodStatus::from('locked'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        PeriodStatus::from('invalid');
    }

    #[Test]
    public function can_transition_to_returns_correctly()
    {
        $this->assertTrue(PeriodStatus::OPEN->canTransitionTo(PeriodStatus::CLOSED));
        $this->assertFalse(PeriodStatus::OPEN->canTransitionTo(PeriodStatus::LOCKED));
        $this->assertFalse(PeriodStatus::OPEN->canTransitionTo(PeriodStatus::OPEN));

        $this->assertTrue(PeriodStatus::CLOSED->canTransitionTo(PeriodStatus::OPEN));
        $this->assertTrue(PeriodStatus::CLOSED->canTransitionTo(PeriodStatus::LOCKED));
        $this->assertFalse(PeriodStatus::CLOSED->canTransitionTo(PeriodStatus::CLOSED));

        $this->assertFalse(PeriodStatus::LOCKED->canTransitionTo(PeriodStatus::OPEN));
        $this->assertFalse(PeriodStatus::LOCKED->canTransitionTo(PeriodStatus::CLOSED));
    }

    #[Test]
    public function is_open_returns_correctly()
    {
        $this->assertTrue(PeriodStatus::OPEN->isOpen());
        $this->assertFalse(PeriodStatus::CLOSED->isOpen());
        $this->assertFalse(PeriodStatus::LOCKED->isOpen());
    }

    #[Test]
    public function is_postable_returns_correctly()
    {
        $this->assertTrue(PeriodStatus::OPEN->isPostable());
        $this->assertFalse(PeriodStatus::CLOSED->isPostable());
        $this->assertFalse(PeriodStatus::LOCKED->isPostable());
    }
}
