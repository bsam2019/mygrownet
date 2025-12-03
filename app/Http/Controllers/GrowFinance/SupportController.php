<?php

namespace App\Http\Controllers\GrowFinance;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel;
use App\Infrastructure\Persistence\Eloquent\Support\TicketCommentModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportController extends Controller
{
    private const MODULE = 'growfinance';

    public function index()
    {
        $tickets = SupportTicketModel::where('user_id', auth()->id())
            ->where('module', self::MODULE)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'ticket_number' => 'GF-' . str_pad($t->id, 6, '0', STR_PAD_LEFT),
                'subject' => $t->subject,
                'category' => $t->category,
                'status' => $t->status,
                'priority' => $t->priority,
                'created_at' => $t->created_at->toISOString(),
                'updated_at' => $t->updated_at->toISOString(),
            ]);

        return Inertia::render('GrowFinance/Support/Index', [
            'tickets' => $tickets,
            'categories' => $this->getCategories(),
        ]);
    }

    public function create()
    {
        return Inertia::render('GrowFinance/Support/Create', [
            'categories' => $this->getCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        try {
            $ticket = SupportTicketModel::create([
                'user_id' => auth()->id(),
                'category' => $request->category,
                'source' => 'member',
                'module' => self::MODULE,
                'priority' => $request->priority ?? 'medium',
                'status' => 'open',
                'subject' => $request->subject,
                'description' => $request->description,
            ]);

            return redirect()
                ->route('growfinance.support.show', $ticket->id)
                ->with('success', 'Support ticket created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(int $id)
    {
        $ticket = SupportTicketModel::with(['comments' => fn($q) => $q->orderBy('created_at', 'asc')])
            ->findOrFail($id);

        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('GrowFinance/Support/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'ticket_number' => 'GF-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'category' => $ticket->category,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at->toISOString(),
                'updated_at' => $ticket->updated_at->toISOString(),
                'satisfaction_rating' => $ticket->satisfaction_rating,
                'rating_feedback' => $ticket->rating_feedback,
            ],
            'comments' => $ticket->comments->map(fn($c) => [
                'id' => $c->id,
                'author_type' => $c->author_type,
                'author_name' => $c->author_name ?? 'Support',
                'comment' => $c->comment,
                'created_at' => $c->created_at->toISOString(),
            ]),
        ]);
    }

    public function addComment(Request $request, int $id)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $ticket = SupportTicketModel::findOrFail($id);

        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            TicketCommentModel::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'author_type' => 'member',
                'author_name' => auth()->user()->name,
                'comment' => $request->comment,
                'is_internal' => false,
            ]);

            $ticket->touch();
            if ($ticket->status === 'waiting') {
                $ticket->update(['status' => 'open']);
            }

            return back()->with('success', 'Comment added successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function rate(Request $request, int $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $ticket = SupportTicketModel::findOrFail($id);

        if ($ticket->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!in_array($ticket->status, ['closed', 'resolved'])) {
            return response()->json(['error' => 'Can only rate closed tickets'], 400);
        }

        if ($ticket->satisfaction_rating) {
            return response()->json(['error' => 'Ticket already rated'], 400);
        }

        $ticket->update([
            'satisfaction_rating' => $request->rating,
            'rating_feedback' => $request->feedback,
            'rated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }

    private function getCategories(): array
    {
        return [
            ['value' => 'invoices', 'label' => 'Invoices & Billing'],
            ['value' => 'expenses', 'label' => 'Expenses'],
            ['value' => 'banking', 'label' => 'Banking & Transactions'],
            ['value' => 'reports', 'label' => 'Reports'],
            ['value' => 'accounts', 'label' => 'Chart of Accounts'],
            ['value' => 'general', 'label' => 'General Help'],
        ];
    }
}
