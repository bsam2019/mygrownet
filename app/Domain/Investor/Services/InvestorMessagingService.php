<?php

namespace App\Domain\Investor\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvestorMessagingService
{
    /**
     * Get all messages for an investor
     * 
     * Table uses:
     * - direction: 'from_investor' (investor sent) or 'to_investor' (admin sent)
     * - status: 'unread', 'read', 'replied', 'archived'
     */
    public function getMessagesForInvestor(int $investorAccountId): array
    {
        $messages = DB::table('investor_messages')
            ->where('investor_account_id', $investorAccountId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $messages->map(function ($message) {
            // Map database direction to frontend expected values
            $direction = $message->direction === 'from_investor' ? 'inbound' : 'outbound';
            $isRead = in_array($message->status, ['read', 'replied', 'archived']);
            
            return [
                'id' => $message->id,
                'subject' => $message->subject,
                'content' => $message->content,
                'preview' => \Str::limit(strip_tags($message->content), 100),
                'direction' => $direction,
                'is_read' => $isRead,
                'sender_label' => $direction === 'inbound' ? 'You' : 'MyGrowNet Team',
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
            ->where('direction', 'to_investor') // Only count unread messages FROM admin
            ->where('status', 'unread')
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
            'direction' => 'from_investor',
            'status' => 'unread',
            'parent_id' => $parentId,
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
            'direction' => 'to_investor',
            'admin_id' => $adminUserId,
            'status' => 'unread',
            'parent_id' => $parentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Send a message from admin (alias for sendMessageToInvestor)
     */
    public function sendMessageFromAdmin(
        int $adminId,
        int $investorAccountId,
        string $subject,
        string $content,
        ?int $parentId = null
    ): int {
        return $this->sendMessageToInvestor($investorAccountId, $subject, $content, $adminId, $parentId);
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
                'status' => 'read',
                'read_at' => now(),
                'updated_at' => now(),
            ]) > 0;
    }

    /**
     * Mark a message as read (admin version - doesn't require investor account ID)
     */
    public function markAsReadByAdmin(int $messageId): bool
    {
        return DB::table('investor_messages')
            ->where('id', $messageId)
            ->update([
                'status' => 'read',
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
            $direction = $message->direction === 'from_investor' ? 'inbound' : 'outbound';
            $isRead = in_array($message->status, ['read', 'replied', 'archived']);
            
            return [
                'id' => $message->id,
                'investor_account_id' => $message->investor_account_id,
                'investor_name' => $message->investor_name,
                'investor_email' => $message->investor_email,
                'subject' => $message->subject,
                'content' => $message->content,
                'preview' => \Str::limit(strip_tags($message->content), 100),
                'direction' => $direction,
                'is_read' => $isRead,
                'created_at_human' => Carbon::parse($message->created_at)->diffForHumans(),
            ];
        })->toArray();
    }

    /**
     * Get all messages for admin with pagination
     */
    public function getAllMessagesForAdmin(int $limit = 50, int $offset = 0): array
    {
        $messages = DB::table('investor_messages')
            ->join('investor_accounts', 'investor_messages.investor_account_id', '=', 'investor_accounts.id')
            ->leftJoin('users', 'investor_messages.admin_id', '=', 'users.id')
            ->select(
                'investor_messages.*', 
                'investor_accounts.name as investor_name', 
                'investor_accounts.email as investor_email',
                'users.name as admin_name'
            )
            ->orderBy('investor_messages.created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return $messages->map(function ($message) {
            $direction = $message->direction === 'from_investor' ? 'inbound' : 'outbound';
            $isRead = in_array($message->status, ['read', 'replied', 'archived']);
            
            return [
                'id' => $message->id,
                'investor_account_id' => $message->investor_account_id,
                'investor_name' => $message->investor_name,
                'investor_email' => $message->investor_email,
                'subject' => $message->subject,
                'content' => $message->content,
                'preview' => \Str::limit(strip_tags($message->content), 100),
                'direction' => $direction,
                'is_read' => $isRead,
                'admin_name' => $message->admin_name,
                'created_at_human' => Carbon::parse($message->created_at)->diffForHumans(),
            ];
        })->toArray();
    }

    /**
     * Get unread message count for admin (messages from investors)
     */
    public function getUnreadCountForAdmin(): int
    {
        return DB::table('investor_messages')
            ->where('direction', 'from_investor')
            ->where('status', 'unread')
            ->count();
    }

    /**
     * Get conversation with a specific investor (for admin view)
     */
    public function getConversation(int $investorAccountId): array
    {
        $messages = DB::table('investor_messages')
            ->leftJoin('users', 'investor_messages.admin_id', '=', 'users.id')
            ->where('investor_messages.investor_account_id', $investorAccountId)
            ->select('investor_messages.*', 'users.name as admin_name')
            ->orderBy('investor_messages.created_at', 'asc')
            ->get();

        return $messages->map(function ($message) {
            $direction = $message->direction === 'from_investor' ? 'inbound' : 'outbound';
            $isRead = in_array($message->status, ['read', 'replied', 'archived']);
            
            return [
                'id' => $message->id,
                'subject' => $message->subject,
                'content' => $message->content,
                'preview' => \Str::limit(strip_tags($message->content), 100),
                'direction' => $direction,
                'is_read' => $isRead,
                'admin_name' => $message->admin_name,
                'created_at_human' => Carbon::parse($message->created_at)->diffForHumans(),
                'parent_id' => $message->parent_id,
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

    /**
     * Find a message by ID
     */
    public function findById(int $messageId): ?object
    {
        return DB::table('investor_messages')->where('id', $messageId)->first();
    }
}
