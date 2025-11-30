<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSupportTicket;
use App\Models\EmployeeSupportTicketComment;
use App\Models\Employee;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel;
use App\Infrastructure\Persistence\Eloquent\Support\TicketCommentModel;
use App\Events\Employee\LiveChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UnifiedSupportController extends Controller
{
    /**
     * Unified Support Dashboard - shows all tickets from all sources
     */
    public function index(Request $request)
    {
        // Get employee tickets
        $employeeTickets = EmployeeSupportTicket::with(['employee.department'])
            ->withCount('comments')
            ->withCount(['comments as unread_count' => function ($q) {
                $q->where('is_internal', false)
                  ->where('author_type', '!=', 'support');
            }])
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'source' => 'employee',
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'category' => $ticket->category,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'assigned_to' => $ticket->assigned_to,
                    'created_at' => $ticket->created_at->toISOString(),
                    'updated_at' => $ticket->updated_at->toISOString(),
                    'comments_count' => $ticket->comments_count,
                    'unread_count' => $ticket->unread_count ?? 0,
                    'requester_name' => $ticket->employee?->full_name ?? 'Unknown',
                    'requester_type' => 'Employee',
                    'department' => $ticket->employee?->department?->name,
                ];
            });

        // Get member/investor tickets from generic support_tickets table
        $genericTickets = SupportTicketModel::with(['user', 'investorAccount'])
            ->withCount('comments')
            ->withCount(['comments as unread_count' => function ($q) {
                $q->where('read_by_admin', false)
                  ->where('author_type', '!=', 'support');
            }])
            ->get();
        
        // Separate investor tickets from member tickets using the source field
        $investorTickets = $genericTickets->filter(function ($ticket) {
            return $ticket->source === 'investor' || $ticket->investor_account_id;
        })->map(function ($ticket) {
            // Get investor name from investor_account or user
            $investorName = $ticket->investorAccount?->name ?? $ticket->user?->name ?? 'Investor';
            // Clean up subject if it has [Investor] prefix
            $subject = preg_replace('/^\[Investor\]\s*/', '', $ticket->subject);
            // Extract investor name from subject if present (format: "[Investor] Name - Category")
            if (preg_match('/^([^-]+)\s*-\s*/', $subject, $matches)) {
                $subject = trim(preg_replace('/^[^-]+\s*-\s*/', '', $subject));
            }
            
            return [
                'id' => $ticket->id,
                'source' => 'investor',
                'ticket_number' => 'INV-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $subject ?: $ticket->subject,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'assigned_to' => $ticket->assigned_to,
                'created_at' => $ticket->created_at->toISOString(),
                'updated_at' => $ticket->updated_at->toISOString(),
                'comments_count' => $ticket->comments_count,
                'unread_count' => $ticket->unread_count ?? 0,
                'requester_name' => $investorName,
                'requester_type' => 'Investor',
                'department' => null,
            ];
        });

        $memberTickets = $genericTickets->filter(function ($ticket) {
            return $ticket->source !== 'investor' && !$ticket->investor_account_id;
        })->map(function ($ticket) {
            $user = $ticket->user;
            
            return [
                'id' => $ticket->id,
                'source' => 'member',
                'assigned_to' => $ticket->assigned_to,
                'ticket_number' => 'MEM-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at->toISOString(),
                'updated_at' => $ticket->updated_at->toISOString(),
                'comments_count' => $ticket->comments_count,
                'unread_count' => $ticket->unread_count ?? 0,
                'requester_name' => $user?->name ?? 'Unknown',
                'requester_type' => 'Member',
                'department' => null,
            ];
        });

        // Combine all tickets
        $allTickets = $employeeTickets->concat($memberTickets)->concat($investorTickets);

        // Apply filters
        if ($request->source && $request->source !== 'all') {
            // Handle combined member/investor filter
            if ($request->source === 'member_investor') {
                $allTickets = $allTickets->filter(fn($t) => in_array($t['source'], ['member', 'investor']));
            } else {
                $allTickets = $allTickets->filter(fn($t) => $t['source'] === $request->source);
            }
        }

        if ($request->status) {
            $allTickets = $allTickets->filter(fn($t) => $t['status'] === $request->status);
        }

        if ($request->priority) {
            $allTickets = $allTickets->filter(fn($t) => $t['priority'] === $request->priority);
        }

        if ($request->search) {
            $search = strtolower($request->search);
            $allTickets = $allTickets->filter(function ($t) use ($search) {
                return str_contains(strtolower($t['subject']), $search) ||
                       str_contains(strtolower($t['ticket_number']), $search) ||
                       str_contains(strtolower($t['requester_name']), $search);
            });
        }

        // Filter by assignment (for agents to see their tickets)
        if ($request->assigned === 'me') {
            $currentUserId = Auth::id();
            $allTickets = $allTickets->filter(fn($t) => $t['assigned_to'] === $currentUserId);
        } elseif ($request->assigned === 'unassigned') {
            $allTickets = $allTickets->filter(fn($t) => empty($t['assigned_to']));
        }

        // Sort by status priority, then by date
        $allTickets = $allTickets->sortBy([
            fn($a, $b) => $this->getStatusPriority($a['status']) <=> $this->getStatusPriority($b['status']),
            fn($a, $b) => $this->getPriorityOrder($a['priority']) <=> $this->getPriorityOrder($b['priority']),
            fn($a, $b) => $b['updated_at'] <=> $a['updated_at'],
        ])->values();

        // Stats
        $stats = [
            'total' => $allTickets->count(),
            'open' => $allTickets->where('status', 'open')->count(),
            'in_progress' => $allTickets->where('status', 'in_progress')->count(),
            'pending' => $allTickets->where('status', 'pending')->count(),
            'resolved' => $allTickets->where('status', 'resolved')->count(),
            'urgent' => $allTickets->where('priority', 'urgent')
                ->whereIn('status', ['open', 'in_progress'])->count(),
            'employee_tickets' => $employeeTickets->count(),
            'member_tickets' => $memberTickets->count(),
            'investor_tickets' => $investorTickets->count(),
        ];

        // Add my_tickets count to stats
        $currentUserId = Auth::id();
        $stats['my_tickets'] = $employeeTickets->concat($memberTickets)->concat($investorTickets)
            ->filter(fn($t) => $t['assigned_to'] === $currentUserId)
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->count();
        $stats['unassigned'] = $employeeTickets->concat($memberTickets)->concat($investorTickets)
            ->filter(fn($t) => empty($t['assigned_to']))
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->count();

        // Get available agents for quick assignment
        $agents = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Administrator', 'Support Agent', 'admin', 'support', 'super-admin', 'employee']);
        })->select('id', 'name', 'email')
          ->get()
          ->map(fn($u) => [
              'id' => $u->id,
              'name' => $u->name,
              'email' => $u->email,
              'role' => $u->roles->first()?->name ?? 'Support Agent',
          ]);

        return Inertia::render('Admin/Support/UnifiedIndex', [
            'tickets' => $allTickets->take(100)->values()->all(), // Limit for performance
            'stats' => $stats,
            'filters' => $request->only(['source', 'status', 'priority', 'search', 'assigned']),
            'currentUserId' => $currentUserId,
            'agents' => $agents,
        ]);
    }

    /**
     * Show a specific ticket (handles employee, member, and investor tickets)
     */
    public function show(Request $request, string $source, int $id)
    {
        if ($source === 'employee') {
            $ticket = EmployeeSupportTicket::with([
                'employee.department',
                'employee.position',
                'assignedTo',
                'comments' => fn($q) => $q->where('is_internal', false)->orderBy('created_at', 'asc'),
                'comments.author',
            ])->findOrFail($id);

            // Get closed by user name if ticket is closed
            $closedByName = null;
            if ($ticket->closed_by) {
                $closedByUser = User::find($ticket->closed_by);
                $closedByName = $closedByUser?->name;
            }

            // Get assigned employee/user info
            $assignedAgent = null;
            if ($ticket->assigned_to) {
                $assignedUser = User::find($ticket->assigned_to);
                if ($assignedUser) {
                    $assignedAgent = [
                        'id' => $assignedUser->id,
                        'name' => $assignedUser->name,
                        'email' => $assignedUser->email,
                        'role' => $assignedUser->roles->first()?->name ?? 'support',
                    ];
                }
            }

            $ticketData = [
                'id' => $ticket->id,
                'source' => 'employee',
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'rating' => $ticket->rating,
                'rating_feedback' => $ticket->rating_feedback,
                'assigned_to' => $ticket->assigned_to,
                'assigned_agent' => $assignedAgent,
                'created_at' => $ticket->created_at->toISOString(),
                'updated_at' => $ticket->updated_at->toISOString(),
                'closed_at' => $ticket->closed_at?->toISOString(),
                'closed_by' => $closedByName,
                'closure_reason' => $ticket->closure_reason,
                'requester' => [
                    'name' => $ticket->employee?->full_name ?? 'Unknown',
                    'type' => 'Employee',
                    'email' => $ticket->employee?->email,
                    'department' => $ticket->employee?->department?->name,
                ],
                'comments' => $ticket->comments->map(fn($c) => [
                    'id' => $c->id,
                    'author_name' => $c->author_name ?? $c->author?->full_name ?? 'Support',
                    'author_type' => $c->author_type,
                    'content' => $c->content,
                    'created_at' => $c->created_at->toISOString(),
                ])->values()->all(),
            ];
        } else {
            // Both member and investor tickets come from the same table
            $ticket = SupportTicketModel::with([
                'user',
                'investorAccount',
                'comments' => fn($q) => $q->orderBy('created_at', 'asc'),
                'comments.user',
                'comments.investorAccount',
            ])->findOrFail($id);

            $isInvestor = $ticket->source === 'investor' || $ticket->investor_account_id;
            $ticketPrefix = $isInvestor ? 'INV-' : 'MEM-';
            $requesterType = $isInvestor ? 'Investor' : 'Member';
            
            // Clean up subject for display
            $displaySubject = preg_replace('/^\[Investor\]\s*[^-]*\s*-\s*/', '', $ticket->subject);
            if (empty($displaySubject)) {
                $displaySubject = $ticket->subject;
            }

            // Get requester name
            $requesterName = $isInvestor 
                ? ($ticket->investorAccount?->name ?? $ticket->user?->name ?? 'Investor')
                : ($ticket->user?->name ?? 'Unknown');
            
            // Get requester email
            $requesterEmail = $isInvestor
                ? ($ticket->investorAccount?->email ?? $ticket->user?->email)
                : $ticket->user?->email;

            // Get closed by user name if ticket is closed
            $closedByName = null;
            if ($ticket->closed_by) {
                $closedByUser = User::find($ticket->closed_by);
                $closedByName = $closedByUser?->name;
            }

            // Get assigned user info
            $assignedAgent = null;
            if ($ticket->assigned_to) {
                $assignedUser = User::find($ticket->assigned_to);
                if ($assignedUser) {
                    $assignedAgent = [
                        'id' => $assignedUser->id,
                        'name' => $assignedUser->name,
                        'email' => $assignedUser->email,
                        'role' => $assignedUser->roles->first()?->name ?? 'support',
                    ];
                }
            }

            $ticketData = [
                'id' => $ticket->id,
                'source' => $isInvestor ? 'investor' : 'member',
                'ticket_number' => $ticketPrefix . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $displaySubject,
                'description' => $ticket->description,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'rating' => $ticket->rating,
                'rating_feedback' => $ticket->rating_feedback,
                'assigned_to' => $ticket->assigned_to,
                'assigned_agent' => $assignedAgent,
                'created_at' => $ticket->created_at->toISOString(),
                'updated_at' => $ticket->updated_at->toISOString(),
                'closed_at' => $ticket->closed_at?->toISOString(),
                'closed_by' => $closedByName,
                'closure_reason' => $ticket->closure_reason,
                'requester' => [
                    'name' => $requesterName,
                    'type' => $requesterType,
                    'email' => $requesterEmail,
                    'department' => null,
                ],
                'comments' => $ticket->comments->map(fn($c) => [
                    'id' => $c->id,
                    'author_name' => $c->author_name ?? $c->investorAccount?->name ?? $c->user?->name ?? 'Support',
                    'author_type' => $c->author_type ?? ($c->is_internal ? 'support' : 'user'),
                    'content' => $c->comment,
                    'created_at' => $c->created_at->toISOString(),
                ])->values()->all(),
            ];
        }

        if ($request->wantsJson()) {
            return response()->json(['ticket' => $ticketData]);
        }

        // Get available agents for assignment
        // Include users with Administrator, Support Agent, or employee roles
        $agents = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Administrator', 'Support Agent', 'admin', 'support', 'super-admin', 'employee']);
        })->select('id', 'name', 'email')
          ->get()
          ->map(fn($u) => [
              'id' => $u->id,
              'name' => $u->name,
              'email' => $u->email,
              'role' => $u->roles->first()?->name ?? 'Support Agent',
          ]);

        return Inertia::render('Admin/Support/UnifiedShow', [
            'ticket' => $ticketData,
            'agents' => $agents,
        ]);
    }

    /**
     * Update ticket status/priority
     */
    public function update(Request $request, string $source, int $id)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:open,in_progress,pending,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        if ($source === 'employee') {
            $ticket = EmployeeSupportTicket::findOrFail($id);
            $ticket->update($validated);
            
            if (isset($validated['status']) && in_array($validated['status'], ['resolved', 'closed']) && !$ticket->resolved_at) {
                $ticket->update(['resolved_at' => now()]);
            }
        } else {
            $ticket = SupportTicketModel::findOrFail($id);
            $ticket->update($validated);
            
            if (isset($validated['status']) && in_array($validated['status'], ['resolved', 'closed']) && !$ticket->resolved_at) {
                $ticket->update(['resolved_at' => now()]);
            }
        }

        // Broadcast status change if status was updated
        if (isset($validated['status'])) {
            event(new \App\Events\Support\TicketStatusChanged(
                ticketId: $id,
                status: $validated['status'],
                source: $source,
                closedBy: in_array($validated['status'], ['closed', 'resolved']) ? Auth::user()->name : null
            ));
        }

        return back()->with('success', 'Ticket updated successfully.');
    }

    /**
     * Close a ticket with tracking of who closed it
     */
    public function close(Request $request, string $source, int $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if ($source === 'employee') {
            $ticket = EmployeeSupportTicket::findOrFail($id);
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $user->id,
                'closure_reason' => $validated['reason'] ?? null,
                'resolved_at' => $ticket->resolved_at ?? now(),
            ]);

            // Add a system comment about closure
            EmployeeSupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'author_type' => 'system',
                'author_name' => 'System',
                'content' => "Ticket closed by {$user->name}" . ($validated['reason'] ? ": {$validated['reason']}" : ''),
                'is_internal' => false,
            ]);
        } else {
            $ticket = SupportTicketModel::findOrFail($id);
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $user->id,
                'closure_reason' => $validated['reason'] ?? null,
                'resolved_at' => $ticket->resolved_at ?? now(),
            ]);

            // Add a system comment about closure
            TicketCommentModel::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'author_type' => 'system',
                'author_name' => 'System',
                'comment' => "Ticket closed by {$user->name}" . ($validated['reason'] ? ": {$validated['reason']}" : ''),
                'is_internal' => false,
            ]);
        }

        // Broadcast status change to the user's widget
        event(new \App\Events\Support\TicketStatusChanged(
            ticketId: $id,
            status: 'closed',
            source: $source,
            closedBy: $user->name
        ));

        return back()->with('success', 'Ticket closed successfully.');
    }

    /**
     * Reopen a closed ticket
     */
    public function reopen(Request $request, string $source, int $id)
    {
        $user = Auth::user();

        if ($source === 'employee') {
            $ticket = EmployeeSupportTicket::findOrFail($id);
            $ticket->update([
                'status' => 'open',
                'closed_at' => null,
                'closed_by' => null,
                'closure_reason' => null,
            ]);

            // Add a system comment about reopening
            EmployeeSupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'author_type' => 'system',
                'author_name' => 'System',
                'content' => "Ticket reopened by {$user->name}",
                'is_internal' => false,
            ]);
        } else {
            $ticket = SupportTicketModel::findOrFail($id);
            $ticket->update([
                'status' => 'open',
                'closed_at' => null,
                'closed_by' => null,
                'closure_reason' => null,
            ]);

            // Add a system comment about reopening
            TicketCommentModel::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'author_type' => 'system',
                'author_name' => 'System',
                'comment' => "Ticket reopened by {$user->name}",
                'is_internal' => false,
            ]);
        }

        return back()->with('success', 'Ticket reopened successfully.');
    }

    /**
     * Add a reply to a ticket
     */
    public function addReply(Request $request, string $source, int $id)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        if ($source === 'employee') {
            $ticket = EmployeeSupportTicket::findOrFail($id);
            $adminEmployee = Employee::where('user_id', $user->id)->first();
            $authorName = $adminEmployee?->full_name ?? $user->name;

            EmployeeSupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'author_id' => $adminEmployee?->id,
                'user_id' => $user->id,
                'author_type' => 'support',
                'author_name' => $authorName,
                'content' => $request->message,
                'is_internal' => false,
            ]);

            // Broadcast for real-time
            broadcast(new LiveChatMessage(
                $ticket->id,
                $adminEmployee?->id ?? $user->id,
                $authorName,
                'support',
                $request->message,
                now()->toISOString()
            ))->toOthers();

            if ($ticket->status === 'open') {
                $ticket->update(['status' => 'in_progress']);
            }
        } else {
            $ticket = SupportTicketModel::findOrFail($id);

            TicketCommentModel::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'author_type' => 'support',
                'author_name' => $user->name,
                'comment' => $request->message,
                'is_internal' => false,
            ]);

            // Broadcast for real-time - use appropriate channel based on ticket source
            $isInvestorTicket = $ticket->source === 'investor' || $ticket->investor_account_id;
            
            try {
                if ($isInvestorTicket) {
                    broadcast(new \App\Events\Investor\InvestorSupportMessage(
                        $ticket->id,
                        $user->id,
                        $user->name,
                        'support',
                        $request->message,
                        now()->toISOString()
                    ))->toOthers();
                } else {
                    // Use member channel for member tickets
                    broadcast(new \App\Events\Member\MemberSupportMessage(
                        $ticket->id,
                        $user->id,
                        $user->name,
                        'support',
                        $request->message,
                        now()->toISOString()
                    ))->toOthers();
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast support reply', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($ticket->status === 'open') {
                $ticket->update(['status' => 'in_progress']);
            }
        }

        return back()->with('success', 'Reply sent.');
    }

    /**
     * Live chat view for a ticket
     */
    public function liveChat(string $source, int $id)
    {
        // Reuse show logic but render different view
        if ($source === 'employee') {
            $ticket = EmployeeSupportTicket::with([
                'employee.department',
                'comments' => fn($q) => $q->where('is_internal', false)->orderBy('created_at', 'asc'),
                'comments.author',
            ])->findOrFail($id);

            $ticketData = [
                'id' => $ticket->id,
                'source' => 'employee',
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'channel_name' => 'support.ticket.' . $ticket->id,
                'requester' => [
                    'name' => $ticket->employee?->full_name ?? 'Unknown',
                    'type' => 'Employee',
                ],
                'comments' => $ticket->comments->map(fn($c) => [
                    'id' => $c->id,
                    'author_name' => $c->author_name ?? $c->author?->full_name ?? 'Support',
                    'author_type' => $c->author_type,
                    'content' => $c->content,
                    'created_at' => $c->created_at->toISOString(),
                ])->values()->all(),
            ];
        } else {
            $ticket = SupportTicketModel::with([
                'user',
                'investorAccount',
                'comments' => fn($q) => $q->orderBy('created_at', 'asc'),
                'comments.user',
                'comments.investorAccount',
            ])->findOrFail($id);

            $isInvestor = $ticket->source === 'investor' || $ticket->investor_account_id;
            $ticketPrefix = $isInvestor ? 'INV-' : 'MEM-';
            $requesterType = $isInvestor ? 'Investor' : 'Member';
            
            // Clean up subject for display
            $displaySubject = preg_replace('/^\[Investor\]\s*[^-]*\s*-\s*/', '', $ticket->subject);
            if (empty($displaySubject)) {
                $displaySubject = $ticket->subject;
            }
            
            $channelName = $isInvestor ? 'investor.support.' . $ticket->id : 'member.support.' . $ticket->id;
            
            // Get requester name
            $requesterName = $isInvestor 
                ? ($ticket->investorAccount?->name ?? $ticket->user?->name ?? 'Investor')
                : ($ticket->user?->name ?? 'Unknown');

            $ticketData = [
                'id' => $ticket->id,
                'source' => $isInvestor ? 'investor' : 'member',
                'ticket_number' => $ticketPrefix . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $displaySubject,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'channel_name' => $channelName,
                'requester' => [
                    'name' => $requesterName,
                    'type' => $requesterType,
                ],
                'comments' => $ticket->comments->map(fn($c) => [
                    'id' => $c->id,
                    'author_name' => $c->author_name ?? $c->investorAccount?->name ?? $c->user?->name ?? 'Support',
                    'author_type' => $c->author_type ?? ($c->is_internal ? 'support' : 'user'),
                    'content' => $c->comment,
                    'created_at' => $c->created_at->toISOString(),
                ])->values()->all(),
            ];
        }

        return Inertia::render('Admin/Support/UnifiedLiveChat', [
            'ticket' => $ticketData,
        ]);
    }

    /**
     * Mark ticket as read by admin
     */
    public function markAsRead(string $source, int $id)
    {
        if ($source === 'employee') {
            $ticket = EmployeeSupportTicket::findOrFail($id);
            // Mark all non-support comments as read
            $ticket->comments()
                ->where('author_type', '!=', 'support')
                ->update(['is_internal' => false]); // Using is_internal as a workaround
        } else {
            $ticket = SupportTicketModel::findOrFail($id);
            $ticket->markAsReadByAdmin();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Assign a ticket to an agent/employee
     */
    public function assign(Request $request, string $source, int $id)
    {
        $validated = $request->validate([
            'agent_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $assignedToId = $validated['agent_id'];
        $assignedToName = null;

        if ($source === 'employee') {
            $ticket = EmployeeSupportTicket::findOrFail($id);
            
            // Get assigned employee name
            if ($assignedToId) {
                $assignedEmployee = Employee::find($assignedToId);
                $assignedToName = $assignedEmployee?->full_name;
            }
            
            $ticket->update(['assigned_to' => $assignedToId]);

            // Add a system comment about assignment
            $message = $assignedToId 
                ? "Ticket assigned to {$assignedToName} by {$user->name}"
                : "Ticket unassigned by {$user->name}";
                
            EmployeeSupportTicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'author_type' => 'system',
                'author_name' => 'System',
                'content' => $message,
                'is_internal' => true,
            ]);
        } else {
            $ticket = SupportTicketModel::findOrFail($id);
            
            // Get assigned user name
            if ($assignedToId) {
                $assignedUser = User::find($assignedToId);
                $assignedToName = $assignedUser?->name;
            }
            
            $ticket->update(['assigned_to' => $assignedToId]);

            // Add a system comment about assignment
            $message = $assignedToId 
                ? "Ticket assigned to {$assignedToName} by {$user->name}"
                : "Ticket unassigned by {$user->name}";
                
            TicketCommentModel::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'author_type' => 'system',
                'author_name' => 'System',
                'comment' => $message,
                'is_internal' => true,
            ]);
        }

        return back()->with('success', $assignedToId ? 'Ticket assigned successfully.' : 'Ticket unassigned.');
    }

    /**
     * Get available agents for ticket assignment
     */
    public function getAgents(Request $request)
    {
        // Get admin users who can handle support tickets
        $adminUsers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'support', 'super-admin']);
        })->select('id', 'name', 'email')->get();

        // Get employees who can handle support tickets (for employee tickets)
        $employees = Employee::where('status', 'active')
            ->select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->first_name . ' ' . $e->last_name,
                'email' => $e->email,
                'type' => 'employee',
            ]);

        return response()->json([
            'admin_users' => $adminUsers->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'type' => 'admin',
            ]),
            'employees' => $employees,
        ]);
    }

    private function getStatusPriority(string $status): int
    {
        return match($status) {
            'open' => 1,
            'in_progress' => 2,
            'pending' => 3,
            'resolved' => 4,
            'closed' => 5,
            default => 6,
        };
    }

    private function getPriorityOrder(string $priority): int
    {
        return match($priority) {
            'urgent' => 1,
            'high' => 2,
            'medium' => 3,
            'low' => 4,
            default => 5,
        };
    }
}
