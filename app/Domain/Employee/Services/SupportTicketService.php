<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Events\Employee\SupportTicketCreated;
use App\Events\Employee\LiveChatMessage;
use App\Models\EmployeeSupportTicket;
use App\Models\EmployeeSupportTicketComment;
use Illuminate\Support\Collection;

class SupportTicketService
{
    public function getTicketsForEmployee(EmployeeId $employeeId, array $filters = []): Collection
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

    public function getTicketStats(EmployeeId $employeeId): array
    {
        $tickets = EmployeeSupportTicket::forEmployee($employeeId->toInt())->get();

        return [
            'total' => $tickets->count(),
            'open' => $tickets->whereIn('status', ['open', 'in_progress', 'waiting'])->count(),
            'resolved' => $tickets->whereIn('status', ['resolved', 'closed'])->count(),
            'by_category' => $tickets->groupBy('category')->map->count()->toArray(),
        ];
    }

    public function createTicket(EmployeeId $employeeId, array $data): EmployeeSupportTicket
    {
        $ticket = EmployeeSupportTicket::create([
            'employee_id' => $employeeId->toInt(),
            'subject' => $data['subject'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'] ?? 'medium',
            'attachments' => $data['attachments'] ?? [],
            'status' => 'open',
        ]);

        // Load the employee relationship for broadcasting
        $ticket->load('employee');

        // Broadcast to admin support dashboard (new ticket notification)
        broadcast(new SupportTicketCreated($ticket));

        // Also broadcast the initial message for live chat
        // This ensures admin LiveChat receives the first message in real-time
        broadcast(new LiveChatMessage(
            $ticket->id,
            $employeeId->toInt(),
            $ticket->employee->full_name,
            'employee',
            $data['description'], // The initial message is stored as description
            now()->toISOString()
        ))->toOthers();

        // Clear the cached support stats so sidebar updates
        cache()->forget('admin_support_stats');

        return $ticket;
    }

    public function addComment(int $ticketId, EmployeeId $employeeId, string $content, array $attachments = []): EmployeeSupportTicketComment
    {
        $ticket = EmployeeSupportTicket::where('id', $ticketId)
            ->where('employee_id', $employeeId->toInt())
            ->with('employee')
            ->firstOrFail();

        $comment = $ticket->comments()->create([
            'author_id' => $employeeId->toInt(),
            'author_type' => 'employee',
            'content' => $content,
            'attachments' => $attachments,
            'is_internal' => false,
        ]);

        // Broadcast the message for live chat
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

    public function getTicketWithComments(int $ticketId, EmployeeId $employeeId): EmployeeSupportTicket
    {
        return EmployeeSupportTicket::where('id', $ticketId)
            ->where('employee_id', $employeeId->toInt())
            ->with([
                'comments' => fn($q) => $q->public()->orderBy('created_at', 'asc'),
                'comments.author',
                'assignee'
            ])
            ->firstOrFail();
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
