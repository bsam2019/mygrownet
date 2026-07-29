<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JournalStatusTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('draft', JournalStatus::DRAFT->value);
        $this->assertSame('posted', JournalStatus::POSTED->value);
        $this->assertSame('reversed', JournalStatus::REVERSED->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(JournalStatus::DRAFT, JournalStatus::from('draft'));
        $this->assertSame(JournalStatus::POSTED, JournalStatus::from('posted'));
        $this->assertSame(JournalStatus::REVERSED, JournalStatus::from('reversed'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        JournalStatus::from('invalid');
    }

    #[Test]
    public function can_transition_to_returns_correctly()
    {
        $this->assertTrue(JournalStatus::DRAFT->canTransitionTo(JournalStatus::POSTED));
        $this->assertFalse(JournalStatus::DRAFT->canTransitionTo(JournalStatus::REVERSED));
        $this->assertTrue(JournalStatus::POSTED->canTransitionTo(JournalStatus::REVERSED));
        $this->assertFalse(JournalStatus::POSTED->canTransitionTo(JournalStatus::DRAFT));
        $this->assertFalse(JournalStatus::REVERSED->canTransitionTo(JournalStatus::DRAFT));
        $this->assertFalse(JournalStatus::REVERSED->canTransitionTo(JournalStatus::POSTED));
    }

    #[Test]
    public function is_postable_returns_correctly()
    {
        $this->assertTrue(JournalStatus::DRAFT->isPostable());
        $this->assertFalse(JournalStatus::POSTED->isPostable());
        $this->assertFalse(JournalStatus::REVERSED->isPostable());
    }

    #[Test]
    public function is_reversible_returns_correctly()
    {
        $this->assertFalse(JournalStatus::DRAFT->isReversible());
        $this->assertTrue(JournalStatus::POSTED->isReversible());
        $this->assertFalse(JournalStatus::REVERSED->isReversible());
    }
}
