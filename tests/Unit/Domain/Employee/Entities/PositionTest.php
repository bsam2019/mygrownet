<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Entities;

use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\Exceptions\PositionException;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\ValueObjects\Salary;
use PHPUnit\Framework\TestCase;

final class PositionTest extends TestCase
{
    private Department $department;
    private Salary $minSalary;
    private Salary $maxSalary;

    protected function setUp(): void
    {
        $this->department = Department::create('Engineering', 'Software development department');
        $this->minSalary = Salary::fromKwacha(5000);
        $this->maxSalary = Salary::fromKwacha(10000);
    }

    public function test_can_create_position(): void
    {
        $position = Position::create(
            'Senior Developer',
            'Lead software development projects',
            $this->department,
            $this->minSalary,
            $this->maxSalary,
            true,
            5.0
        );

        $this->assertEquals('Senior Developer', $position->getTitle());
        $this->assertEquals('Lead software development projects', $position->getDescription());
        $this->assertEquals($this->department, $position->getDepartment());
        $this->assertTrue($position->getBaseSalaryMin()->equals($this->minSalary));
        $this->assertTrue($position->getBaseSalaryMax()->equals($this->maxSalary));
        $this->assertTrue($position->isCommissionEligible());
        $this->assertEquals(5.0, $position->getCommissionRate());
        $this->assertTrue($position->isActive());
        $this->assertEmpty($position->getResponsibilities());
        $this->assertEmpty($position->getRequiredPermissions());
    }

    public function test_can_add_responsibility(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $position->addResponsibility('Code review');
        $position->addResponsibility('Mentoring junior developers');

        $this->assertTrue($position->hasResponsibility('Code review'));
        $this->assertTrue($position->hasResponsibility('Mentoring junior developers'));
        $this->assertCount(2, $position->getResponsibilities());
    }

    public function test_cannot_add_empty_responsibility(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $this->expectException(PositionException::class);
        $this->expectExceptionMessage('Invalid responsibility');

        $position->addResponsibility('');
    }

    public function test_cannot_add_duplicate_responsibility(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $position->addResponsibility('Code review');
        $position->addResponsibility('Code review'); // Duplicate

        $this->assertCount(1, $position->getResponsibilities());
    }

    public function test_can_remove_responsibility(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $position->addResponsibility('Code review');
        $position->addResponsibility('Testing');
        $position->removeResponsibility('Code review');

        $this->assertFalse($position->hasResponsibility('Code review'));
        $this->assertTrue($position->hasResponsibility('Testing'));
        $this->assertCount(1, $position->getResponsibilities());
    }

