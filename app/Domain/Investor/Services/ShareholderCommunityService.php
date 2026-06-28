<?php

namespace App\Domain\Investor\Services;

use App\Models\ShareholderForumCategory;
use App\Models\ShareholderForumTopic;
use App\Models\ShareholderForumReply;
use App\Models\ShareholderDirectoryProfile;
use App\Models\ShareholderContactRequest;
use App\Models\InvestorAccount;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Shareholder Community Service
 * 
 * Manages forum discussions, directory, and networking
 * for shareholders of the private limited company.
 */
class ShareholderCommunityService
{
    // =====================================================
    // FORUM METHODS
    // =====================================================

    /**
     * Get forum categories with topic counts
     */
    public function getForumCategories(): Collection
    {
        return ShareholderForumCategory::active()
            ->withCount(['topics' => fn($q) => $q->where('status', 'approved')])
            ->get();
    }

    /**
     * Get topics in a category
     */
    public function getCategoryTopics(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return ShareholderForumTopic::where('category_id', $categoryId)
            ->approved()
            ->with(['author.user', 'category'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_reply_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get a topic with replies
     */
    public function getTopicWithReplies(string $slug): array
    {
        $topic = ShareholderForumTopic::where('slug', $slug)
            ->approved()
            ->with(['author.user', 'category'])
            ->firstOrFail();

        $topic->incrementViews();

        $replies = ShareholderForumReply::where('topic_id', $topic->id)
            ->approved()
            ->with(['author.user'])
            ->orderBy('created_at')
            ->get();

        return [
            'topic' => $topic,
            'replies' => $replies,
        ];
    }

    /**
     * Create a new topic (pending moderation)
     */
    public function createTopic(
        InvestorAccount $author,
        int $categoryId,
        string $title,
        string $content
    ): ShareholderForumTopic {
        $category = ShareholderForumCategory::findOrFail($categoryId);

        return ShareholderForumTopic::create([
            'category_id' => $categoryId,
            'author_investor_id' => $author->id,
            'title' => $title,
            'content' => $content,
            'status' => $category->requires_moderation ? 'pending_moderation' : 'approved',
        ]);
    }

    /**
     * Create a reply to a topic (pending moderation)
     */
    public function createReply(
        InvestorAccount $author,
        int $topicId,
        string $content
    ): ShareholderForumReply {
        $topic = ShareholderForumTopic::findOrFail($topicId);

        return ShareholderForumReply::create([
            'topic_id' => $topicId,
            'author_investor_id' => $author->id,
            'content' => $content,
            'status' => $topic->category->requires_moderation ? 'pending_moderation' : 'approved',
        ]);
    }

    // =====================================================
    // DIRECTORY METHODS
    // =====================================================

    /**
     * Get listed shareholders in directory
     */
    public function getDirectoryListings(int $perPage = 20): LengthAwarePaginator
    {
        return ShareholderDirectoryProfile::listed()
            ->with(['investorAccount.user'])
            ->orderBy('display_name')
            ->paginate($perPage);
    }

    /**
     * Get or create directory profile for investor
     */
    public function getOrCreateProfile(InvestorAccount $investor): ShareholderDirectoryProfile
    {
        return ShareholderDirectoryProfile::firstOrCreate(
            ['investor_account_id' => $investor->id],
            [
                'is_listed' => false,
                'display_name' => $investor->user->name ?? 'Anonymous',
                'allow_contact' => false,
            ]
        );
    }

    /**
     * Update directory profile
     */
    public function updateProfile(
        InvestorAccount $investor,
        array $data
    ): ShareholderDirectoryProfile {
        $profile = $this->getOrCreateProfile($investor);
        
        $profile->update([
            'is_listed' => $data['is_listed'] ?? false,
            'display_name' => $data['display_name'] ?? $profile->display_name,
            'industry' => $data['industry'] ?? null,
            'location' => $data['location'] ?? null,
            'bio' => $data['bio'] ?? null,
            'show_investment_date' => $data['show_investment_date'] ?? false,
            'allow_contact' => $data['allow_contact'] ?? false,
        ]);

        return $profile;
    }

    // =====================================================
    // CONTACT REQUEST METHODS
    // =====================================================

    /**
     * Send a contact request to another shareholder
     */
    public function sendContactRequest(
        InvestorAccount $requester,
        InvestorAccount $recipient,
        string $message
    ): ShareholderContactRequest {
        // Check if recipient allows contact
        $recipientProfile = ShareholderDirectoryProfile::where('investor_account_id', $recipient->id)->first();
        
        if (!$recipientProfile || !$recipientProfile->canBeContacted()) {
            throw new \InvalidArgumentException('This shareholder is not accepting contact requests.');
        }

        // Check for existing pending request
        $existing = ShareholderContactRequest::where('requester_id', $requester->id)
            ->where('recipient_id', $recipient->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('You already have a pending request to this shareholder.');
        }

        return ShareholderContactRequest::create([
            'requester_id' => $requester->id,
            'recipient_id' => $recipient->id,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    /**
     * Get pending contact requests for an investor
     */
    public function getPendingContactRequests(InvestorAccount $investor): Collection
    {
        return ShareholderContactRequest::where('recipient_id', $investor->id)
            ->pending()
            ->with(['requester.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get sent contact requests
     */
    public function getSentContactRequests(InvestorAccount $investor): Collection
    {
        return ShareholderContactRequest::where('requester_id', $investor->id)
            ->with(['recipient.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Respond to a contact request
     */
    public function respondToContactRequest(
        ShareholderContactRequest $request,
        bool $accept,
        ?string $response = null
    ): void {
        if ($accept) {
            $request->accept($response);
        } else {
            $request->decline($response);
        }
    }
}
