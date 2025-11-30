<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSupportTicket;
use App\Models\EmployeeSupportTicketComment;
use App\Models\Employee;
use App\Events\Employee\LiveChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SupportTicketController extends Controller
{
    /**
     * Live Support Dashboard - shows active tickets for real-time support
     */
    public function dashboard()
    {
        $activeTickets = EmployeeSupportTicket::with(['employee.department'])
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->withCount('comments')
            ->orderByRaw("CASE 
                WHEN priority = 'urgent' THEN 1 
                WHEN priority = 'high' THEN 2 
                WHEN priority = 'medium' THEN 3 
                ELSE 4 END")
            ->orderBy('updated_at', 'desc')
            ->get();

        // Calculate average response time (simplified)
        $avgResponseTime = '< 5 min';
        
        $stats = [
            'total' => EmployeeSupportTicket::count(),
            'open' => EmployeeSupportTicket::where('status', 'open')->count(),
            'in_progress' => EmployeeSupportTicket::where('status', 'in_progress')->count(),
            'urgent' => EmployeeSupportTicket::where('priority', 'urgent')
                ->whereIn('status', ['open', 'in_progress'])->count(),
            'avg_response_time' => $avgResponseTime,
        ];

        return Inertia::render('Admin/Support/Dashboard', [
            'activeTickets' => $activeTickets,
            'stats' => $stats,
        ]);
    }

    public function index(Request $request)
    {
        $query = EmployeeSupportTicket::with(['employee.department', 'assignedTo'])
            ->withCount('comments');

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->orderByRaw("CASE 
                WHEN status = 'open' THEN 1 
                WHEN status = 'in_progress' THEN 2 
                WHEN status = 'pending' THEN 3 
                ELSE 4 END")
            ->orderByRaw("CASE 
                WHEN priority = 'urgent' THEN 1 
                WHEN priority = 'high' THEN 2 
                WHEN priority = 'medium' THEN 3 
                ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Stats
        $stats = [
            'total' => EmployeeSupportTicket::count(),
            'open' => EmployeeSupportTicket::where('status', 'open')->count(),
            'in_progress' => EmployeeSupportTicket::where('status', 'in_progress')->count(),
            'pending' => EmployeeSupportTicket::where('status', 'pending')->count(),
            'resolved' => EmployeeSupportTicket::where('status', 'resolved')->count(),
            'urgent' => EmployeeSupportTicket::where('priority', 'urgent')
                ->whereIn('status', ['open', 'in_progress'])->count(),
        ];

        $categories = [
            'general' => 'General Inquiry',
            'technical' => 'Technical Issue',
            'hr' => 'HR Related',
            'payroll' => 'Payroll',
            'benefits' => 'Benefits',
            'it' => 'IT Support',
            'facilities' => 'Facilities',
            'other' => 'Other',
        ];

        return Inertia::render('Admin/Support/Index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => $request->only(['status', 'priority', 'category', 'search']),
        ]);
    }

    public function show(Request $request, EmployeeSupportTicket $ticket)
    {
        $ticket->load([
            'employee.department',
            'employee.position',
            'assignedTo',
            'comments' => function ($q) {
                $q->where('is_internal', false)->orderBy('created_at', 'asc');
            },
            'comments.author',
        ]);

        // Return JSON for AJAX requests (used by polling in LiveChat)
        if ($request->wantsJson()) {
            return response()->json([
                'ticket' => $ticket,
            ]);
        }

        // Get support agents for assignment
        $supportAgents = Employee::whereHas('user', function ($q) {
            $q->whereHas('roles', function ($rq) {
                $rq->whereIn('name', ['admin', 'hr', 'support']);
            });
        })->get(['id', 'first_name', 'last_name']);

        return Inertia::render('Admin/Support/Show', [
            'ticket' => $ticket,
            'supportAgents' => $supportAgents,
        ]);
    }

    public function update(Request $request, EmployeeSupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:open,in_progress,pending,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'sometimes|nullable|exists:employees,id',
        ]);

        $oldStatus = $ticket->status;

        $ticket->update($validated);

        // If status changed to resolved/closed, set resolved_at
        if (isset($validated['status']) && in_array($validated['status'], ['resolved', 'closed']) && !$ticket->resolved_at) {
            $ticket->update(['resolved_at' => now()]);
        }

        // Notify employee of status change
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $this->notifyStatusChange($ticket, $oldStatus, $validated['status']);
        }

        return back()->with('success', 'Ticket updated successfully.');
    }

    public function addComment(Request $request, EmployeeSupportTicket $ticket)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'is_internal' => 'boolean',
        ]);

        $user = Auth::user();
        $adminEmployee = Employee::where('user_id', $user->id)->first();
        $authorName = $adminEmployee?->full_name ?? $user->name;

        $comment = EmployeeSupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'author_id' => $adminEmployee?->id, // nullable if admin has no employee record
            'user_id' => $user->id,
            'author_type' => 'support',
            'author_name' => $authorName,
            'content' => $request->content,
            'is_internal' => $request->is_internal ?? false,
        ]);

        // Broadcast the message for live chat (only if not internal)
        if (!$request->is_internal) {
            broadcast(new LiveChatMessage(
                $ticket->id,
                $adminEmployee?->id ?? $user->id,
                $authorName,
                'support',
                $request->content,
                now()->toISOString()
            ))->toOthers();
        }

        // Update ticket status to in_progress if it was open
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Reply sent.');
    }

    public function liveChat(EmployeeSupportTicket $ticket)
    {
        $ticket->load([
            'employee.department',
            'comments' => function ($q) {
                $q->where('is_internal', false)->orderBy('created_at', 'asc');
            },
            'comments.author',
        ]);

        return Inertia::render('Admin/Support/LiveChat', [
            'ticket' => $ticket,
        ]);
    }

    public function sendChatMessage(Request $request, EmployeeSupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $adminEmployee = Employee::where('user_id', $user->id)->first();
        $authorName = $adminEmployee?->full_name ?? $user->name;

        // Save the message - supports both employees and admin users
        $comment = EmployeeSupportTicketComment::create([
            'ticket_id' => $ticket->id,
            'author_id' => $adminEmployee?->id, // nullable if admin has no employee record
            'user_id' => $user->id, // always store the user_id
            'author_type' => 'support',
            'author_name' => $authorName, // store for display
            'content' => $request->message,
            'is_internal' => false,
        ]);

        // Broadcast the message
        broadcast(new LiveChatMessage(
            $ticket->id,
            $adminEmployee?->id ?? $user->id,
            $authorName,
            'support',
            $request->message,
            now()->toISOString()
        ))->toOthers();

        return response()->json(['success' => true, 'comment' => $comment]);
    }

    protected function notifyStatusChange(EmployeeSupportTicket $ticket, string $oldStatus, string $newStatus): void
    {
        $notificationService = app(\App\Domain\Employee\Services\NotificationService::class);

        $statusLabels = [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'pending' => 'Pending',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];

        $notificationService->createNotification(
            $ticket->employee_id,
            'ticket_status_changed',
            'Support Ticket Updated',
            "Your ticket #{$ticket->ticket_number} status changed to {$statusLabels[$newStatus]}",
            "/employee/portal/support/{$ticket->id}",
            ['ticket_id' => $ticket->id, 'old_status' => $oldStatus, 'new_status' => $newStatus]
        );
    }
}
