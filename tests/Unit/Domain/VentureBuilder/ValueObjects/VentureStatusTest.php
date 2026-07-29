<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\ValueObjects;

use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureStatusTest extends TestCase
{
    #[Test]
    public function draft_creates_correct_status(): void
    {
        $status = VentureStatus::draft();
        $this->assertSame('draft', $status->value());
        $this->assertTrue($status->isDraft());
        $this->assertFalse($status->isFunding());
        $this->assertFalse($status->isFunded());
        $this->assertFalse($status->isActive());
    }

    #[Test]
    public function review_creates_correct_status(): void
    {
        $status = VentureStatus::review();
        $this->assertSame('review', $status->value());
    }

    #[Test]
    public function approved_creates_correct_status(): void
    {
        $status = VentureStatus::approved();
        $this->assertSame('approved', $status->value());
    }

    #[Test]
    public function funding_creates_correct_status(): void
    {
        $status = VentureStatus::funding();
        $this->assertSame('funding', $status->value());
        $this->assertTrue($status->isFunding());
        $this->assertTrue($status->canAcceptInvestments());
    }

    #[Test]
    public function funded_creates_correct_status(): void
    {
        $status = VentureStatus::funded();
        $this->assertSame('funded', $status->value());
        $this->assertTrue($status->isFunded());
        $this->assertFalse($status->canAcceptInvestments());
    }

    #[Test]
    public function active_creates_correct_status(): void
    {
        $status = VentureStatus::active();
        $this->assertSame('active', $status->value());
        $this->assertTrue($status->isActive());
    }

    #[Test]
    public function completed_creates_correct_status(): void
    {
        $status = VentureStatus::completed();
        $this->assertSame('completed', $status->value());
    }

    #[Test]
    public function cancelled_creates_correct_status(): void
    {
        $status = VentureStatus::cancelled();
        $this->assertSame('cancelled', $status->value());
    }

    #[Test]
    public function from_string_creates_correct_status(): void
    {
        $this->assertSame('review', VentureStatus::fromString('review')->value());
        $this->assertSame('funded', VentureStatus::fromString('funded')->value());
    }

    #[Test]
    public function from_string_throws_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        VentureStatus::fromString('nonexistent');
    }

    #[Test]
    public function can_transition_from_draft_to_review(): void
    {
        $this->assertTrue(VentureStatus::draft()->canTransitionTo('review'));
    }

    #[Test]
    public function can_transition_from_draft_to_cancelled(): void
    {
        $this->assertTrue(VentureStatus::draft()->canTransitionTo('cancelled'));
    }

    #[Test]
    public function cannot_transition_from_draft_to_active(): void
    {
        $this->assertFalse(VentureStatus::draft()->canTransitionTo('active'));
    }

    #[Test]
    public function can_transition_from_review_to_approved(): void
    {
        $this->assertTrue(VentureStatus::review()->canTransitionTo('approved'));
    }

    #[Test]
    public function can_transition_from_review_back_to_draft(): void
    {
        $this->assertTrue(VentureStatus::review()->canTransitionTo('draft'));
    }

    #[Test]
    public function can_transition_from_approved_to_funding(): void
    {
        $this->assertTrue(VentureStatus::approved()->canTransitionTo('funding'));
    }

    #[Test]
    public function cannot_transition_from_approved_to_active(): void
    {
        $this->assertFalse(VentureStatus::approved()->canTransitionTo('active'));
    }

    #[Test]
    public function can_transition_from_funding_to_funded(): void
    {
        $this->assertTrue(VentureStatus::funding()->canTransitionTo('funded'));
    }

    #[Test]
    public function can_transition_from_funding_to_cancelled(): void
    {
        $this->assertTrue(VentureStatus::funding()->canTransitionTo('cancelled'));
    }

    #[Test]
    public function can_transition_from_funded_to_active(): void
    {
        $this->assertTrue(VentureStatus::funded()->canTransitionTo('active'));
    }

    #[Test]
    public function can_transition_from_active_to_completed(): void
    {
        $this->assertTrue(VentureStatus::active()->canTransitionTo('completed'));
    }

    #[Test]
    public function completed_has_no_transitions(): void
    {
        $this->assertEmpty(VentureStatus::completed()->allowedTransitions());
        $this->assertFalse(VentureStatus::completed()->canTransitionTo('draft'));
    }

    #[Test]
    public function cancelled_has_no_transitions(): void
    {
        $this->assertEmpty(VentureStatus::cancelled()->allowedTransitions());
        $this->assertFalse(VentureStatus::cancelled()->canTransitionTo('funding'));
    }

    #[Test]
    public function allowed_transitions_returns_correct_array(): void
    {
        $this->assertSame(['funded', 'cancelled'], VentureStatus::funding()->allowedTransitions());
        $this->assertSame(['completed'], VentureStatus::active()->allowedTransitions());
    }

    #[Test]
    public function can_accept_investments_only_when_funding(): void
    {
        $this->assertTrue(VentureStatus::funding()->canAcceptInvestments());
        $this->assertFalse(VentureStatus::draft()->canAcceptInvestments());
        $this->assertFalse(VentureStatus::approved()->canAcceptInvestments());
        $this->assertFalse(VentureStatus::funded()->canAcceptInvestments());
        $this->assertFalse(VentureStatus::active()->canAcceptInvestments());
        $this->assertFalse(VentureStatus::completed()->canAcceptInvestments());
        $this->assertFalse(VentureStatus::cancelled()->canAcceptInvestments());
    }
}
