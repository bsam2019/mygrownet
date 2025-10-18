<?php

namespace App\Domain\Employee\Exceptions;

/**
 * Exception thrown when commission calculation fails
 */
class CommissionCalculationException extends EmployeeDomainException
{
    public static function invalidRate(float $rate): self
    {
        return new self(
            "Invalid commission rate: {$rate}. Rate must be between 0 and 100",
            ['rate' => $rate]
        );
    }

    public static function invalidAmount(float $amount): self
    {
        return new self(
            "Invalid commission base amount: {$amount}. Amount must be greater than 0",
            ['amount' => $amount]
        );
    }

    public static function employeeNotEligible(int $employeeId, string $reason): self
    {
        return new self(
            "Employee {$employeeId} is not eligible for commission: {$reason}",
            [
                'employee_id' => $employeeId,
                'reason' => $reason
            ]
        );
    }

    public static function calculationFailed(int $employeeId, string $error): self
    {
        return new self(
            "Commission calculation failed for employee {$employeeId}: {$error}",
            [
                'employee_id' => $employeeId,
                'error' => $error
            ]
        );
    }

    public static function missingInvestmentData(int $investmentId): self
    {
        return new self(
            "Missing investment data for commission calculation: investment ID {$investmentId}",
            ['investment_id' => $investmentId]
        );
    }

    public static function inactiveEmployee(string $employeeId): self
    {
        return new self(
            "Cannot calculate commission for inactive employee: {$employeeId}",
            ['employee_id' => $employeeId]
        );
    }

    public static function notEligibleForCommission(string $employeeId): self
    {
        return new self(
            "Employee {$employeeId} is not eligible for commission",
            ['employee_id' => $employeeId]
        );
    }

    public static function noUserAccountLinked(string $employeeId): self
    {
        return new self(
            "No user account linked to employee {$employeeId} for referral commission calculation",
            ['employee_id' => $employeeId]
        );
    }

    public static function invalidInvestmentStatus(int $investmentId, string $status): self
    {
        return new self(
            "Invalid investment status '{$status}' for commission calculation on investment {$investmentId}",
            [
                'investment_id' => $investmentId,
                'status' => $status
            ]
        );
    }

    public static function invalidInvestmentAmount(int $investmentId, float $amount): self
    {
        return new self(
            "Invalid investment amount {$amount} for commission calculation on investment {$investmentId}",
            [
                'investment_id' => $investmentId,
                'amount' => $amount
            ]
        );
    }
}