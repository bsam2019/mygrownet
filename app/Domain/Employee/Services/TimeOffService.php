<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Entities\TimeOffRequest;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\TimeOffType;
use App\Domain\Employee\Repositories\TimeOffRepositoryInterface;
use App\Domain\Employee\Exceptions\TimeOffException;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class TimeOffService
{
    public function __construct(
        private TimeOffRepositoryInterface $timeOffRepository
    ) {}

    public function createRequest(
        EmployeeId $employeeId,
        string $type,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        float $daysRequested,
        ?string $reason = null
    ): TimeOffRequest {
        $timeOffType = TimeOffType::fromString($type);

        // Check for overlapping requests
        if ($this->timeOffRepository->hasOverlappingRequest($employeeId, $startDate, $endDate)) {
            throw new TimeOffException('You already have a time off request for these dates');
        }

        // Check balance
        $balance = $this->getBalance($employeeId, $type, (int) $startDate->format('Y'));
        if ($balance['remaining'] < $daysRequested) {
            throw TimeOffException::insufficientBalance($type, $balance['remaining'], $daysRequested);
        }

        $request = TimeOffRequest::create(
            $employeeId,
            $timeOffType,
            $startDate,
            $endDate,
            $daysRequested,
            $reason
        );

        $this->timeOffRepository->save($request);

        return $request;
    }

    public function getRequestsForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        return $this->timeOffRepository->findByEmployee($employeeId, $filters);
    }

    public function getPendingRequests(EmployeeId $employeeId): Collection
    {
        return $this->timeOffRepository->findPendingByEmployee($employeeId);
    }

    public function getUpcomingTimeOff(EmployeeId $employeeId): Collection
    {
        return $this->timeOffRepository->findUpcoming($employeeId);
    }

    public function cancelRequest(int $requestId, EmployeeId $employeeId): void
    {
        $request = $this->timeOffRepository->findById($requestId);

        if (!$request) {
            throw TimeOffException::requestNotFound($requestId);
        }

        if (!$request->getEmployeeId()->equals($employeeId)) {
            throw new TimeOffException('You are not authorized to cancel this request');
        }

        $request->cancel();
        $this->timeOffRepository->save($request);
    }

    public function approveRequest(int $requestId, EmployeeId $reviewerId, ?string $notes = null): void
    {
        $request = $this->timeOffRepository->findById($requestId);

        if (!$request) {
            throw TimeOffException::requestNotFound($requestId);
        }

        $request->approve($reviewerId, $notes);
        $this->timeOffRepository->save($request);
    }

    public function rejectRequest(int $requestId, EmployeeId $reviewerId, ?string $notes = null): void
    {
        $request = $this->timeOffRepository->findById($requestId);

        if (!$request) {
            throw TimeOffException::requestNotFound($requestId);
        }

        $request->reject($reviewerId, $notes);
        $this->timeOffRepository->save($request);
    }

    public function getBalance(EmployeeId $employeeId, string $type, int $year): array
    {
        $timeOffType = TimeOffType::fromString($type);
        return $this->timeOffRepository->getBalance($employeeId, $timeOffType, $year);
    }

    public function getAllBalances(EmployeeId $employeeId, int $year): array
    {
        $balances = [];
        
        foreach (TimeOffType::all() as $type) {
            $balances[$type->getValue()] = $this->timeOffRepository->getBalance($employeeId, $type, $year);
        }

        return $balances;
    }

    public function getPendingRequestsForManager(EmployeeId $managerId): Collection
    {
        return $this->timeOffRepository->findPendingForManager($managerId);
    }

    public function getTimeOffSummary(EmployeeId $employeeId, int $year): array
    {
        $balances = $this->getAllBalances($employeeId, $year);
        $pending = $this->getPendingRequests($employeeId);
        $upcoming = $this->getUpcomingTimeOff($employeeId);

        return [
            'balances' => $balances,
            'pending_count' => $pending->count(),
            'upcoming_count' => $upcoming->count(),
            'total_used' => array_sum(array_column($balances, 'used')),
            'total_remaining' => array_sum(array_column($balances, 'remaining')),
        ];
    }
}
