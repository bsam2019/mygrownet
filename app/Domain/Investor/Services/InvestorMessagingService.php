<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorMessage;
use App\Domain\Investor\Repositories\InvestorMessageRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;

class InvestorMessagingService
{
    public function __construct(
        private readonly InvestorMessageRepositoryInterface $messageRepository,
        private readonly InvestorAccountRepositoryInterface $accountRepository
    ) {}

    /**
     * Send a message from investor to admin
     */
    public function sendMessageFromInvestor(
        int $investorAccountId,
        string $subject,
        string $content,
        ?int $parentId = null
    ): InvestorMessage {
        $investor = $this->accountRepository->findById($investorAccountId);
        
        if (!$investor) {
            throw new \DomainException('Investor account not found');
        }

        $message = InvestorMessage::create(
            investorAccountId: $investorAccountId,
            adminId: null, // Will be assigned when admin responds
            subject: $subject,
            content: $content,
            direction: 'inbound',
            parentId: $parentId
        );

        return $this->messageRepository->save($message);
    }

    /**
     * Send a message from admin to investor
     */
    public function sendMessageFromAdmin(
        int $adminId,
        int $investorAccountId,
        string $subject,
        string $content,
        ?int $parentId = null
    ): InvestorMessage {
        $investor = $this->accountRepository->findById($investorAccountId);
        
        if (!$investor) {
            throw new \DomainException('Investor account not found');
        }

        $message = InvestorMessage::create(
            investorAccountId: $investorAccountId,
            adminId: $adminId,
            subject: $subject,
            content: $content,
            direction: 'outbound',
            parentId: $parentId
        );

        return $this->messageRepository->save($message);
    }

    /**
     * Get messages for an investor
     */
    public function getMessagesForInvestor(int $investorAccountId, int $limit = 50): array
    {
        $messages = $this->messageRepository->findByInvestorAccountId($investorAccountId, $limit);
        
        return array_map(fn($msg) => $this->enrichMessage($msg), $messages);
    }

    /**
     * Get conversation thread for an investor
     */
    public function getConversation(int $investorAccountId, int $limit = 50): array
    {
        $messages = $this->messageRepository->findConversation($investorAccountId, $limit);
        
        return array_map(fn($msg) => $this->enrichMessage($msg), $messages);
    }

    /**
     * Get unread count for investor
     */
    public function getUnreadCountForInvestor(int $investorAccountId): int
    {
        return $this->messageRepository->getUnreadCountForInvestor($investorAccountId);
    }

    /**
     * Get unread count for admin (all investor messages)
     */
    public function getUnreadCountForAdmin(): int
    {
        return $this->messageRepository->getUnreadCountForAdmin();
    }

    /**
     * Mark message as read
     */
    public function markAsRead(int $messageId, int $investorAccountId): void
    {
        $message = $this->messageRepository->findById($messageId);
        
        if (!$message) {
            throw new \DomainException('Message not found');
        }

        // Only mark as read if it belongs to this investor
        if ($message->getInvestorAccountId() !== $investorAccountId) {
            throw new \DomainException('Unauthorized');
        }

        // Only mark outbound messages (from admin) as read by investor
        if ($message->isOutbound()) {
            $this->messageRepository->markAsRead($messageId);
        }
    }

    /**
     * Mark all messages as read for investor
     */
    public function markAllAsRead(int $investorAccountId): void
    {
        $this->messageRepository->markAllAsReadForInvestor($investorAccountId);
    }

    /**
     * Get all messages for admin view
     */
    public function getAllMessagesForAdmin(int $limit = 50, int $offset = 0): array
    {
        $messages = $this->messageRepository->getAllForAdmin($limit, $offset);
        
        return array_map(fn($msg) => $this->enrichMessageForAdmin($msg), $messages);
    }

    /**
     * Enrich message with additional data
     */
    private function enrichMessage(InvestorMessage $message): array
    {
        $data = $message->toArray();
        
        // Add sender info
        if ($message->isInbound()) {
            $data['sender_type'] = 'investor';
            $data['sender_label'] = 'You';
        } else {
            $data['sender_type'] = 'admin';
            $data['sender_label'] = 'MyGrowNet Team';
        }

        return $data;
    }

    /**
     * Enrich message with admin-specific data
     */
    private function enrichMessageForAdmin(InvestorMessage $message): array
    {
        $data = $message->toArray();
        
        // Get investor name
        $investor = $this->accountRepository->findById($message->getInvestorAccountId());
        $data['investor_name'] = $investor ? $investor->getName() : 'Unknown Investor';
        $data['investor_email'] = $investor ? $investor->getEmail() : '';

        // Add admin name if available
        if ($message->getAdminId()) {
            $admin = \App\Models\User::find($message->getAdminId());
            $data['admin_name'] = $admin ? $admin->name : 'Admin';
        }

        return $data;
    }
}
