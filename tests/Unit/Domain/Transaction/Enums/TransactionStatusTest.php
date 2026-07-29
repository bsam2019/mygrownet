<?php

namespace Tests\Unit\Domain\Transaction\Enums;

use App\Domain\Transaction\Enums\TransactionStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransactionStatusTest extends TestCase
{
    #[Test]
    public function cases_have_expected_values(): void
    {
        $this->assertEquals('pending', TransactionStatus::PENDING->value);
        $this->assertEquals('processing', TransactionStatus::PROCESSING->value);
        $this->assertEquals('completed', TransactionStatus::COMPLETED->value);
        $this->assertEquals('failed', TransactionStatus::FAILED->value);
        $this->assertEquals('cancelled', TransactionStatus::CANCELLED->value);
        $this->assertEquals('reversed', TransactionStatus::REVERSED->value);
    }

    #[Test]
    public function from_string(): void
    {
        $this->assertSame(TransactionStatus::PENDING, TransactionStatus::from('pending'));
        $this->assertSame(TransactionStatus::COMPLETED, TransactionStatus::from('completed'));
    }

    #[Test]
    public function try_from_string(): void
    {
        $this->assertSame(TransactionStatus::FAILED, TransactionStatus::tryFrom('failed'));
        $this->assertNull(TransactionStatus::tryFrom('unknown'));
    }

    #[Test]
    public function isFinal_returns_true_for_terminal_states(): void
    {
        $this->assertTrue(TransactionStatus::COMPLETED->isFinal());
        $this->assertTrue(TransactionStatus::FAILED->isFinal());
        $this->assertTrue(TransactionStatus::CANCELLED->isFinal());
        $this->assertTrue(TransactionStatus::REVERSED->isFinal());
    }

    #[Test]
    public function isFinal_returns_false_for_non_terminal_states(): void
    {
        $this->assertFalse(TransactionStatus::PENDING->isFinal());
        $this->assertFalse(TransactionStatus::PROCESSING->isFinal());
    }

    #[Test]
    public function isSuccessful_returns_true_only_for_completed(): void
    {
        $this->assertTrue(TransactionStatus::COMPLETED->isSuccessful());
        $this->assertFalse(TransactionStatus::PENDING->isSuccessful());
        $this->assertFalse(TransactionStatus::PROCESSING->isSuccessful());
        $this->assertFalse(TransactionStatus::FAILED->isSuccessful());
        $this->assertFalse(TransactionStatus::CANCELLED->isSuccessful());
        $this->assertFalse(TransactionStatus::REVERSED->isSuccessful());
    }

    #[Test]
    public function canBeCancelled_returns_true_for_pending_and_processing(): void
    {
        $this->assertTrue(TransactionStatus::PENDING->canBeCancelled());
        $this->assertTrue(TransactionStatus::PROCESSING->canBeCancelled());
    }

    #[Test]
    public function canBeCancelled_returns_false_for_terminal_states(): void
    {
        $this->assertFalse(TransactionStatus::COMPLETED->canBeCancelled());
        $this->assertFalse(TransactionStatus::FAILED->canBeCancelled());
        $this->assertFalse(TransactionStatus::CANCELLED->canBeCancelled());
        $this->assertFalse(TransactionStatus::REVERSED->canBeCancelled());
    }

    #[Test]
    public function canBeReversed_returns_true_only_for_completed(): void
    {
        $this->assertTrue(TransactionStatus::COMPLETED->canBeReversed());
        $this->assertFalse(TransactionStatus::PENDING->canBeReversed());
        $this->assertFalse(TransactionStatus::PROCESSING->canBeReversed());
        $this->assertFalse(TransactionStatus::FAILED->canBeReversed());
        $this->assertFalse(TransactionStatus::CANCELLED->canBeReversed());
        $this->assertFalse(TransactionStatus::REVERSED->canBeReversed());
    }

    #[Test]
    public function affectsBalance_returns_true_only_for_completed(): void
    {
        $this->assertTrue(TransactionStatus::COMPLETED->affectsBalance());
        $this->assertFalse(TransactionStatus::PENDING->affectsBalance());
        $this->assertFalse(TransactionStatus::PROCESSING->affectsBalance());
        $this->assertFalse(TransactionStatus::FAILED->affectsBalance());
        $this->assertFalse(TransactionStatus::CANCELLED->affectsBalance());
        $this->assertFalse(TransactionStatus::REVERSED->affectsBalance());
    }

    #[Test]
    public function label_returns_human_readable_strings(): void
    {
        $this->assertEquals('Pending', TransactionStatus::PENDING->label());
        $this->assertEquals('Processing', TransactionStatus::PROCESSING->label());
        $this->assertEquals('Completed', TransactionStatus::COMPLETED->label());
        $this->assertEquals('Failed', TransactionStatus::FAILED->label());
        $this->assertEquals('Cancelled', TransactionStatus::CANCELLED->label());
        $this->assertEquals('Reversed', TransactionStatus::REVERSED->label());
    }

    #[Test]
    public function color_returns_expected_values(): void
    {
        $this->assertEquals('yellow', TransactionStatus::PENDING->color());
        $this->assertEquals('blue', TransactionStatus::PROCESSING->color());
        $this->assertEquals('green', TransactionStatus::COMPLETED->color());
        $this->assertEquals('red', TransactionStatus::FAILED->color());
        $this->assertEquals('gray', TransactionStatus::CANCELLED->color());
        $this->assertEquals('orange', TransactionStatus::REVERSED->color());
    }

    #[Test]
    public function icon_returns_expected_values(): void
    {
        $this->assertEquals('clock', TransactionStatus::PENDING->icon());
        $this->assertEquals('arrow-path', TransactionStatus::PROCESSING->icon());
        $this->assertEquals('check-circle', TransactionStatus::COMPLETED->icon());
        $this->assertEquals('x-circle', TransactionStatus::FAILED->icon());
        $this->assertEquals('ban', TransactionStatus::CANCELLED->icon());
        $this->assertEquals('arrow-uturn-left', TransactionStatus::REVERSED->icon());
    }

    #[Test]
    public function badgeClass_returns_css_classes(): void
    {
        $this->assertEquals('bg-yellow-100 text-yellow-800 border-yellow-200', TransactionStatus::PENDING->badgeClass());
        $this->assertEquals('bg-blue-100 text-blue-800 border-blue-200', TransactionStatus::PROCESSING->badgeClass());
        $this->assertEquals('bg-green-100 text-green-800 border-green-200', TransactionStatus::COMPLETED->badgeClass());
        $this->assertEquals('bg-red-100 text-red-800 border-red-200', TransactionStatus::FAILED->badgeClass());
        $this->assertEquals('bg-gray-100 text-gray-800 border-gray-200', TransactionStatus::CANCELLED->badgeClass());
        $this->assertEquals('bg-orange-100 text-orange-800 border-orange-200', TransactionStatus::REVERSED->badgeClass());
    }

    #[Test]
    public function all_cases_are_covered(): void
    {
        $cases = TransactionStatus::cases();
        $this->assertCount(6, $cases);
    }
}
