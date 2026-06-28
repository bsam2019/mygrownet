<?php

namespace App\Http\Controllers\Admin;

use App\Application\Messaging\DTOs\SendMessageDTO;
use App\Application\Messaging\UseCases\BroadcastMessageUseCase;
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
        private MarkMessageAsReadUseCase $markMessageAsReadUseCase,
        private BroadcastMessageUseCase $broadcastMessageUseCase
    ) {}

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'inbox');
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $userId = auth()->id();

        $messages = match ($tab) {
            'sent' => $this->getSentMessagesUseCase->execute($userId, $perPage, $offset),
            default => $this->getInboxUseCase->execute($userId, $perPage, $offset),
        };

        // Always get unread inbox count regardless of active tab
        $inboxMessages = $this->getInboxUseCase->execute($userId, 1000, 0); // Get all inbox messages for count
        $unreadCount = count(array_filter($inboxMessages, fn($m) => !$m->isRead));

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
            'unreadCount' => $unreadCount,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'has_more' => count($messages) === $perPage,
            ],
        ]);
    }

    public function show(int $id)
    {
        $userId = auth()->id();
        
        // First, get the message to find out who the other user is
        $message = \App\Infrastructure\Persistence\Eloquent\Messaging\MessageModel::findOrFail($id);
        
        // Determine the other user ID (if we sent it, other user is recipient, otherwise sender)
        $otherUserId = $message->sender_id === $userId ? $message->recipient_id : $message->sender_id;
        
        // Get the conversation with this user
        $messages = $this->getConversationUseCase->execute($userId, $otherUserId);
        
        // Mark message as read if we're the recipient
        if ($message->recipient_id === $userId && !$message->is_read) {
            $this->markMessageAsReadUseCase->execute($id, $userId);
        }

        return Inertia::render('Admin/Messages/Show', [
            'messages' => $messages,
            'otherUserId' => $otherUserId,
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

            // If it's a reply (has parent_id), stay on the conversation page
            if ($request->validated('parent_id')) {
                return back()->with('success', 'Reply sent successfully!');
            }

            // Otherwise redirect to inbox
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

    /**
     * Show broadcast message form
     */
    public function broadcast()
    {
        $totalMembers = User::where('id', '!=', auth()->id())->count();
        $withStarterKit = User::where('id', '!=', auth()->id())
            ->where('has_starter_kit', true)
            ->count();
        
        // For now, active_subscription is the same as has_starter_kit
        // When subscription system is implemented, update this query
        $activeSubscriptions = $withStarterKit;
        $bothFilters = $withStarterKit; // Same since both filters check starter kit for now

        return Inertia::render('Admin/Messages/Broadcast', [
            'stats' => [
                'total_members' => $totalMembers,
                'with_starter_kit' => $withStarterKit,
                'active_subscriptions' => $activeSubscriptions,
                'both_filters' => $bothFilters,
            ]
        ]);
    }

    /**
     * Send broadcast message
     */
    public function sendBroadcast(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'filters' => 'nullable|array',
            'filters.role' => 'nullable|string',
            'filters.has_starter_kit' => 'nullable|boolean',
            'filters.professional_level' => 'nullable|integer',
            'filters.active_subscription' => 'nullable|boolean',
        ]);

        try {
            $result = $this->broadcastMessageUseCase->execute(
                auth()->id(),
                $validated['subject'],
                $validated['body'],
                $validated['filters'] ?? []
            );

            return back()->with('success', "Broadcast sent successfully to {$result['success_count']} members!");
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Broadcast failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send broadcast message');
        }
    }
} 