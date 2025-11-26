<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\InvestorMessagingService;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestorMessageController extends Controller
{
    public function __construct(
        private readonly InvestorMessagingService $messagingService,
        private readonly InvestorAccountRepositoryInterface $accountRepository
    ) {}

    /**
     * Display all investor messages
     */
    public function index(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $messages = $this->messagingService->getAllMessagesForAdmin($perPage, $offset);
        $unreadCount = $this->messagingService->getUnreadCountForAdmin();

        // Get all investors for compose dropdown
        $investors = $this->accountRepository->all();
        $investorList = array_map(fn($inv) => [
            'id' => $inv->getId(),
            'name' => $inv->getName(),
            'email' => $inv->getEmail(),
        ], $investors);

        return Inertia::render('Admin/Investor/Messages/Index', [
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'investors' => $investorList,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'has_more' => count($messages) === $perPage,
            ],
        ]);
    }

    /**
     * Show conversation with a specific investor
     */
    public function show(int $investorAccountId)
    {
        $investor = $this->accountRepository->findById($investorAccountId);
        
        if (!$investor) {
            return back()->with('error', 'Investor not found');
        }

        $messages = $this->messagingService->getConversation($investorAccountId);

        // Mark inbound messages as read
        foreach ($messages as $message) {
            if ($message['direction'] === 'inbound' && !$message['is_read']) {
                $this->messagingService->markAsRead($message['id'], $investorAccountId);
            }
        }

        return Inertia::render('Admin/Investor/Messages/Show', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message to an investor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'investor_account_id' => 'required|integer|exists:investor_accounts,id',
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'parent_id' => 'nullable|integer|exists:investor_messages,id',
        ]);

        try {
            $this->messagingService->sendMessageFromAdmin(
                adminId: auth()->id(),
                investorAccountId: $validated['investor_account_id'],
                subject: $validated['subject'],
                content: $validated['content'],
                parentId: $validated['parent_id'] ?? null
            );

            return back()->with('success', 'Message sent successfully');
        } catch (\Exception $e) {
            \Log::error('Admin investor message error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send message');
        }
    }

    /**
     * Reply to a message
     */
    public function reply(Request $request, int $messageId)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        // Get the original message to find the investor
        $originalMessage = app(\App\Domain\Investor\Repositories\InvestorMessageRepositoryInterface::class)
            ->findById($messageId);

        if (!$originalMessage) {
            return back()->with('error', 'Original message not found');
        }

        try {
            $this->messagingService->sendMessageFromAdmin(
                adminId: auth()->id(),
                investorAccountId: $originalMessage->getInvestorAccountId(),
                subject: 'Re: ' . $originalMessage->getSubject(),
                content: $validated['content'],
                parentId: $messageId
            );

            return back()->with('success', 'Reply sent successfully');
        } catch (\Exception $e) {
            \Log::error('Admin investor reply error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send reply');
        }
    }
}
