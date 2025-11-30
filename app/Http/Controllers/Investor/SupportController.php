<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel;
use App\Infrastructure\Persistence\Eloquent\Support\TicketCommentModel;
use App\Models\InvestorAccount;
use App\Events\Investor\InvestorSupportMessage;
use App\Events\Support\UnifiedTicketCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Get the current investor from session
     */
    private function getInvestor(): ?InvestorAccount
    {
        $investorId = session('investor_id');
        if (!$investorId) {
            return null;
        }
        return InvestorAccount::find($investorId);
    }

    /**
     * List tickets for the chat widget (JSON response)
     */
    public function listJson(Request $request)
    {
        $investor = $this->getInvestor();
        
        if (!$investor) {
            return response()->json(['error' => 'Unauthorized', 'tickets' => []], 401);
        }

        // Get all tickets (active and closed) for this investor
        $tickets = SupportTicketModel::where(function($query) use ($investor) {
                // Match by investor_account_id (new field)
                $query->where('investor_account_id', $investor->id)
                    // Or by user_id if investor has one
                    ->orWhere(function($q) use ($investor) {
                        if ($investor->user_id) {
                            $q->where('user_id', $investor->user_id)
                              ->where('source', 'investor');
                        }
                    });
            })
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'ticket_number' => 'INV-' . str_pad($t->id, 6, '0', STR_PAD_LEFT),
                'subject' => $this->cleanSubjectForUser($t->subject),
                'status' => $t->status,
                'updated_at' => $t->updated_at->toISOString(),
                'comments_count' => $t->comments()->count(),
                'rating' => $t->rating,
                'rating_feedback' => $t->rating_feedback,
            ]);

        return response()->json(['tickets' => $tickets]);
    }

    /**
     * Get ticket details for the chat widget (JSON response)
     */
    public function showJson(Request $request, int $id)
    {
        $investor = $this->getInvestor();
        
        if (!$investor) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $ticket = SupportTicketModel::with(['comments' => fn($q) => $q->orderBy('created_at', 'asc')])
            ->find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Verify ownership
        $isOwner = $ticket->investor_account_id === $investor->id ||
                   ($investor->user_id && $ticket->user_id === $investor->user_id && $ticket->source === 'investor');
        
        if (!$isOwner) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json([
            'ticket' => [
                'id' => $ticket->id,
                'ticket_number' => 'INV-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $this->cleanSubjectForUser($ticket->subject),
                'description' => $ticket->description,
                'status' => $ticket->status,
                'rating' => $ticket->rating,
                'rating_feedback' => $ticket->rating_feedback,
                'created_at' => $ticket->created_at->toISOString(),
                'comments' => $ticket->comments->map(fn($c) => [
                    'id' => $c->id,
                    'author_id' => $c->investor_account_id ?? $c->user_id ?? 0,
                    'author_type' => $c->author_type ?? ($c->is_internal ? 'support' : 'user'),
                    'display_author_name' => $c->author_name ?? $c->user?->name ?? 'Support',
                    'content' => $c->comment,
                    'created_at' => $c->created_at->toISOString(),
                ])->toArray(),
            ],
        ]);
    }

    /**
     * Remove the [Investor] Name prefix from subject when showing to the investor
     */
    private function cleanSubjectForUser(string $subject): string
    {
        // Remove patterns like "[Investor] John Doe - " from the beginning
        return preg_replace('/^\[Investor\]\s+[^-]+-\s*/', '', $subject);
    }

    /**
     * Quick chat - create a new ticket from the live chat widget
     */
    public function quickChat(Request $request)
    {
        $investor = $this->getInvestor();
        
        if (!$investor) {
            return response()->json(['error' => 'Please log in to send a message'], 401);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
            'category' => 'nullable|string|in:general,investment,withdrawal,account,returns',
        ]);

        $categorySubjects = [
            'general' => 'General Inquiry',
            'investment' => 'Investment Question',
            'withdrawal' => 'Withdrawal Help',
            'account' => 'Account Issue',
            'returns' => 'Returns & Dividends',
        ];

        $category = $request->category ?? 'general';
        $subject = $categorySubjects[$category] ?? 'Support Request';

        try {
            // Create ticket in the support_tickets table
            $ticket = SupportTicketModel::create([
                'user_id' => $investor->user_id, // Can be null
                'investor_account_id' => $investor->id,
                'category' => $category,
                'source' => 'investor',
                'priority' => 'medium',
                'status' => 'open',
                'subject' => '[Investor] ' . $investor->name . ' - ' . $subject,
                'description' => $request->message,
            ]);

            \Log::info('Investor support ticket created', [
                'ticket_id' => $ticket->id,
                'investor_id' => $investor->id,
                'investor_name' => $investor->name,
            ]);

            // Broadcast new ticket event to admin dashboard for real-time updates
            try {
                broadcast(new UnifiedTicketCreated(
                    $ticket->id,
                    'investor',
                    'INV-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                    $subject,
                    $investor->name ?? 'Investor',
                    'medium',
                    $category,
                    now()->toISOString()
                ));
                
                \Log::info('Broadcast UnifiedTicketCreated event', ['ticket_id' => $ticket->id]);
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast new ticket event', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Broadcast the initial message for real-time updates on the ticket channel
            try {
                broadcast(new InvestorSupportMessage(
                    $ticket->id,
                    $investor->id,
                    $investor->name ?? 'Investor',
                    'investor',
                    $request->message,
                    now()->toISOString()
                ))->toOthers();
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast investor support message', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'ticket_id' => $ticket->id,
                'ticket_number' => 'INV-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create investor quick chat ticket', [
                'investor_id' => $investor->id,
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
     */
    public function chat(Request $request, int $id)
    {
        $investor = $this->getInvestor();
        
        if (!$investor) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $ticket = SupportTicketModel::find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Verify ownership
        $isOwner = $ticket->investor_account_id === $investor->id ||
                   ($investor->user_id && $ticket->user_id === $investor->user_id && $ticket->source === 'investor');
        
        if (!$isOwner) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            // Add comment to ticket
            TicketCommentModel::create([
                'ticket_id' => $ticket->id,
                'user_id' => $investor->user_id, // Can be null
                'investor_account_id' => $investor->id,
                'author_type' => 'investor',
                'author_name' => $investor->name,
                'comment' => $request->message,
                'is_internal' => false,
            ]);

            // Update ticket timestamp and status if needed
            $ticket->touch();
            if ($ticket->status === 'waiting') {
                $ticket->update(['status' => 'open']);
            }

            // Broadcast the message for real-time updates
            try {
                broadcast(new InvestorSupportMessage(
                    $ticket->id,
                    $investor->id,
                    $investor->name ?? 'Investor',
                    'investor',
                    $request->message,
                    now()->toISOString()
                ))->toOthers();
            } catch (\Exception $e) {
                \Log::warning('Failed to broadcast investor chat message', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Failed to send investor chat message', [
                'ticket_id' => $id,
                'investor_id' => $investor->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Failed to send message. Please try again.',
            ], 500);
        }
    }

    /**
     * Rate a closed support ticket
     */
    public function rate(Request $request, int $id)
    {
        $investor = $this->getInvestor();
        
        if (!$investor) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $ticket = SupportTicketModel::find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Verify ownership
        $isOwner = $ticket->investor_account_id === $investor->id ||
                   ($investor->user_id && $ticket->user_id === $investor->user_id && $ticket->source === 'investor');
        
        if (!$isOwner) {
            return response()->json(['error' => 'Access denied'], 403);
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

            \Log::info('Investor rated support ticket', [
                'ticket_id' => $id,
                'investor_id' => $investor->id,
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
