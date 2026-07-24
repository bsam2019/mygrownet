<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\SupportTicketRepositoryInterface;
use App\Events\Employee\SupportTicketCreated;
use App\Events\Employee\LiveChatMessage;
use Illuminate\Support\Collection;

class SupportTicketService
{
    public function __construct(
        private SupportTicketRepositoryInterface $ticketRepo
    ) {}

    public function getTicketsForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        return $this->ticketRepo->findByEmployee($employeeId, $filters);
    }

    public function getTicketStats(EmployeeId $employeeId): array
    {
        $tickets = $this->ticketRepo->findByEmployee($employeeId);

        return [
            'total' => $tickets->count(),
            'open' => $tickets->whereIn('status', ['open', 'in_progress', 'waiting'])->count(),
            'resolved' => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
            'by_category' => $tickets->groupBy('category')->map->count()->toArray(),
        ];
    }

    public function createTicket(EmployeeId $employeeId, array $data): object
    {
        $ticket = $this->ticketRepo->create([
            'employee_id' => $employeeId->toInt(),
            'subject' => $data['subject'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'] ?? 'medium',
            'attachments' => $data['attachments'] ?? [],
            'status' => 'open',
        ]);

        $ticket->load('employee');

        broadcast(new SupportTicketCreated($ticket));

        broadcast(new LiveChatMessage(
            $ticket->id,
            $employeeId->toInt(),
            $ticket->employee->full_name,
            'employee',
            $data['description'],
            now()->toISOString()
        ))->toOthers();

        cache()->forget('admin_support_stats');

        return $ticket;
    }

    public function addComment(int $ticketId, EmployeeId $employeeId, string $content, array $attachments = []): object
    {
        $ticket = $this->ticketRepo->findById($ticketId);

        if (!$ticket || $ticket->employee_id !== $employeeId->toInt()) {
            throw new \RuntimeException('Ticket not found');
        }

        $ticket->load('employee');

        $comment = $this->ticketRepo->addComment($ticketId, [
            'author_id' => $employeeId->toInt(),
            'author_type' => 'employee',
            'content' => $content,
            'attachments' => $attachments,
            'is_internal' => false,
        ]);

        broadcast(new LiveChatMessage(
            $ticket->id,
            $employeeId->toInt(),
            $ticket->employee->full_name,
            'employee',
            $content,
            now()->toISOString()
        ))->toOthers();

        return $comment;
    }

    public function getTicketWithComments(int $ticketId, EmployeeId $employeeId): object
    {
        $ticket = $this->ticketRepo->findWithComments($ticketId, $employeeId);

        if (!$ticket) {
            throw new \RuntimeException('Ticket not found');
        }

        return $ticket;
    }

    public function getCategories(): array
    {
        return [
            'it' => 'IT Support',
            'hr' => 'HR & Benefits',
            'facilities' => 'Facilities',
            'payroll' => 'Payroll',
            'equipment' => 'Equipment',
            'access' => 'Access & Permissions',
            'other' => 'Other',
        ];
    }
}