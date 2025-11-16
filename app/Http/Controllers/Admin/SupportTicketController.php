<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Application\Support\UseCases\GetUserTicketsUseCase;
use App\Application\Support\UseCases\GetTicketWithCommentsUseCase;
use App\Application\Support\UseCases\AssignTicketUseCase;
use App\Application\Support\UseCases\UpdateTicketStatusUseCase;
use App\Application\Support\UseCases\AddCommentUseCase;
use App\Domain\Support\Repositories\TicketRepository;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private GetTicketWithCommentsUseCase $getTicketWithCommentsUseCase,
        private AssignTicketUseCase $assignTicketUseCase,
        private UpdateTicketStatusUseCase $updateTicketStatusUseCase,
        private AddCommentUseCase $addCommentUseCase
    ) {}

    public function index(Request $request): Response
    {
        $tickets = $this->ticketRepository->findAll();

        return Inertia::render('Admin/Support/Index', [
            'tickets' => $tickets
        ]);
    }

    public function show(int $id): Response
    {
        $data = $this->getTicketWithCommentsUseCase->execute($id, true);

        return Inertia::render('Admin/Support/Show', $data);
    }

    public function assign(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id'
        ]);

        try {
            $this->assignTicketUseCase->execute($id, $request->input('admin_id'));
            return back()->with('success', 'Ticket assigned successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,waiting,resolved,closed'
        ]);

        try {
            $this->updateTicketStatusUseCase->execute($id, $request->input('status'));
            return back()->with('success', 'Ticket status updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function addComment(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'comment' => 'required|string|min:1|max:2000',
            'is_internal' => 'boolean'
        ]);

        try {
            $this->addCommentUseCase->execute(
                ticketId: $id,
                userId: auth()->id(),
                comment: $request->input('comment'),
                isInternal: $request->boolean('is_internal', false)
            );

            return back()->with('success', 'Comment added successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
