<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Messaging\MessageModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    private const MODULE = 'bizboost';

    /**
     * Display messages inbox
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $filter = $request->get('filter', 'inbox');
        
        $query = MessageModel::forModule(self::MODULE)
            ->with(['sender:id,name,email', 'recipient:id,name,email'])
            ->whereNull('parent_id'); // Only top-level messages

        if ($filter === 'inbox') {
            $query->where('recipient_id', $user->id);
        } elseif ($filter === 'sent') {
            $query->where('sender_id', $user->id);
        }

        $messages = $query->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn ($message) => [
                'id' => $message->id,
                'subject' => $message->subject,
                'body' => $message->body,
                'sender' => $message->sender ? [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'email' => $message->sender->email,
                ] : null,
                'recipient' => $message->recipient ? [
                    'id' => $message->recipient->id,
                    'name' => $message->recipient->name,
                    'email' => $message->recipient->email,
                ] : null,
                'isRead' => $message->is_read,
                'readAt' => $message->read_at?->toISOString(),
                'createdAt' => $message->created_at->toISOString(),
                'timeAgo' => $message->created_at->diffForHumans(),
                'replyCount' => $message->replies()->count(),
            ]);

        $unreadCount = MessageModel::forModule(self::MODULE)
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        return Inertia::render('BizBoost/Messages/Index', [
            'messages' => $messages,
            'filter' => $filter,
            'unreadCount' => $unreadCount,
        ]);
    }


    /**
     * Show a single message thread
     */
    public function show(Request $request, int $id): Response
    {
        $user = $request->user();
        
        $message = MessageModel::forModule(self::MODULE)
            ->with(['sender:id,name,email', 'recipient:id,name,email', 'replies.sender:id,name,email'])
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('recipient_id', $user->id);
            })
            ->findOrFail($id);

        // Mark as read if recipient
        if ($message->recipient_id === $user->id && !$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return Inertia::render('BizBoost/Messages/Show', [
            'message' => [
                'id' => $message->id,
                'subject' => $message->subject,
                'body' => $message->body,
                'sender' => $message->sender ? [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'email' => $message->sender->email,
                ] : null,
                'recipient' => $message->recipient ? [
                    'id' => $message->recipient->id,
                    'name' => $message->recipient->name,
                    'email' => $message->recipient->email,
                ] : null,
                'isRead' => $message->is_read,
                'createdAt' => $message->created_at->toISOString(),
                'timeAgo' => $message->created_at->diffForHumans(),
                'replies' => $message->replies->map(fn ($reply) => [
                    'id' => $reply->id,
                    'body' => $reply->body,
                    'sender' => $reply->sender ? [
                        'id' => $reply->sender->id,
                        'name' => $reply->sender->name,
                    ] : null,
                    'createdAt' => $reply->created_at->toISOString(),
                    'timeAgo' => $reply->created_at->diffForHumans(),
                ]),
            ],
        ]);
    }

    /**
     * Show compose form
     */
    public function create(Request $request): Response
    {
        // Get team members for recipient selection
        $business = $this->getBusiness($request);
        $teamMembers = $business->teamMembers()
            ->with('user:id,name,email')
            ->get()
            ->map(fn ($member) => [
                'id' => $member->user->id,
                'name' => $member->user->name,
                'email' => $member->user->email,
            ]);

        return Inertia::render('BizBoost/Messages/Create', [
            'teamMembers' => $teamMembers,
            'replyTo' => $request->get('reply_to'),
        ]);
    }

    /**
     * Send a new message
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:messages,id',
        ]);

        $message = MessageModel::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
            'module' => self::MODULE,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'id' => $message->id,
        ]);
    }

    /**
     * Reply to a message
     */
    public function reply(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $parentMessage = MessageModel::forModule(self::MODULE)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('recipient_id', $user->id);
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        // Determine recipient (the other party in the conversation)
        $recipientId = $parentMessage->sender_id === $user->id 
            ? $parentMessage->recipient_id 
            : $parentMessage->sender_id;

        $reply = MessageModel::create([
            'sender_id' => $user->id,
            'recipient_id' => $recipientId,
            'subject' => 'Re: ' . $parentMessage->subject,
            'body' => $validated['body'],
            'parent_id' => $parentMessage->id,
            'module' => self::MODULE,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
            'reply' => [
                'id' => $reply->id,
                'body' => $reply->body,
                'sender' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'createdAt' => $reply->created_at->toISOString(),
                'timeAgo' => $reply->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Get unread message count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = MessageModel::forModule(self::MODULE)
            ->where('recipient_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $message = MessageModel::forModule(self::MODULE)
            ->where('recipient_id', $request->user()->id)
            ->findOrFail($id);

        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a message
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $message = MessageModel::forModule(self::MODULE)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('recipient_id', $user->id);
            })
            ->findOrFail($id);

        $message->delete();

        return response()->json(['success' => true]);
    }

    private function getBusiness(Request $request)
    {
        return \App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
