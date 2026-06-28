<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Investor\Concerns\RequiresInvestorAuth;
use App\Domain\Investor\Services\ShareholderCommunityService;
use App\Models\ShareholderForumTopic;
use App\Models\ShareholderContactRequest;
use App\Models\InvestorAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommunityController extends Controller
{
    use RequiresInvestorAuth;

    public function __construct(
        private ShareholderCommunityService $communityService
    ) {}

    // =====================================================
    // FORUM
    // =====================================================

    /**
     * Display forum categories
     */
    public function forum(Request $request)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $categories = $this->communityService->getForumCategories();

        return Inertia::render('Investor/Forum', [
            'investor' => [
                'id' => $domainInvestor->getId(),
                'name' => $domainInvestor->getName(),
                'email' => $domainInvestor->getEmail(),
            ],
            'categories' => $categories,
            'activePage' => 'forum',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    /**
     * Display topics in a category
     */
    public function forumCategory(Request $request, int $categoryId)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $categories = $this->communityService->getForumCategories();
        $topics = $this->communityService->getCategoryTopics($categoryId);

        return Inertia::render('Investor/ForumCategory', [
            'investor' => [
                'id' => $domainInvestor->getId(),
                'name' => $domainInvestor->getName(),
                'email' => $domainInvestor->getEmail(),
            ],
            'categories' => $categories,
            'topics' => $topics,
            'currentCategoryId' => $categoryId,
            'activePage' => 'forum',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    /**
     * Display a topic with replies
     */
    public function forumTopic(Request $request, string $slug)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $data = $this->communityService->getTopicWithReplies($slug);

        return Inertia::render('Investor/ForumTopic', [
            ...$data,
            'investor' => [
                'id' => $domainInvestor->getId(),
                'name' => $domainInvestor->getName(),
                'email' => $domainInvestor->getEmail(),
            ],
            'activePage' => 'forum',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    /**
     * Create a new topic
     */
    public function createTopic(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:shareholder_forum_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
        ]);

        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());

        try {
            $topic = $this->communityService->createTopic(
                author: $investor,
                categoryId: $validated['category_id'],
                title: $validated['title'],
                content: $validated['content']
            );

            $message = $topic->isPending() 
                ? 'Topic submitted for moderation.' 
                : 'Topic created successfully.';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a reply to a topic
     */
    public function createReply(Request $request, ShareholderForumTopic $topic)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $investor = InvestorAccount::find($domainInvestor->getId());

        try {
            $reply = $this->communityService->createReply(
                author: $investor,
                topicId: $topic->id,
                content: $validated['content']
            );

            $message = $reply->isPending() 
                ? 'Reply submitted for moderation.' 
                : 'Reply posted successfully.';

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // =====================================================
    // DIRECTORY
    // =====================================================

    /**
     * Display shareholder directory
     */
    public function directory(Request $request)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());
        $listings = $this->communityService->getDirectoryListings();
        $myProfile = $this->communityService->getOrCreateProfile($investor);

        return Inertia::render('Investor/Directory', [
            'investor' => [
                'id' => $domainInvestor->getId(),
                'name' => $domainInvestor->getName(),
                'email' => $domainInvestor->getEmail(),
            ],
            'listings' => $listings,
            'myProfile' => $myProfile,
            'activePage' => 'directory',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    /**
     * Update directory profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'is_listed' => 'boolean',
            'display_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:500',
            'show_investment_date' => 'boolean',
            'allow_contact' => 'boolean',
        ]);

        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());

        $this->communityService->updateProfile($investor, $validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    // =====================================================
    // CONTACT REQUESTS
    // =====================================================

    /**
     * Send a contact request
     */
    public function sendContactRequest(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:investor_accounts,id',
            'message' => 'required|string|max:1000',
        ]);

        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $requester = InvestorAccount::find($domainInvestor->getId());
        $recipient = InvestorAccount::findOrFail($validated['recipient_id']);

        try {
            $this->communityService->sendContactRequest(
                requester: $requester,
                recipient: $recipient,
                message: $validated['message']
            );

            return back()->with('success', 'Contact request sent.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Respond to a contact request
     */
    public function respondToContactRequest(Request $request, ShareholderContactRequest $contactRequest)
    {
        $validated = $request->validate([
            'accept' => 'required|boolean',
            'response' => 'nullable|string|max:500',
        ]);

        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());

        if ($contactRequest->recipient_id !== $investor->id) {
            abort(403);
        }

        $this->communityService->respondToContactRequest(
            request: $contactRequest,
            accept: $validated['accept'],
            response: $validated['response'] ?? null
        );

        $message = $validated['accept'] ? 'Request accepted.' : 'Request declined.';
        return back()->with('success', $message);
    }

    /**
     * Get contact requests
     */
    public function contactRequests(Request $request)
    {
        $domainInvestor = $this->requireInvestorAuth();
        
        if ($domainInvestor instanceof \Illuminate\Http\RedirectResponse) {
            return $domainInvestor;
        }

        $investor = InvestorAccount::find($domainInvestor->getId());
        $pending = $this->communityService->getPendingContactRequests($investor);
        $sent = $this->communityService->getSentContactRequests($investor);

        return Inertia::render('Investor/ContactRequests', [
            'investor' => [
                'id' => $domainInvestor->getId(),
                'name' => $domainInvestor->getName(),
                'email' => $domainInvestor->getEmail(),
            ],
            'pendingRequests' => $pending,
            'sentRequests' => $sent,
            'activePage' => 'directory',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }
}
