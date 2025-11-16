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
}
