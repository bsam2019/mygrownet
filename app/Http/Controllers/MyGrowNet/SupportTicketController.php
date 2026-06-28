<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Http\Requests\MyGrowNet\CreateTicketRequest;
use App\Http\Requests\MyGrowNet\AddCommentRequest;
use App\Application\Support\UseCases\CreateTicketUseCase;
use App\Application\Support\UseCases\GetUserTicketsUseCase;
use App\Application\Support\UseCases\GetTicketWithCommentsUseCase;
use App\Application\Support\UseCases\AddCommentUseCase;
use App\Application\Support\DTOs\CreateTicketDTO;
use App\Events\Support\UnifiedTicketCreated;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SupportTicketController extends Controller
{
    public function __construct(
        private CreateTicketUseCase $createTicketUseCase,
        private GetUserTicketsUseCase $getUserTicketsUseCase,
        private GetTicketWithCommentsUseCase $getTicketWithCommentsUseCase,
        private AddCommentUseCase $addCommentUseCase
    ) {}

    public function index(): Response
    {
        $tickets = $this->getUserTicketsUseCase->execute(auth()->id());

        $view = request()->query('mobile') 
            ? 'MyGrowNet/Support/MobileIndex' 
            : 'MyGrowNet/Support/Index';

        return Inertia::render($view, [
            'tickets' => $tickets
        ]);
    }

    public function create(): Response
    {
        $view = request()->query('mobile') 
            ? 'MyGrowNet/Support/MobileCreate' 
            : 'MyGrowNet/Support/Create';

        return Inertia::render($view);
    }

    public function store(CreateTicketRequest $request): RedirectResponse
    {
        try {
            $dto = new CreateTicketDTO(
                userId: auth()->id(),
                category: $request->input('category'),
                priority: $request->input('priority', 'medium'),
                subject: $request->input('subject'),
                description: $request->input('description')
            );

            $ticket = $this->createTicketUseCase->execute($dto);

            return back()
                ->with('success', 'Support ticket created successfully');
        } catch (\Exception $e) {
            \Log::error('Ticket creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(int $id): Response
    {
        try {
            $data = $this->getTicketWithCommentsUseCase->execute($id, false);

            // Verify user owns this ticket
            if ($data['ticket']->userId !== auth()->id()) {
                abort(403);
            }

            $view = request()->query('mobile') 
                ? 'MyGrowNet/Support/MobileShow' 
                : 'MyGrowNet/Support/Show';

            return Inertia::render($view, $data);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function addComment(AddCommentRequest $request, int $id): RedirectResponse
    {
        try {
            $this->addCommentUseCase->execute(
                ticketId: $id,
                userId: auth()->id(),
                comment: $request->input('comment'),
                isInternal: false
            );

            return back()->with('success', 'Comment added successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getComments(int $id)
    {
        try {
            $data = $this->getTicketWithCommentsUseCase->execute($id, false);

            // Verify user owns this ticket
            if ($data['ticket']->userId !== auth()->id()) {
                abort(403);
            }

            return response()->json($data['comments']);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Quick chat - create a new ticket from the live chat widget
     * Uses direct model creation for flexibility with short messages
     */
    public function quickChat(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:2000',
            'category' => 'nullable|string|in:general,subscription,learning,commission,account,investment,withdrawal,returns',
        ]);

        // Map category to subject for better context
        $categorySubjects = [
            'general' => 'General Inquiry',
            'subscription' => 'Subscription Help',
            'learning' => 'Learning Content',
            'commission' => 'Commissions & Earnings',
            'account' => 'Account Issue',
            'investment' => 'Investment Question',
            'withdrawal' => 'Withdrawal Help',
            'returns' => 'Returns & Dividends',
        ];

        $category = $request->category ?? 'general';
        $subject = $categorySubjects[$category] ?? 'Support Request';
        $user = auth()->user();

        try {
            // Create ticket directly using the model (bypasses strict DDD validation for chat)
            $ticket = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::create([
                'user_id' => $user->id,
                'category' => $category,
                'source' => 'member',
                'priority' => 'medium',
                'status' => 'open',
                'subject' => '[Member] ' . $user->name . ' - ' . $subject,
                'description' => $request->message,
            ]);

            \Log::info('Member support ticket created via quick chat', [
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);

            // Broadcast new ticket event to admin dashboard for real-time updates
            try {
                broadcast(new \App\Events\Support\UnifiedTicketCreated(
                    $ticket->id,
                    'member',
                    'MEM-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                    $subject,
                    $user->name,
                    'medium',
                    $category,
                    now()->toISOString()
                ));
                
                \Log::info('Broadcast UnifiedTicketCreated event for member ticket', ['ticket_id' => $ticket->id]);
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast new member ticket event', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Broadcast the initial message for real-time updates on the ticket channel
            try {
                broadcast(new \App\Events\Member\MemberSupportMessage(
                    $ticket->id,
                    $user->id,
                    $user->name,
                    'member',
                    $request->message,
                    now()->toISOString()
                ))->toOthers();
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast member quick chat message', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'ticket_id' => $ticket->id,
                'ticket_number' => 'MEM-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create quick chat ticket', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Failed to send message. Please try again.',
            ], 500);
        }
    }

    /**
     * Send a chat message to an existing ticket
     * Uses direct model creation for flexibility
     */
    public function chat(\Illuminate\Http\Request $request, int $id)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:2000',
        ]);

        $user = auth()->user();
        $ticket = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Verify user owns this ticket
        if ($ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Add comment directly using the model
            \App\Infrastructure\Persistence\Eloquent\Support\TicketCommentModel::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'author_type' => 'member',
                'author_name' => $user->name,
                'comment' => $request->message,
                'is_internal' => false,
            ]);

            // Update ticket timestamp and status if needed
            $ticket->touch();
            if ($ticket->status === 'waiting') {
                $ticket->update(['status' => 'open']);
            }

            // Broadcast the message for real-time updates using member channel
            try {
                \Log::info('Broadcasting MemberSupportMessage', [
                    'ticket_id' => $id,
                    'channel' => 'member.support.' . $id,
                    'sender_id' => $user->id,
                    'sender_name' => $user->name,
                    'sender_type' => 'member',
                ]);
                
                broadcast(new \App\Events\Member\MemberSupportMessage(
                    $id,
                    $user->id,
                    $user->name,
                    'member',
                    $request->message,
                    now()->toISOString()
                ))->toOthers();
                
                \Log::info('MemberSupportMessage broadcast sent successfully', ['ticket_id' => $id]);
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast member chat message', [
                    'ticket_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Failed to send chat message', [
                'ticket_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Failed to send message. Please try again.',
            ], 500);
        }
    }

    /**
     * Get ticket details for the chat widget (JSON response)
     */
    public function showJson(int $id)
    {
        $user = auth()->user();
        $ticket = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::with([
            'comments' => fn($q) => $q->orderBy('created_at', 'asc')
        ])->find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Verify user owns this ticket
        if ($ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'ticket' => [
                'id' => $ticket->id,
                'ticket_number' => 'MEM-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $this->cleanSubjectForUser($ticket->subject),
                'description' => $ticket->description,
                'status' => $ticket->status,
                'rating' => $ticket->rating,
                'rating_feedback' => $ticket->rating_feedback,
                'created_at' => $ticket->created_at->toISOString(),
                'comments' => $ticket->comments->map(fn($c) => [
                    'id' => $c->id,
                    'author_id' => $c->user_id ?? 0,
                    'author_type' => $c->author_type ?? 'member',
                    'display_author_name' => $c->author_name ?? $c->user?->name ?? 'Support',
                    'content' => $c->comment,
                    'created_at' => $c->created_at->toISOString(),
                ])->toArray(),
            ],
        ]);
    }

    /**
     * List tickets for the chat widget (JSON response)
     */
    public function listJson()
    {
        $user = auth()->user();
        
        // Get all tickets (active and closed) for the user
        $tickets = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::where('user_id', $user->id)
            ->where('source', 'member')
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'tickets' => $tickets->map(fn($t) => [
                'id' => $t->id,
                'ticket_number' => 'MEM-' . str_pad($t->id, 6, '0', STR_PAD_LEFT),
                'subject' => $this->cleanSubjectForUser($t->subject),
                'status' => $t->status,
                'updated_at' => $t->updated_at->toISOString(),
                'comments_count' => $t->comments()->count(),
                'rating' => $t->rating,
                'rating_feedback' => $t->rating_feedback,
            ])->toArray(),
        ]);
    }

    /**
     * Remove the [Member] Name prefix from subject when showing to the member
     */
    private function cleanSubjectForUser(string $subject): string
    {
        // Remove patterns like "[Member] John Doe - " from the beginning
        return preg_replace('/^\[Member\]\s+[^-]+-\s*/', '', $subject);
    }

    /**
     * Rate a closed support ticket
     */
    public function rate(\Illuminate\Http\Request $request, int $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $ticket = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Verify user owns this ticket
        if ($ticket->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only allow rating closed/resolved tickets
        if (!in_array($ticket->status, ['closed', 'resolved'])) {
            return response()->json(['error' => 'Can only rate closed tickets'], 400);
        }

        // Check if already rated
        if ($ticket->satisfaction_rating) {
            return response()->json(['error' => 'Ticket already rated'], 400);
        }

        try {
            $ticket->update([
                'satisfaction_rating' => $request->rating,
                'rating_feedback' => $request->feedback,
                'rated_at' => now(),
            ]);

            \Log::info('Member rated support ticket', [
                'ticket_id' => $id,
                'user_id' => $user->id,
                'rating' => $request->rating,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to rate ticket', [
                'ticket_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to submit rating. Please try again.',
            ], 500);
        }
    }
}
