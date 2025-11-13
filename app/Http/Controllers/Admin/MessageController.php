<?php

namespace App\Http\Controllers\Admin;

use App\Application\Messaging\DTOs\SendMessageDTO;
use App\Application\Messaging\UseCases\GetConversationUseCase;
use App\Application\Messaging\UseCases\GetInboxUseCase;
use App\Application\Messaging\UseCases\GetSentMessagesUseCase;
use App\Application\Messaging\UseCases\MarkMessageAsReadUseCase;
use App\Application\Messaging\UseCases\SendMessageUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyGrowNet\SendMessageRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function __construct(
        private GetInboxUseCase $getInboxUseCase,
        private GetSentMessagesUseCase $getSentMessagesUseCase,
        private GetConversationUseCase $getConversationUseCase,
        private SendMessageUseCase $sendMessageUseCase,
        private MarkMessageAsReadUseCase $markMessageAsReadUseCase
    ) {}

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'inbox');
        $userId = auth()->id();

        $messages = match ($tab) {
            'sent' => $this->getSentMessagesUseCase->execute($userId),
            default => $this->getInboxUseCase->execute($userId),
        };

        // Get all users for compose modal
        $users = User::select('id', 'name', 'email')
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get()
            ->toArray();

        return Inertia::render('Admin/Messages/Index', [
            'messages' => $messages,
            'tab' => $tab,
            'users' => $users,
        ]);
    }

    public function show(int $id)
    {
        $userId = auth()->id();
        
        // Get the conversation with this user
        $messages = $this->getConversationUseCase->execute($userId, $id);

        return Inertia::render('Admin/Messages/Show', [
            'messages' => $messages,
            'otherUserId' => $id,
        ]);
    }

    public function compose()
    {
        // Get all users for admin to message
        $users = User::select('id', 'name', 'email')
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Messages/Compose', [
            'users' => $users,
        ]);
    }

    public function store(SendMessageRequest $request)
    {
        try {
            $dto = new SendMessageDTO(
                senderId: auth()->id(),
                recipientId: $request->validated('recipient_id'),
                subject: $request->validated('subject'),
                body: $request->validated('body'),
                parentId: $request->validated('parent_id')
            );

            $message = $this->sendMessageUseCase->execute($dto);

            return redirect()->route('admin.messages.index')
                ->with('success', 'Message sent successfully!');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function markAsRead(int $id)
    {
        try {
            $this->markMessageAsReadUseCase->execute($id, auth()->id());

            return response()->json(['success' => true]);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
