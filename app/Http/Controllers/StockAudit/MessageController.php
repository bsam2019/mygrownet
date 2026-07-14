<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\MessagingService;
use App\Domain\StockFlow\Exceptions\MessageNotFoundException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function __construct(
        private MessagingService $messagingService,
    ) {}

    public function inbox(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $userId = $request->user('stockflow')->id;

        $messages = $this->messagingService->getInbox($companyId, $userId);

        $senderIds = array_unique(array_map(fn($m) => $m['sender_id'], $messages));
        $users = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel::whereIn('id', $senderIds)
            ->get()
            ->keyBy('id');

        $messages = array_map(function ($m) use ($users) {
            $sender = $users->get($m['sender_id']);
            $m['sender'] = $sender ? ['id' => $sender->id, 'name' => $sender->name, 'email' => $sender->email] : null;
            return $m;
        }, $messages);

        return Inertia::render('StockAudit/Messages/Inbox', [
            'messages' => $messages,
        ]);
    }

    public function sent(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $userId = $request->user('stockflow')->id;

        $messages = $this->messagingService->getSent($companyId, $userId);

        $recipientIds = array_unique(array_map(fn($m) => $m['recipient_id'], $messages));
        $users = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel::whereIn('id', $recipientIds)
            ->get()
            ->keyBy('id');

        $messages = array_map(function ($m) use ($users) {
            $recipient = $users->get($m['recipient_id']);
            $m['recipient'] = $recipient ? ['id' => $recipient->id, 'name' => $recipient->name, 'email' => $recipient->email] : null;
            return $m;
        }, $messages);

        return Inertia::render('StockAudit/Messages/Sent', [
            'messages' => $messages,
        ]);
    }

    public function show(Request $request, int $userId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $currentUserId = $request->user('stockflow')->id;

        $conversation = $this->messagingService->getConversation($companyId, $currentUserId, $userId);

        $this->messagingService->markAllAsRead($companyId, $currentUserId);

        $otherUser = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel::find($userId);

        return Inertia::render('StockAudit/Messages/Show', [
            'conversation' => $conversation,
            'otherUser' => $otherUser ? ['id' => $otherUser->id, 'name' => $otherUser->name, 'email' => $otherUser->email] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|integer|exists:sa_users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'parent_id' => 'nullable|integer|exists:sa_messages,id',
        ]);

        $companyId = $request->session()->get('stock_audit_company_id');
        $senderId = $request->user('stockflow')->id;

        $message = $this->messagingService->sendMessage(
            $companyId,
            $senderId,
            (int) $validated['recipient_id'],
            $validated['subject'],
            $validated['body'],
            isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
        );

        return response()->json(['message' => $message->toArray()], 201);
    }

    public function unreadCount(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $userId = $request->user('stockflow')?->id;

        if (!$userId) return response()->json(['count' => 0]);

        return response()->json([
            'count' => $this->messagingService->getUnreadCount($companyId, $userId),
        ]);
    }

    public function markAsRead(Request $request, int $messageId)
    {
        $userId = $request->user('stockflow')->id;

        try {
            $this->messagingService->markAsRead($messageId, $userId);
            return response()->json(['success' => true]);
        } catch (MessageNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function markAllAsRead(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $userId = $request->user('stockflow')->id;

        $this->messagingService->markAllAsRead($companyId, $userId);
        return response()->json(['success' => true]);
    }

    public function compose(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $users = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel::whereHas('companyUsers', fn($q) => $q->where('sa_company_id', $companyId))
            ->where('id', '!=', $request->user('stockflow')->id)
            ->get(['id', 'name', 'email']);

        return Inertia::render('StockAudit/Messages/Compose', [
            'users' => $users,
        ]);
    }
}
