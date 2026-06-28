<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Entities;

use App\Domain\GrowBiz\ValueObjects\EmployeeId;
use App\Domain\GrowBiz\ValueObjects\EmployeeStatus;
use DateTimeImmutable;

/**
 * GrowBiz Employee Entity
 * 
 * Represents an employee managed by an SME business owner.
 */
class Employee
{
    private function __construct(
        private EmployeeId $id,
        private int $managerId,
        private ?int $userId,
        private string $firstName,
        private string $lastName,
        private ?string $email,
        private ?string $phone,
        private ?string $position,
        private ?string $department,
        private EmployeeStatus $status,
        private ?DateTimeImmutable $hireDate,
        private ?float $hourlyRate,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $managerId,
        string $firstName,
        string $lastName,
        ?string $email = null,
        ?string $phone = null,
        ?string $position = null,
        ?string $department = null,
        ?string $hireDate = null,
        ?float $hourlyRate = null,
        ?string $notes = null,
        ?int $userId = null
    ): self {
        return new self(
            id: EmployeeId::generate(),
            managerId: $managerId,
            userId: $userId,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            phone: $phone,
            position: $position,
            department: $department,
            status: EmployeeStatus::active(),
            hireDate: $hireDate ? new DateTimeImmutable($hireDate) : null,
            hourlyRate: $hourlyRate,
            notes: $notes,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }


    public static function reconstitute(
        EmployeeId $id,
        int $managerId,
        ?int $userId,
        string $firstName,
        string $lastName,
        ?string $email,
        ?string $phone,
        ?string $position,
        ?string $department,
        EmployeeStatus $status,
        ?DateTimeImmutable $hireDate,
        ?float $hourlyRate,
        ?string $notes,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id, $managerId, $userId, $firstName, $lastName, $email, $phone,
            $position, $department, $status, $hireDate, $hourlyRate, $notes,
            $createdAt, $updatedAt
        );
    }

    public function update(
        string $firstName,
        string $lastName,
        ?string $email,
        ?string $phone,
        ?string $position,
        ?string $department,
        ?string $hireDate,
        ?float $hourlyRate,
        ?string $notes
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->position = $position;
        $this->department = $department;
        $this->hireDate = $hireDate ? new DateTimeImmutable($hireDate) : null;
        $this->hourlyRate = $hourlyRate;
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = EmployeeStatus::active();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->status = EmployeeStatus::inactive();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function linkToUser(int $userId): void
    {
        $this->userId = $userId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateStatus(EmployeeStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): EmployeeId { return $this->id; }
    public function id(): int { return $this->id->toInt(); }
    public function getManagerId(): int { return $this->managerId; }
    public function ownerId(): int { return $this->managerId; }
    public function getUserId(): ?int { return $this->userId; }
    public function getFirstName(): string { return $this->firstName; }
    public function firstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function lastName(): string { return $this->lastName; }
    public function getName(): string { return trim($this->firstName . ' ' . $this->lastName); }
    public function name(): string { return $this->getName(); }
    public function getEmail(): ?string { return $this->email; }
    public function email(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function phone(): ?string { return $this->phone; }
    public function getPosition(): ?string { return $this->position; }
    public function position(): ?string { return $this->position; }
    public function getDepartment(): ?string { return $this->department; }
    public function department(): ?string { return $this->department; }
    public function getRole(): ?string { return $this->position; }
    public function role(): ?string { return $this->position; }
    public function getStatus(): EmployeeStatus { return $this->status; }
    public function status(): EmployeeStatus { return $this->status; }
    public function getHireDate(): ?DateTimeImmutable { return $this->hireDate; }
    public function hireDate(): ?DateTimeImmutable { return $this->hireDate; }
    public function getHourlyRate(): ?float { return $this->hourlyRate; }
    public function hourlyRate(): ?float { return $this->hourlyRate; }
    public function getNotes(): ?string { return $this->notes; }
    public function notes(): ?string { return $this->notes; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'owner_id' => $this->managerId,
            'user_id' => $this->userId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'name' => $this->getName(),
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'role' => $this->position,
            'department' => $this->department,
            'status' => $this->status->value(),
            'hire_date' => $this->hireDate?->format('Y-m-d'),
            'hourly_rate' => $this->hourlyRate,
            'notes' => $this->notes,
            'is_active' => $this->isActive(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
