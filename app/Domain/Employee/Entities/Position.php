<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entities;

use App\Domain\Employee\Exceptions\PositionException;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;

final class Position
{
    private PositionId $id;
    private string $title;
    private string $description;
    private Department $department;
    private Salary $baseSalaryMin;
    private Salary $baseSalaryMax;
    private bool $commissionEligible;
    private float $commissionRate;
    private array $responsibilities;
    private array $requiredPermissions;
    private bool $isActive;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        PositionId $id,
        string $title,
        string $description,
        Department $department,
        Salary $baseSalaryMin,
        Salary $baseSalaryMax,
        bool $commissionEligible = false,
        float $commissionRate = 0.0,
        array $responsibilities = [],
        array $requiredPermissions = [],
        bool $isActive = true
    ) {
        $this->validateTitle($title);
        $this->validateSalaryRange($baseSalaryMin, $baseSalaryMax);
        $this->validateCommissionRate($commissionRate);

        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->department = $department;
        $this->baseSalaryMin = $baseSalaryMin;
        $this->baseSalaryMax = $baseSalaryMax;
        $this->commissionEligible = $commissionEligible;
        $this->commissionRate = $commissionRate;
        $this->responsibilities = $responsibilities;
        $this->requiredPermissions = $requiredPermissions;
        $this->isActive = $isActive;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        string $title,
        string $description,
        Department $department,
        Salary $baseSalaryMin,
        Salary $baseSalaryMax,
        bool $commissionEligible = false,
        float $commissionRate = 0.0
    ): self {
        return new self(
            PositionId::generate(),
            $title,
            $description,
            $department,
            $baseSalaryMin,
            $baseSalaryMax,
            $commissionEligible,
            $commissionRate
        );
    }

    public function addResponsibility(string $responsibility): void
    {
        $trimmedResponsibility = trim($responsibility);
        if (empty($trimmedResponsibility)) {
            throw PositionException::invalidResponsibility($responsibility);
        }

        if (!in_array($trimmedResponsibility, $this->responsibilities, true)) {
            $this->responsibilities[] = $trimmedResponsibility;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeResponsibility(string $responsibility): void
    {
        $this->responsibilities = array_filter($this->responsibilities, function ($resp) use ($responsibility) {
            return $resp !== $responsibility;
        });
        $this->updatedAt = new DateTimeImmutable();
    }

    public function hasResponsibility(string $responsibility): bool
    {
        return in_array($responsibility, $this->responsibilities, true);
    }

    public function addRequiredPermission(string $permission): void
    {
        $trimmedPermission = trim($permission);
        if (empty($trimmedPermission)) {
            throw PositionException::invalidPermission($permission);
        }

        if (!in_array($trimmedPermission, $this->requiredPermissions, true)) {
            $this->requiredPermissions[] = $trimmedPermission;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeRequiredPermission(string $permission): void
    {
        $this->requiredPermissions = array_filter($this->requiredPermissions, function ($perm) use ($permission) {
            return $perm !== $permission;
        });
        $this->updatedAt = new DateTimeImmutable();
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->requiredPermissions, true);
    }

    public function isSalaryInRange(Salary $salary): bool
    {
        return $salary->isGreaterThanOrEqual($this->baseSalaryMin) 
            && $salary->isLessThanOrEqual($this->baseSalaryMax);
    }

    public function isCommissionEligible(): bool
    {
        return $this->commissionEligible;
    }

    public function calculateCommission(Salary $baseAmount): Salary
    {
        if (!$this->commissionEligible) {
            return Salary::zero();
        }

        return $baseAmount->multiplyByPercentage($this->commissionRate);
    }

    public function updateDetails(string $title, string $description): void
    {
        $this->validateTitle($title);
        
        $this->title = $title;
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateSalaryRange(Salary $baseSalaryMin, Salary $baseSalaryMax): void
    {
        $this->validateSalaryRange($baseSalaryMin, $baseSalaryMax);
        
        $this->baseSalaryMin = $baseSalaryMin;
        $this->baseSalaryMax = $baseSalaryMax;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateCommissionSettings(bool $commissionEligible, float $commissionRate): void
    {
        $this->validateCommissionRate($commissionRate);
        
        $this->commissionEligible = $commissionEligible;
        $this->commissionRate = $commissionRate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): PositionId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function getBaseSalaryMin(): Salary
    {
        return $this->baseSalaryMin;
    }

    public function getBaseSalaryMax(): Salary
    {
        return $this->baseSalaryMax;
    }

    public function getCommissionRate(): float
    {
        return $this->commissionRate;
    }

    public function getResponsibilities(): array
    {
        return $this->responsibilities;
    }

    public function getRequiredPermissions(): array
    {
        return $this->requiredPermissions;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Private validation methods
    private function validateTitle(string $title): void
    {
        $trimmedTitle = trim($title);
        if (empty($trimmedTitle)) {
            throw PositionException::invalidTitle($title);
        }

        if (strlen($trimmedTitle) > 100) {
            throw PositionException::invalidTitle('Position title cannot exceed 100 characters');
        }
    }

    private function validateSalaryRange(Salary $min, Salary $max): void
    {
        if ($min->isGreaterThan($max)) {
            throw PositionException::invalidSalaryRange(
                $min->getAmount(),
                $max->getAmount()
            );
        }
    }

    private function validateCommissionRate(float $rate): void
    {
        if ($rate < 0 || $rate > 100) {
            throw PositionException::invalidCommissionRate($rate);
        }
    }
}