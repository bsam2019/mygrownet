<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Jobs\BizBoost\CampaignSequenceJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): Response
    {
        $business = $this->getBusiness($request);
        
        $campaigns = $business->campaigns()
            ->withCount('posts')
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('BizBoost/Campaigns/Index', [
            'campaigns' => $campaigns,
            'stats' => $this->getCampaignStats($business),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $business = $this->getBusiness($request);

        // Check campaign limit
        $result = $this->subscriptionService->canIncrement($user, 'campaigns', 'bizboost');
        if (!$result['allowed']) {
            return redirect()->route('bizboost.feature-upgrade', ['feature' => 'campaigns'])
                ->with('error', $result['reason']);
        }

        return Inertia::render('BizBoost/Campaigns/Create', [
            'objectives' => $this->getCampaignObjectives(),
            'templates' => $this->getCampaignTemplates(),
            'integrations' => $business->integrations()->where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'objective' => 'required|string|in:increase_sales,promote_stock,announce_discount,bring_back_customers,grow_followers',
            'duration_days' => 'required|integer|min:3|max:30',
            'start_date' => 'required|date|after_or_equal:today',
            'target_platforms' => 'required|array|min:1',
            'target_platforms.*' => 'string|in:facebook,instagram',
            'template_id' => 'nullable|integer',
            'auto_generate_content' => 'boolean',
        ]);

        $business = $this->getBusiness($request);

        // Check limit
        $result = $this->subscriptionService->canIncrement($request->user(), 'campaigns', 'bizboost');
        if (!$result['allowed']) {
            return back()->with('error', $result['reason']);
        }

        DB::beginTransaction();
        try {
            $campaign = $business->campaigns()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'objective' => $validated['objective'],
                'status' => 'draft',
                'start_date' => $validated['start_date'],
                'end_date' => \Carbon\Carbon::parse($validated['start_date'])->addDays($validated['duration_days'] - 1),
                'duration_days' => $validated['duration_days'],
                'target_platforms' => $validated['target_platforms'],
                'campaign_config' => [
                    'template_id' => $validated['template_id'] ?? null,
                    'auto_generate' => $validated['auto_generate_content'] ?? false,
                    'posting_times' => ['09:00', '12:00', '18:00'],
                ],
            ]);

            // Generate campaign posts based on objective
            if ($validated['auto_generate_content'] ?? false) {
                $this->generateCampaignPosts($campaign, $business);
            }

            DB::commit();

            return redirect()->route('bizboost.campaigns.show', $campaign->id)
                ->with('success', 'Campaign created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create campaign: ' . $e->getMessage());
        }
    }

    public function show(Request $request, int $id): Response
    {
        $business = $this->getBusiness($request);
        $campaign = $business->campaigns()->with(['posts.media'])->findOrFail($id);

        return Inertia::render('BizBoost/Campaigns/Show', [
            'campaign' => $campaign,
            'posts' => $campaign->posts()->with('media')->orderBy('scheduled_at')->get(),
            'analytics' => $this->getCampaignAnalytics($campaign),
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $business = $this->getBusiness($request);
        $campaign = $business->campaigns()->findOrFail($id);

        if ($campaign->status !== 'draft') {
            return redirect()->route('bizboost.campaigns.show', $id)
                ->with('error', 'Only draft campaigns can be edited.');
        }

        return Inertia::render('BizBoost/Campaigns/Edit', [
            'campaign' => $campaign,
            'objectives' => $this->getCampaignObjectives(),
            'templates' => $this->getCampaignTemplates(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $business = $this->getBusiness($request);
        $campaign = $business->campaigns()->findOrFail($id);

        if ($campaign->status !== 'draft') {
            return back()->with('error', 'Only draft campaigns can be edited.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'objective' => 'required|string',
            'duration_days' => 'required|integer|min:3|max:30',
            'start_date' => 'required|date|after_or_equal:today',
            'target_platforms' => 'required|array|min:1',
        ]);

        $campaign->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'objective' => $validated['objective'],
            'start_date' => $validated['start_date'],
            'end_date' => \Carbon\Carbon::parse($validated['start_date'])->addDays($validated['duration_days'] - 1),
            'duration_days' => $validated['duration_days'],
            'target_platforms' => $validated['target_platforms'],
        ]);

        return redirect()->route('bizboost.campaigns.show', $id)
            ->with('success', 'Campaign updated successfully!');
    }

    public function start(Request $request, int $id): RedirectResponse
    {
        $business = $this->getBusiness($request);
        $campaign = $business->campaigns()->findOrFail($id);

        if ($campaign->status !== 'draft') {
            return back()->with('error', 'Only draft campaigns can be started.');
        }

        if ($campaign->posts()->count() === 0) {
            return back()->with('error', 'Campaign must have at least one post. Add posts or enable auto-generate content.');
        }

        // Update start date to today if it's in the past
        $startDate = $campaign->start_date;
        if ($startDate->lt(now()->startOfDay())) {
            $startDate = now()->startOfDay();
        }

        $campaign->update([
            'status' => 'active',
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addDays($campaign->duration_days - 1)->toDateString(),
        ]);

        // Dispatch the campaign sequence job
        CampaignSequenceJob::dispatch($campaign->id);

        return back()->with('success', 'Campaign started successfully!');
    }

    public function pause(Request $request, int $id): RedirectResponse
    {
        $business = $this->getBusiness($request);
        $campaign = $business->campaigns()->findOrFail($id);

        if ($campaign->status !== 'active') {
            return back()->with('error', 'Only active campaigns can be paused.');
        }

        $campaign->update(['status' => 'paused']);

        return back()->with('success', 'Campaign paused.');
    }

    public function resume(Request $request, int $id): RedirectResponse
    {
        $business = $this->getBusiness($request);
        $campaign = $business->campaigns()->findOrFail($id);

        if ($campaign->status !== 'paused') {
            return back()->with('error', 'Only paused campaigns can be resumed.');
        }

        $campaign->update(['status' => 'active']);
        CampaignSequenceJob::dispatch($campaign->id);

        return back()->with('success', 'Campaign resumed.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $business = $this->getBusiness($request);
        $campaign = $business->campaigns()->findOrFail($id);

        if ($campaign->status === 'active') {
            return back()->with('error', 'Cannot delete an active campaign. Pause it first.');
        }

        $campaign->delete();

        return redirect()->route('bizboost.campaigns.index')
            ->with('success', 'Campaign deleted.');
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }

    private function getCampaignStats(BizBoostBusinessModel $business): array
    {
        return [
            'total' => $business->campaigns()->count(),
            'active' => $business->campaigns()->where('status', 'active')->count(),
            'completed' => $business->campaigns()->where('status', 'completed')->count(),
            'draft' => $business->campaigns()->where('status', 'draft')->count(),
        ];
    }

    private function getCampaignObjectives(): array
    {
        return [
            ['id' => 'increase_sales', 'name' => 'Increase Sales', 'description' => 'Drive more sales with promotional content'],
            ['id' => 'promote_stock', 'name' => 'Promote New Stock', 'description' => 'Showcase new products or arrivals'],
            ['id' => 'announce_discount', 'name' => 'Announce Discount', 'description' => 'Promote special offers and discounts'],
            ['id' => 'bring_back_customers', 'name' => 'Re-engage Customers', 'description' => 'Bring back inactive customers'],
            ['id' => 'grow_followers', 'name' => 'Grow Followers', 'description' => 'Increase social media following'],
        ];
    }

    private function getCampaignTemplates(): array
    {
        return [
            ['id' => 1, 'name' => '7-Day Sales Boost', 'duration' => 7, 'posts' => 7],
            ['id' => 2, 'name' => '14-Day Brand Awareness', 'duration' => 14, 'posts' => 10],
            ['id' => 3, 'name' => 'Weekend Flash Sale', 'duration' => 3, 'posts' => 5],
            ['id' => 4, 'name' => 'Product Launch', 'duration' => 7, 'posts' => 8],
        ];
    }

    private function generateCampaignPosts(BizBoostCampaignModel $campaign, BizBoostBusinessModel $business): void
    {
        $postTemplates = $this->getPostTemplatesForObjective($campaign->objective, $campaign->duration_days);

        foreach ($postTemplates as $day => $template) {
            $post = $business->posts()->create([
                'title' => $template['title'],
                'caption' => $template['caption'],
                'status' => 'draft',
                'post_type' => 'standard',
                'campaign_id' => $campaign->id,
            ]);

            // Link to campaign with sequence info
            DB::table('bizboost_campaign_posts')->insert([
                'campaign_id' => $campaign->id,
                'post_id' => $post->id,
                'sequence_day' => $day,
                'sequence_type' => $template['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $campaign->update(['posts_created' => count($postTemplates)]);
    }

    private function getPostTemplatesForObjective(string $objective, int $days): array
    {
        $templates = [];
        
        $contentMap = [
            'increase_sales' => [
                'intro' => ['title' => 'Special Offer Alert!', 'caption' => '🔥 Don\'t miss our amazing deals! Visit us today.'],
                'engagement' => ['title' => 'Customer Favorite', 'caption' => '⭐ Our customers love this! Have you tried it?'],
                'reminder' => ['title' => 'Last Chance!', 'caption' => '⏰ Hurry! This offer won\'t last forever.'],
                'cta' => ['title' => 'Shop Now', 'caption' => '🛒 Ready to save? Visit us today!'],
            ],
            'promote_stock' => [
                'intro' => ['title' => 'New Arrivals!', 'caption' => '✨ Fresh stock just landed! Be the first to see.'],
                'engagement' => ['title' => 'Sneak Peek', 'caption' => '👀 Here\'s a preview of what\'s new...'],
                'reminder' => ['title' => 'In Stock Now', 'caption' => '📦 Available now while stocks last!'],
                'cta' => ['title' => 'Get Yours', 'caption' => '🏃 Don\'t wait - get yours today!'],
            ],
        ];

        $content = $contentMap[$objective] ?? $contentMap['increase_sales'];
        $types = ['intro', 'engagement', 'reminder', 'cta'];

        for ($day = 1; $day <= $days; $day++) {
            $typeIndex = ($day - 1) % count($types);
            $type = $types[$typeIndex];
            $templates[$day] = array_merge($content[$type], ['type' => $type]);
        }

        return $templates;
    }

    private function getCampaignAnalytics(BizBoostCampaignModel $campaign): array
    {
        $posts = $campaign->posts;
        
        return [
            'total_posts' => $posts->count(),
            'published_posts' => $posts->where('status', 'published')->count(),
            'scheduled_posts' => $posts->where('status', 'scheduled')->count(),
            'total_engagement' => $posts->sum(fn($p) => $p->analytics['engagement'] ?? 0),
            'total_reach' => $posts->sum(fn($p) => $p->analytics['reach'] ?? 0),
        ];
    }
}
