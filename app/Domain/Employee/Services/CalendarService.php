<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\EmployeeCalendarEvent;
use App\Models\EmployeeTimeOffRequest;
use Illuminate\Support\Collection;
use DateTimeImmutable;

class CalendarService
{
    public function getEventsForEmployee(EmployeeId $employeeId, DateTimeImmutable $start, DateTimeImmutable $end): Collection
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->forDateRange($start, $end)
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->get();
    }

    public function getUpcomingEvents(EmployeeId $employeeId, int $limit = 5): Collection
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->upcoming()
            ->limit($limit)
            ->get();
    }

    public function getTodayEvents(EmployeeId $employeeId): Collection
    {
        return EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->today()
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->get();
    }

    public function createEvent(EmployeeId $employeeId, array $data): EmployeeCalendarEvent
    {
        return EmployeeCalendarEvent::create([
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

    public function updateEvent(int $eventId, EmployeeId $employeeId, array $data): EmployeeCalendarEvent
    {
        $event = EmployeeCalendarEvent::where('id', $eventId)
            ->where('employee_id', $employeeId->value())
            ->firstOrFail();

        $event->update($data);

        return $event;
    }

    public function cancelEvent(int $eventId, EmployeeId $employeeId): void
    {
        $event = EmployeeCalendarEvent::where('id', $eventId)
            ->where('employee_id', $employeeId->value())
            ->firstOrFail();

        $event->update(['status' => 'cancelled']);
    }

    public function getTeamAvailability(int $departmentId, DateTimeImmutable $date): array
    {
        // Get all time off requests for the department on this date
        $timeOff = EmployeeTimeOffRequest::whereHas('employee', fn($q) => $q->where('department_id', $departmentId))
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->with('employee')
            ->get();

        return [
            'date' => $date->format('Y-m-d'),
            'unavailable' => $timeOff->map(fn($request) => [
                'employee_id' => $request->employee_id,
                'employee_name' => $request->employee->full_name,
                'type' => $request->type,
            ])->toArray(),
        ];
    }

    public function getCalendarSummary(EmployeeId $employeeId): array
    {
        $today = EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->today()
            ->where('status', 'scheduled')
            ->count();

        $thisWeek = EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->thisWeek()
            ->where('status', 'scheduled')
            ->count();

        $upcoming = EmployeeCalendarEvent::forEmployee($employeeId->value())
            ->upcoming()
            ->limit(3)
            ->get();

        return [
            'today_count' => $today,
            'week_count' => $thisWeek,
            'upcoming' => $upcoming,
        ];
    }
}
