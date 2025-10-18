<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidEmploymentStatusException;
use App\Domain\Employee\ValueObjects\EmploymentStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EmploymentStatusTest extends TestCase
{
    public function test_can_create_active_status(): void
    {
        $status = EmploymentStatus::active();
        
        $this->assertEquals(EmploymentStatus::ACTIVE, $status->getStatus());
        $this->assertTrue($status->isActive());
        $this->assertFalse($status->isInactive());
        $this->assertFalse($status->isTerminated());
        $this->assertFalse($status->isSuspended());
        $this->assertNull($status->getReason());
        $this->assertInstanceOf(DateTimeImmutable::class, $status->getEffectiveDate());
    }

    public function test_can_create_active_status_with_reason_and_date(): void
    {
        $reason = 'New hire';
        $effectiveDate = new DateTimeImmutable('2024-01-01');
        $status = EmploymentStatus::active($reason, $effectiveDate);
        
        $this->assertEquals(EmploymentStatus::ACTIVE, $status->getStatus());
        $this->assertEquals($reason, $status->getReason());
        $this->assertEquals($effectiveDate, $status->getEffectiveDate());
    }

    public function test_can_create_inactive_status(): void
    {
        $reason = 'Leave of absence';
        $status = EmploymentStatus::inactive($reason);
        
        $this->assertEquals(EmploymentStatus::INACTIVE, $status->getStatus());
        $this->assertTrue($status->isInactive());
        $this->assertFalse($status->isActive());
        $this->assertEquals($reason, $status->getReason());
    }

    public function test_can_create_terminated_status(): void
    {
        $reason = 'End of contract';
        $status = EmploymentStatus::terminated($reason);
        
        $this->assertEquals(EmploymentStatus::TERMINATED, $status->getStatus());
        $this->assertTrue($status->isTerminated());
        $this->assertFalse($status->isActive());
        $this->assertEquals($reason, $status->getReason());
    }

    public function test_can_create_suspended_status(): void
    {
        $reason = 'Disciplinary action';
        $status = EmploymentStatus::suspended($reason);
        
        $this->assertEquals(EmploymentStatus::SUSPENDED, $status->getStatus());
        $this->assertTrue($status->isSuspended());
        $this->assertFalse($status->isActive());
        $this->assertEquals($reason, $status->getReason());
    }

    public function test_can_create_from_string(): void
    {
        $status = EmploymentStatus::fromString(EmploymentStatus::ACTIVE, 'Test reason');
        
        $this->assertEquals(EmploymentStatus::ACTIVE, $status->getStatus());
        $this->assertEquals('Test reason', $status->getReason());
    }

    public function test_throws_exception_for_invalid_status(): void
    {
        $this->expectException(InvalidEmploymentStatusException::class);
        $this->expectExceptionMessage('Invalid employment status: invalid');
        
        EmploymentStatus::fromString('invalid');
    }

    public function test_valid_transitions_from_active(): void
    {
        $status = EmploymentStatus::active();
        
        $this->assertTrue($status->canTransitionTo(EmploymentStatus::INACTIVE));
        $this->assertTrue($status->canTransitionTo(EmploymentStatus::TERMINATED));
        $this->assertTrue($status->canTransitionTo(EmploymentStatus::SUSPENDED));
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::ACTIVE));
    }

    public function test_valid_transitions_from_inactive(): void
    {
        $status = EmploymentStatus::inactive('Leave');
        
        $this->assertTrue($status->canTransitionTo(EmploymentStatus::ACTIVE));
        $this->assertTrue($status->canTransitionTo(EmploymentStatus::TERMINATED));
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::INACTIVE));
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::SUSPENDED));
    }

    public function test_valid_transitions_from_suspended(): void
    {
        $status = EmploymentStatus::suspended('Disciplinary');
        
        $this->assertTrue($status->canTransitionTo(EmploymentStatus::ACTIVE));
        $this->assertTrue($status->canTransitionTo(EmploymentStatus::TERMINATED));
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::INACTIVE));
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::SUSPENDED));
    }

    public function test_no_valid_transitions_from_terminated(): void
    {
        $status = EmploymentStatus::terminated('End of contract');
        
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::ACTIVE));
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::INACTIVE));
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::SUSPENDED));
        $this->assertFalse($status->canTransitionTo(EmploymentStatus::TERMINATED));
        $this->assertEmpty($status->getValidTransitions());
    }

    public function test_successful_transition(): void
    {
        $activeStatus = EmploymentStatus::active();
        $inactiveStatus = $activeStatus->transitionTo(EmploymentStatus::INACTIVE, 'Medical leave');
        
        $this->assertEquals(EmploymentStatus::INACTIVE, $inactiveStatus->getStatus());
        $this->assertEquals('Medical leave', $inactiveStatus->getReason());
        $this->assertInstanceOf(DateTimeImmutable::class, $inactiveStatus->getEffectiveDate());
    }

    public function test_transition_with_custom_date(): void
    {
        $activeStatus = EmploymentStatus::active();
        $effectiveDate = new DateTimeImmutable('2024-06-01');
        $inactiveStatus = $activeStatus->transitionTo(EmploymentStatus::INACTIVE, 'Medical leave', $effectiveDate);
        
        $this->assertEquals($effectiveDate, $inactiveStatus->getEffectiveDate());
    }

    public function test_throws_exception_for_invalid_transition(): void
    {
        $terminatedStatus = EmploymentStatus::terminated('End of contract');
        
        $this->expectException(InvalidEmploymentStatusException::class);
        $this->expectExceptionMessage('Invalid employment status transition from terminated to active');
        
        $terminatedStatus->transitionTo(EmploymentStatus::ACTIVE, 'Rehire');
    }

    public function test_throws_exception_for_transition_to_invalid_status(): void
    {
        $activeStatus = EmploymentStatus::active();
        
        $this->expectException(InvalidEmploymentStatusException::class);
        $this->expectExceptionMessage('Invalid employment status: invalid');
        
        $activeStatus->transitionTo('invalid', 'Test');
    }

    public function test_equality_comparison(): void
    {
        $effectiveDate = new DateTimeImmutable('2024-01-01');
        $status1 = EmploymentStatus::active('New hire', $effectiveDate);
        $status2 = EmploymentStatus::active('New hire', $effectiveDate);
        $status3 = EmploymentStatus::active('Different reason', $effectiveDate);
        $status4 = EmploymentStatus::inactive('Leave', $effectiveDate);
        
        $this->assertTrue($status1->equals($status2));
        $this->assertFalse($status1->equals($status3));
        $this->assertFalse($status1->equals($status4));
    }

    public function test_string_representation(): void
    {
        $status = EmploymentStatus::active();
        
        $this->assertEquals(EmploymentStatus::ACTIVE, $status->toString());
        $this->assertEquals(EmploymentStatus::ACTIVE, (string) $status);
    }

    public function test_get_valid_statuses(): void
    {
        $validStatuses = EmploymentStatus::getValidStatuses();
        
        $this->assertContains(EmploymentStatus::ACTIVE, $validStatuses);
        $this->assertContains(EmploymentStatus::INACTIVE, $validStatuses);
        $this->assertContains(EmploymentStatus::TERMINATED, $validStatuses);
        $this->assertContains(EmploymentStatus::SUSPENDED, $validStatuses);
        $this->assertCount(4, $validStatuses);
    }

    public function test_get_valid_transitions_for_active(): void
    {
        $status = EmploymentStatus::active();
        $validTransitions = $status->getValidTransitions();
        
        $this->assertContains(EmploymentStatus::INACTIVE, $validTransitions);
        $this->assertContains(EmploymentStatus::TERMINATED, $validTransitions);
        $this->assertContains(EmploymentStatus::SUSPENDED, $validTransitions);
        $this->assertCount(3, $validTransitions);
    }

    public function test_get_valid_transitions_for_terminated(): void
    {
        $status = EmploymentStatus::terminated('End of contract');
        $validTransitions = $status->getValidTransitions();
        
        $this->assertEmpty($validTransitions);
    }
}