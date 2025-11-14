<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Application\Messaging\DTOs\SendMessageDTO;
use App\Application\Messaging\UseCases\GetConversationUseCase;
use App\Application\Messaging\UseCases\GetInboxUseCase;
use App\Application\Messaging\UseCases\GetSentMessagesUseCase;
use App\Application\Messaging\UseCases\MarkMessageAsReadUseCase;
use App\Application\Messaging\UseCases\SendMessageUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyGrowNet\SendMessageRequest;
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
        
        // If this is an AJAX/API request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            $messages = match ($tab) {
                'sent' => $this->getSentMessagesUseCase->execute($userId),
                default => $this->getInboxUseCase->execute($userId),
            };
            
            return response()->json([
                'messages' => $messages,
                'tab' => $tab,
            ]);
        }
        
        // Otherwise, render Inertia page
        $isMobile = $request->get('mobile') || $this->isMobileDevice($request);

        $messages = match ($tab) {
            'sent' => $this->getSentMessagesUseCase->execute($userId),
            default => $this->getInboxUseCase->execute($userId),
        };

        // Use mobile view if requested or detected
        $view = $isMobile ? 'MyGrowNet/Messages/MobileIndex' : 'MyGrowNet/Messages/Index';

        return Inertia::render($view, [
            'messages' => $messages,
            'tab' => $tab,
        ]);
    }

    private function isMobileDevice(Request $request): bool
    {
        $userAgent = $request->header('User-Agent');
        $mobileKeywords = ['Mobile', 'Android', 'iPhone', 'iPad', 'iPod'];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function show(int $id, Request $request)
    {
        $userId = auth()->id();
        
        // First, get the message to find out who the other user is
        $message = \App\Infrastructure\Persistence\Eloquent\Messaging\MessageModel::findOrFail($id);
        
        // Determine the other user ID (if we sent it, other user is recipient, otherwise sender)
        $otherUserId = $message->sender_id === $userId ? $message->recipient_id : $message->sender_id;
        
        // Get the conversation with this user
        $messages = $this->getConversationUseCase->execute($userId, $otherUserId);
        
        // If this is an AJAX/API request, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'messages' => $messages,
                'otherUserId' => $otherUserId,
            ]);
        }
        
        // Otherwise, render Inertia page
        $isMobile = $request->get('mobile') || $this->isMobileDevice($request);

        // Use mobile view if requested or detected
        $view = $isMobile ? 'MyGrowNet/Messages/MobileShow' : 'MyGrowNet/Messages/Show';

        return Inertia::render($view, [
            'messages' => $messages,
            'otherUserId' => $otherUserId,
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

            // Return JSON for AJAX requests (from modals)
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully'
                ]);
            }

            return back()->with('success', 'Message sent successfully');
        } catch (\DomainException $e) {
            // Return JSON error for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
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
