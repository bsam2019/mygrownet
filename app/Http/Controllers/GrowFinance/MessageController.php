<?php

namespace App\Http\Controllers\GrowFinance;

use App\Application\Messaging\DTOs\SendMessageDTO;
use App\Application\Messaging\UseCases\GetConversationUseCase;
use App\Application\Messaging\UseCases\GetInboxUseCase;
use App\Application\Messaging\UseCases\GetSentMessagesUseCase;
use App\Application\Messaging\UseCases\MarkMessageAsReadUseCase;
use App\Application\Messaging\UseCases\SendMessageUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    private const MODULE = 'growfinance';

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
            'sent' => $this->getSentMessagesUseCase->execute($userId, 100, 0, self::MODULE),
            default => $this->getInboxUseCase->execute($userId, 100, 0, self::MODULE),
        };

        if ($request->wantsJson()) {
            return response()->json([
                'messages' => $messages,
                'tab' => $tab,
            ]);
        }

        return Inertia::render('GrowFinance/Messages/Index', [
            'messages' => $messages,
            'tab' => $tab,
        ]);
    }

    public function show(int $id, Request $request)
    {
        $userId = auth()->id();
        $message = \App\Infrastructure\Persistence\Eloquent\Messaging\MessageModel::findOrFail($id);
        $otherUserId = $message->sender_id === $userId ? $message->recipient_id : $message->sender_id;
        
        $messages = $this->getConversationUseCase->execute($userId, $otherUserId, 50, self::MODULE);

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'messages' => $messages,
                'otherUserId' => $otherUserId,
            ]);
        }

        return Inertia::render('GrowFinance/Messages/Show', [
            'messages' => $messages,
            'otherUserId' => $otherUserId,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'parent_id' => 'nullable|exists:messages,id',
            'recipient_id' => 'nullable|exists:users,id', // Optional, defaults to support admin
        ]);

        try {
            // Get the support admin user (first admin or user with ID 1)
            $supportAdminId = $request->recipient_id ?? $this->getSupportAdminId();
            
            $dto = new SendMessageDTO(
                senderId: auth()->id(),
                recipientId: $supportAdminId,
                subject: $request->subject,
                body: $request->body,
                parentId: $request->parent_id,
                module: self::MODULE,
                metadata: array_merge($request->metadata ?? [], [
                    'source' => 'growfinance_user',
                ]),
            );

            $this->sendMessageUseCase->execute($dto);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully'
                ]);
            }

            return back()->with('success', 'Message sent to support');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get the support admin user ID for GrowFinance messages.
     */
    private function getSupportAdminId(): int
    {
        // Try to find a user with admin role, fallback to user ID 1
        $admin = \App\Models\User::role(['admin', 'super-admin'])->first();
        
        return $admin?->id ?? 1;
    }

    public function markAsRead(int $id)
    {
        try {
            $this->markMessageAsReadUseCase->execute($id, auth()->id());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