    public function test_can_add_required_permission(): void
    {
        $position = Position::create(
            'Manager',
            'Team management',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $position->addRequiredPermission('manage_team');
        $position->addRequiredPermission('approve_expenses');

        $this->assertTrue($position->hasPermission('manage_team'));
        $this->assertTrue($position->hasPermission('approve_expenses'));
        $this->assertCount(2, $position->getRequiredPermissions());
    }

    public function test_cannot_add_empty_permission(): void
    {
        $position = Position::create(
            'Manager',
            'Team management',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $this->expectException(PositionException::class);
        $this->expectExceptionMessage('Invalid permission');

        $position->addRequiredPermission('');
    }

    public function test_can_remove_required_permission(): void
    {
        $position = Position::create(
            'Manager',
            'Team management',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $position->addRequiredPermission('manage_team');
        $position->addRequiredPermission('approve_expenses');
        $position->removeRequiredPermission('manage_team');

        $this->assertFalse($position->hasPermission('manage_team'));
        $this->assertTrue($position->hasPermission('approve_expenses'));
        $this->assertCount(1, $position->getRequiredPermissions());
    }

    public function test_salary_range_validation(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $testSalary1 = Salary::fromKwacha(7500); // Within range
        $testSalary2 = Salary::fromKwacha(3000); // Below range
        $testSalary3 = Salary::fromKwacha(12000); // Above range
        $testSalary4 = Salary::fromKwacha(5000); // At minimum
        $testSalary5 = Salary::fromKwacha(10000); // At maximum

        $this->assertTrue($position->isSalaryInRange($testSalary1));
        $this->assertFalse($position->isSalaryInRange($testSalary2));
        $this->assertFalse($position->isSalaryInRange($testSalary3));
        $this->assertTrue($position->isSalaryInRange($testSalary4));
        $this->assertTrue($position->isSalaryInRange($testSalary5));
    }

    public function test_commission_calculation(): void
    {
        $position = Position::create(
            'Sales Representative',
            'Sales and client management',
            $this->department,
            $this->minSalary,
            $this->maxSalary,
            true,
            10.0 // 10% commission
        );

        $baseAmount = Salary::fromKwacha(1000);
        $commission = $position->calculateCommission($baseAmount);
        $expectedCommission = Salary::fromKwacha(100); // 10% of 1000

        $this->assertTrue($commission->equals($expectedCommission));
    }

    public function test_no_commission_for_non_eligible_position(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary,
            false, // Not commission eligible
            0.0
        );

        $baseAmount = Salary::fromKwacha(1000);
        $commission = $position->calculateCommission($baseAmount);

        $this->assertTrue($commission->isZero());
    }

    public function test_can_update_details(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $position->updateDetails('Senior Developer', 'Lead development projects');

        $this->assertEquals('Senior Developer', $position->getTitle());
        $this->assertEquals('Lead development projects', $position->getDescription());
    }

    public function test_can_update_salary_range(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $newMinSalary = Salary::fromKwacha(6000);
        $newMaxSalary = Salary::fromKwacha(12000);

        $position->updateSalaryRange($newMinSalary, $newMaxSalary);

        $this->assertTrue($position->getBaseSalaryMin()->equals($newMinSalary));
        $this->assertTrue($position->getBaseSalaryMax()->equals($newMaxSalary));
    }

    public function test_can_update_commission_settings(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary,
            false,
            0.0
        );

        $position->updateCommissionSettings(true, 7.5);

        $this->assertTrue($position->isCommissionEligible());
        $this->assertEquals(7.5, $position->getCommissionRate());
    }

    public function test_can_deactivate_and_activate(): void
    {
        $position = Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $this->assertTrue($position->isActive());

        $position->deactivate();
        $this->assertFalse($position->isActive());

        $position->activate();
        $this->assertTrue($position->isActive());
    }

    public function test_cannot_create_position_with_empty_title(): void
    {
        $this->expectException(PositionException::class);
        $this->expectExceptionMessage('Invalid position title');

        Position::create(
            '',
            'Description',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );
    }

    public function test_cannot_create_position_with_title_too_long(): void
    {
        $longTitle = str_repeat('a', 101);

        $this->expectException(PositionException::class);
        $this->expectExceptionMessage('cannot exceed 100 characters');

        Position::create(
            $longTitle,
            'Description',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );
    }

    public function test_cannot_create_position_with_invalid_salary_range(): void
    {
        $this->expectException(PositionException::class);
        $this->expectExceptionMessage('Invalid salary range');

        Position::create(
            'Developer',
            'Software development',
            $this->department,
            $this->maxSalary, // Max as min
            $this->minSalary  // Min as max - invalid range
        );
    }

    public function test_cannot_create_position_with_invalid_commission_rate(): void
    {
        $this->expectException(PositionException::class);
        $this->expectExceptionMessage('Invalid commission rate');

        Position::create(
            'Sales Rep',
            'Sales',
            $this->department,
            $this->minSalary,
            $this->maxSalary,
            true,
            150.0 // Invalid rate > 100
        );
    }

    public function test_cannot_update_with_invalid_commission_rate(): void
    {
        $position = Position::create(
            'Sales Rep',
            'Sales',
            $this->department,
            $this->minSalary,
            $this->maxSalary
        );

        $this->expectException(PositionException::class);
        $this->expectExceptionMessage('Invalid commission rate');

        $position->updateCommissionSettings(true, -5.0); // Negative rate
    }
}