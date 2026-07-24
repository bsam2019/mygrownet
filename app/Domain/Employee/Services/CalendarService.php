<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\CalendarEventRepositoryInterface;
use App\Domain\Employee\Repositories\TimeOffRepositoryInterface;
use Illuminate\Support\Collection;
use DateTimeImmutable;

class CalendarService
{
    public function __construct(
        private CalendarEventRepositoryInterface $calendarEventRepo,
        private TimeOffRepositoryInterface $timeOffRepo
    ) {}

    public function getEventsForEmployee(EmployeeId $employeeId, DateTimeImmutable $start, DateTimeImmutable $end): Collection
    {
        return $this->calendarEventRepo->findByEmployee($employeeId, $start, $end);
    }

    public function getUpcomingEvents(EmployeeId $employeeId, int $limit = 5): Collection
    {
        return $this->calendarEventRepo->findUpcoming($employeeId, $limit);
    }

    public function getTodayEvents(EmployeeId $employeeId): Collection
    {
        return $this->calendarEventRepo->findToday($employeeId);
    }

    public function createEvent(EmployeeId $employeeId, array $data): object
    {
        return $this->calendarEventRepo->create([
            'employee_id' => $employeeId->value(),
            'created_by' => $employeeId->value(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'personal',
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_all_day' => $data['is_all_day'] ?? false,
            'location' => $data['location'] ?? null,
            'meeting_link' => $data['meeting_link'] ?? null,
            'attendees' => $data['attendees'] ?? [],
            'reminders' => $data['reminders'] ?? [],
            'status' => 'scheduled',
        ]);
    }

    public function updateEvent(int $eventId, EmployeeId $employeeId, array $data): object
    {
        $event = $this->calendarEventRepo->update($eventId, $employeeId, $data);

        if (!$event) {
            throw new \RuntimeException('Event not found');
        }

        return $event;
    }

    public function cancelEvent(int $eventId, EmployeeId $employeeId): void
    {
        $this->calendarEventRepo->cancel($eventId, $employeeId);
    }

    public function getTeamAvailability(int $departmentId, DateTimeImmutable $date): array
    {
        $timeOff = $this->timeOffRepo->findByDepartmentAndDate($departmentId, $date);

        return [
            'date' => $date->format('Y-m-d'),
            'unavailable' => $timeOff->map(fn($request) => [
                'employee_id' => $request->employee_id,
                'employee_name' => $request->employee->full_name ?? 'Unknown',
                'type' => $request->type,
            ])->toArray(),
        ];
    }

    public function getCalendarSummary(EmployeeId $employeeId): array
    {
        $today = $this->calendarEventRepo->countToday($employeeId);
        $thisWeek = $this->calendarEventRepo->countThisWeek($employeeId);
        $upcoming = $this->calendarEventRepo->findUpcoming($employeeId, 3);

        return [
            'today_count' => $today,
            'week_count' => $thisWeek,
            'upcoming' => $upcoming,
        ];
    }
}