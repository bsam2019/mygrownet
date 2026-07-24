<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\CalendarEventRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\Employee\EmployeeCalendarEvent;
use Illuminate\Support\Collection;
use DateTimeImmutable;

class EloquentCalendarEventRepository implements CalendarEventRepositoryInterface
{
    public function findById(int $id): ?EmployeeCalendarEvent
    {
        return EmployeeCalendarEvent::find($id);
    }

    public function findByEmployee(EmployeeId $employeeId, DateTimeImmutable $start, DateTimeImmutable $end): Collection
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->forDateRange($start, $end)
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->get();
    }

    public function findUpcoming(EmployeeId $employeeId, int $limit = 5): Collection
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->upcoming()
            ->limit($limit)
            ->get();
    }

    public function findToday(EmployeeId $employeeId): Collection
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->today()
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->get();
    }

    public function findThisWeek(EmployeeId $employeeId): Collection
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->thisWeek()
            ->where('status', 'scheduled')
            ->get();
    }

    public function create(array $data): EmployeeCalendarEvent
    {
        return EmployeeCalendarEvent::create($data);
    }

    public function update(int $eventId, EmployeeId $employeeId, array $data): ?EmployeeCalendarEvent
    {
        $event = EmployeeCalendarEvent::where('id', $eventId)
            ->where('employee_id', $employeeId->value())
            ->first();

        if (!$event) {
            return null;
        }

        $event->update($data);
        return $event;
    }

    public function cancel(int $eventId, EmployeeId $employeeId): void
    {
        EmployeeCalendarEvent::where('id', $eventId)
            ->where('employee_id', $employeeId->value())
            ->update(['status' => 'cancelled']);
    }

    public function countToday(EmployeeId $employeeId): int
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->today()
            ->where('status', 'scheduled')
            ->count();
    }

    public function countThisWeek(EmployeeId $employeeId): int
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->thisWeek()
            ->where('status', 'scheduled')
            ->count();
    }
}