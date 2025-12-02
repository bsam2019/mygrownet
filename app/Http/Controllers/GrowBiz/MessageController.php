<?php

namespace App\Http\Controllers\GrowBiz;

use App\Application\Messaging\DTOs\SendMessageDTO;
use App\Application\Messaging\UseCases\GetConversationUseCase;
use App\Application\Messaging\UseCases\GetInboxUseCase;
use App\Application\Messaging\UseCases\GetSentMessagesUseCase;
use App\Application\Messaging\UseCases\MarkMessageAsReadUseCase;
use App\Application\Messaging\UseCases\SendMessageUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * Display messages list
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tab = $request->get('tab', 'inbox');
        
        // Get messages using existing use cases
        $allMessages = match ($tab) {
            'sent' => $this->getSentMessagesUseCase->execute($user->id),
            default => $this->getInboxUseCase->execute($user->id),
        };
        
        // Filter to only GrowBiz-related messages (team communication)
        $teamMemberIds = $this->getTeamMemberIds($user);
        
        $messages = array_filter($allMessages, function ($message) use ($teamMemberIds, $tab, $user) {
            // Include messages from/to team members
            $otherUserId = $tab === 'inbox' ? $message->senderId : $message->recipientId;
            if (in_array($otherUserId, $teamMemberIds)) {
                return true;
            }
            // Include messages with [GrowBiz] prefix
            if (str_contains($message->subject, '[GrowBiz]')) {
                return true;
            }
            return false;
        });

        return Inertia::render('GrowBiz/Messages/Index', [
            'messages' => array_values($messages),
            'pagination' => [
                'currentPage' => 1,
                'lastPage' => 1,
                'total' => count($messages),
            ],
            'tab' => $tab,
            'teamMembers' => $this->getTeamMembers($user),
        ]);
    }

    /**
     * Show a specific message/conversation
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        
        // Get the message to find the other user
        $message = \App\Infrastructure\Persistence\Eloquent\Messaging\MessageModel::findOrFail($id);
        
        // Verify user has access to this message
        if ($message->sender_id !== $user->id && $message->recipient_id !== $user->id) {
            abort(403);
        }
        
        // Determine the other user ID
        $otherUserId = $message->sender_id === $user->id 
            ? $message->recipient_id 
            : $message->sender_id;
        
        // Get conversation using existing use case
        $conversation = $this->getConversationUseCase->execute($user->id, $otherUserId);
        
        // Get other user info
        $otherUser = DB::table('users')->find($otherUserId);
        
        // Transform for frontend
        $transformedConversation = array_map(function ($msg) use ($user) {
            return [
                'id' => $msg->id,
                'senderId' => $msg->senderId,
                'senderName' => $msg->senderName,
                'body' => $msg->body,
                'createdAt' => $msg->createdAt,
                'isOwn' => $msg->senderId === $user->id,
            ];
        }, $conversation);

        return Inertia::render('GrowBiz/Messages/Show', [
            'conversation' => $transformedConversation,
            'otherUser' => [
                'id' => $otherUser?->id,
                'name' => $otherUser?->name ?? 'Unknown',
            ],
            'teamMembers' => $this->getTeamMembers($user),
        ]);
    }

    /**
     * Send a new message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
        ]);
        
        $user = $request->user();
        
        // Prefix subject with [GrowBiz] for filtering
        $subject = str_starts_with($validated['subject'], '[GrowBiz]') 
            ? $validated['subject'] 
            : '[GrowBiz] ' . $validated['subject'];
        
        try {
            $dto = new SendMessageDTO(
                senderId: $user->id,
                recipientId: (int) $validated['recipient_id'],
                subject: $subject,
                body: $validated['body'],
                parentId: null
            );

            $this->sendMessageUseCase->execute($dto);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Message sent']);
            }
            
            return back()->with('success', 'Message sent successfully');
        } catch (\DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reply to a message
     */
    public function reply(Request $request, int $id)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:10000',
        ]);
        
        $user = $request->user();
        $originalMessage = \App\Infrastructure\Persistence\Eloquent\Messaging\MessageModel::findOrFail($id);
        
        // Determine recipient (the other person in the conversation)
        $recipientId = $originalMessage->sender_id === $user->id 
            ? $originalMessage->recipient_id 
            : $originalMessage->sender_id;
        
        // Create reply with Re: prefix if not already present
        $subject = str_starts_with($originalMessage->subject, 'Re:') 
            ? $originalMessage->subject 
            : 'Re: ' . $originalMessage->subject;
        
        try {
            $dto = new SendMessageDTO(
                senderId: $user->id,
                recipientId: $recipientId,
                subject: $subject,
                body: $validated['body'],
                parentId: $id
            );

            $this->sendMessageUseCase->execute($dto);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Reply sent']);
            }
            
            return back()->with('success', 'Reply sent successfully');
        } catch (\DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get team member IDs for the user
     */
    private function getTeamMemberIds($user): array
    {
        $ids = [];
        
        // Get employees managed by this user
        $employeeIds = GrowBizEmployeeModel::where('manager_id', $user->id)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();
        $ids = array_merge($ids, $employeeIds);
        
        // Get manager if user is an employee
        $employeeRecord = GrowBizEmployeeModel::where('user_id', $user->id)->first();
        if ($employeeRecord && $employeeRecord->manager_id) {
            $ids[] = $employeeRecord->manager_id;
        }
        
        // Get colleagues (other employees under same manager)
        if ($employeeRecord && $employeeRecord->manager_id) {
            $colleagueIds = GrowBizEmployeeModel::where('manager_id', $employeeRecord->manager_id)
                ->whereNotNull('user_id')
                ->where('user_id', '!=', $user->id)
                ->pluck('user_id')
                ->toArray();
            $ids = array_merge($ids, $colleagueIds);
        }
        
        return array_unique($ids);
    }

    /**
     * Get team members for compose dropdown
     */
    private function getTeamMembers($user): array
    {
        $members = [];
        
        // Get employees managed by this user
        $employees = GrowBizEmployeeModel::where('manager_id', $user->id)
            ->whereNotNull('user_id')
            ->with('user')
            ->get();
            
        foreach ($employees as $employee) {
            if ($employee->user) {
                $members[] = [
                    'id' => $employee->user->id,
                    'name' => $employee->user->name,
                    'role' => $employee->role ?? 'Employee',
                ];
            }
        }
        
        // Get manager if user is an employee
        $employeeRecord = GrowBizEmployeeModel::where('user_id', $user->id)->first();
        if ($employeeRecord && $employeeRecord->manager_id) {
            $manager = DB::table('users')->find($employeeRecord->manager_id);
            if ($manager) {
                $members[] = [
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'role' => 'Manager',
                ];
            }
            
            // Get colleagues
            $colleagues = GrowBizEmployeeModel::where('manager_id', $employeeRecord->manager_id)
                ->whereNotNull('user_id')
                ->where('user_id', '!=', $user->id)
                ->with('user')
                ->get();
                
            foreach ($colleagues as $colleague) {
                if ($colleague->user) {
                    $members[] = [
                        'id' => $colleague->user->id,
                        'name' => $colleague->user->name,
                        'role' => $colleague->role ?? 'Colleague',
                    ];
                }
            }
        }
        
        return $members;
    }
}
