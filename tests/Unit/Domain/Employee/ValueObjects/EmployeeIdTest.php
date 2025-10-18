<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidEmployeeIdException;
use App\Domain\Employee\ValueObjects\EmployeeId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EmployeeIdTest extends TestCase
{
    public function test_can_generate_new_employee_id(): void
    {
        $employeeId = EmployeeId::generate();
        
        $this->assertInstanceOf(EmployeeId::class, $employeeId);
        $this->assertTrue(Uuid::isValid($employeeId->toString()));
    }

    public function test_can_create_from_valid_string(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $employeeId = EmployeeId::fromString($uuidString);
        
        $this->assertInstanceOf(EmployeeId::class, $employeeId);
        $this->assertEquals($uuidString, $employeeId->toString());
    }

    public function test_can_create_from_uuid_interface(): void
    {
        $uuid = Uuid::uuid4();
        $employeeId = EmployeeId::fromUuid($uuid);
        
        $this->assertInstanceOf(EmployeeId::class, $employeeId);
        $this->assertEquals($uuid->toString(), $employeeId->toString());
        $this->assertTrue($uuid->equals($employeeId->getValue()));
    }

    public function test_throws_exception_for_empty_string(): void
    {
        $this->expectException(InvalidEmployeeIdException::class);
        $this->expectExceptionMessage('Employee ID cannot be empty');
        
        EmployeeId::fromString('');
    }

    public function test_throws_exception_for_whitespace_only_string(): void
    {
        $this->expectException(InvalidEmployeeIdException::class);
        $this->expectExceptionMessage('Employee ID cannot be empty');
        
        EmployeeId::fromString('   ');
    }

    public function test_throws_exception_for_invalid_uuid_format(): void
    {
        $this->expectException(InvalidEmployeeIdException::class);
        $this->expectExceptionMessage('Invalid Employee ID format: invalid-uuid');
        
        EmployeeId::fromString('invalid-uuid');
    }

    public function test_string_representation(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $employeeId = EmployeeId::fromString($uuidString);
        
        $this->assertEquals($uuidString, $employeeId->toString());
        $this->assertEquals($uuidString, (string) $employeeId);
    }

    public function test_equality_comparison(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $employeeId1 = EmployeeId::fromString($uuidString);
        $employeeId2 = EmployeeId::fromString($uuidString);
        $employeeId3 = EmployeeId::generate();
        
        $this->assertTrue($employeeId1->equals($employeeId2));
        $this->assertFalse($employeeId1->equals($employeeId3));
    }

    public function test_different_instances_with_same_uuid_are_equal(): void
    {
        $uuid = Uuid::uuid4();
        $employeeId1 = EmployeeId::fromUuid($uuid);
        $employeeId2 = EmployeeId::fromString($uuid->toString());
        
        $this->assertTrue($employeeId1->equals($employeeId2));
    }

    public function test_generated_ids_are_unique(): void
    {
        $employeeId1 = EmployeeId::generate();
        $employeeId2 = EmployeeId::generate();
        
        $this->assertFalse($employeeId1->equals($employeeId2));
        $this->assertNotEquals($employeeId1->toString(), $employeeId2->toString());
    }
}