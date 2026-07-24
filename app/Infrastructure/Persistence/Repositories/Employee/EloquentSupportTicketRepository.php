<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\SupportTicketRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\Employee\EmployeeSupportTicket;
use App\Models\Employee\EmployeeSupportTicketComment;
use Illuminate\Support\Collection;

class EloquentSupportTicketRepository implements SupportTicketRepositoryInterface
{
    public function findById(int $id): ?EmployeeSupportTicket
    {
        return EmployeeSupportTicket::find($id);
    }

    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        $query = EmployeeSupportTicket::forEmployee($employeeId->toInt())
            ->with('assignee');

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'open') {
                $query->open();
            } elseif ($filters['status'] === 'closed') {
                $query->closed();
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function findWithComments(int $ticketId, EmployeeId $employeeId): ?EmployeeSupportTicket
    {
        return EmployeeSupportTicket::where('id', $ticketId)
            ->where('employee_id', $employeeId->toInt())
            ->with([
                'comments' => fn($q) => $q->public()->orderBy('created_at', 'asc'),
                'comments.author',
                'assignee'
            ])
            ->first();
    }

    public function create(array $data): EmployeeSupportTicket
    {
        return EmployeeSupportTicket::create($data);
    }

    public function addComment(int $ticketId, array $data): EmployeeSupportTicketComment
    {
        $ticket = EmployeeSupportTicket::findOrFail($ticketId);
        return $ticket->comments()->create($data);
    }
}