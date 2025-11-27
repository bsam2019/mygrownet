<?php

namespace App\Domain\Investor\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvestorMessagingService
{
    /**
     * Get all messages for an investor
     */
    public function getMessagesForInvestor(int $investorAccountId): array
    {
        $messages = DB::table('investor_messages')
            ->where('investor_account_id', $investorAccountId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'subject' => $message->subject,
                'content' => $message->content,
                'preview' => \Str::limit(strip_tags($message->content), 100),
                'direction' => $message->direction,
                'is_read' => (bool) $message->is_read,
                'sender_label' => $message->direction === 'inbound' ? 'You' : 'MyGrowNet Team',
                'created_at_human' => Carbon::parse($message->created_at)->diffForHumans(),
                'parent_id' => $message->parent_id,
            ];
        })->toArray();
    }

    /**
     * Get unread message count for an investor
     */
    public function getUnreadCountForInvestor(int $investorAccountId): int
    {
        return DB::table('investor_messages')
            ->where('investor_account_id', $investorAccountId)
            ->where('direction', 'outbound') // Only count unread messages FROM admin
            ->where('is_read', false)
            ->count();
    }

    /**
     * Send a message from an investor
     */
    public function sendMessageFromInvestor(
        int $investorAccountId,
        string $subject,
        string $content,
        ?int $parentId = null
    ): int {
        return DB::table('investor_messages')->insertGetId([
            'investor_account_id' => $investorAccountId,
            'subject' => $subject,
            'content' => $content,
            'direction' => 'inbound',
            'parent_id' => $parentId,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Send a message from admin to investor
     */
    public function sendMessageToInvestor(
        int $investorAccountId,
        string $subject,
        string $content,
        int $adminUserId,
        ?int $parentId = null
    ): int {
        return DB::table('investor_messages')->insertGetId([
            'investor_account_id' => $investorAccountId,
            'subject' => $subject,
            'content' => $content,
            'direction' => 'outbound',
            'admin_user_id' => $adminUserId,
            'parent_id' => $parentId,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Mark a message as read
     */
    public function markAsRead(int $messageId, int $investorAccountId): bool
    {
        return DB::table('investor_messages')
            ->where('id', $messageId)
            ->where('investor_account_id', $investorAccountId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * Get all messages (for admin)
     */
    public function getAllMessages(): array
    {
        $messages = DB::table('investor_messages')
            ->join('investor_accounts', 'investor_messages.investor_account_id', '=', 'investor_accounts.id')
            ->select('investor_messages.*', 'investor_accounts.name as investor_name', 'investor_accounts.email as investor_email')
            ->orderBy('investor_messages.created_at', 'desc')
            ->get();

        return $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'investor_account_id' => $message->investor_account_id,
                'investor_name' => $message->investor_name,
                'investor_email' => $message->investor_email,
                'subject' => $message->subject,
                'content' => $message->content,
                'preview' => \Str::limit(strip_tags($message->content), 100),
                'direction' => $message->direction,
                'is_read' => (bool) $message->is_read,
                'created_at_human' => Carbon::parse($message->created_at)->diffForHumans(),
            ];
        })->toArray();
    }

    /**
     * Get messages for a specific investor (admin view)
     */
    public function getMessagesForInvestorAdmin(int $investorAccountId): array
    {
        return $this->getMessagesForInvestor($investorAccountId);
    }
}
