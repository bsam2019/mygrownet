<?php

namespace App\Http\Controllers\BizBoost;

use App\Events\BizBoost\PostPublished;
use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Jobs\BizBoost\PublishToSocialMediaJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $posts = $business->posts()
            ->with('media')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => 
                $q->where('caption', 'like', "%{$search}%")
            )
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'draft' => $business->posts()->where('status', 'draft')->count(),
            'scheduled' => $business->posts()->where('status', 'scheduled')->count(),
            'published' => $business->posts()->where('status', 'published')->count(),
            'failed' => $business->posts()->where('status', 'failed')->count(),
        ];

        return Inertia::render('BizBoost/Posts/Index', [
            'posts' => $posts,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->getBusiness($request);
        
        // Check limit
        $result = $this->subscriptionService->canIncrement(
            $request->user(), 'posts_per_month', 'bizboost'
        );

        if (!$result['allowed']) {
            return Inertia::render('BizBoost/FeatureUpgradeRequired', [
                'feature' => 'posts',
                'message' => $result['reason'],
                'suggestedTier' => $result['suggested_tier'] ?? 'basic',
            ]);
        }

        $canSchedule = $this->subscriptionService->hasFeature(
            $request->user(), 'scheduling', 'bizboost'
        );

        $canAutoPost = $this->subscriptionService->hasFeature(
            $request->user(), 'auto_posting', 'bizboost'
        );

        $integrations = $business->integrations()
            ->where('status', 'active')
            ->get();

        // Load template if template_id is provided
        $template = null;
        if ($request->template_id) {
            $templateModel = \App\Infrastructure\Persistence\Eloquent\BizBoostTemplateModel::find($request->template_id);
            if ($templateModel) {
                // Increment usage count
                $templateModel->incrementUsage();
                
                $template = [
                    'id' => $templateModel->id,
                    'name' => $templateModel->name,
                    'description' => $templateModel->description,
                    'template_data' => $templateModel->template_data,
                ];
            }
        }

        // Load campaign if campaign_id is provided
        $campaign = null;
        if ($request->campaign_id) {
            $campaignModel = $business->campaigns()->find($request->campaign_id);
            // Allow adding posts to draft, active, or paused campaigns
            if ($campaignModel && in_array($campaignModel->status, ['draft', 'active', 'paused'])) {
                $campaign = [
                    'id' => $campaignModel->id,
                    'name' => $campaignModel->name,
                    'objective' => $campaignModel->objective,
                    'target_platforms' => $campaignModel->target_platforms,
                    'status' => $campaignModel->status,
                ];
            }
        }

        return Inertia::render('BizBoost/Posts/Create', [
            'canSchedule' => $canSchedule,
            'canAutoPost' => $canAutoPost,
            'integrations' => $integrations,
            'products' => $business->products()->where('is_active', true)->get(['id', 'name', 'price']),
            'template' => $template,
            'campaign' => $campaign,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'caption' => 'required|string|max:2200',
            'title' => 'nullable|string|max:255',
            'status' => 'required|in:draft,scheduled',
            'scheduled_at' => 'required_if:status,scheduled|nullable|date|after:now',
            'platform_targets' => 'nullable|array',
            'platform_targets.*' => 'string|in:facebook,instagram',
            'post_type' => 'nullable|in:standard,story,reel',
            'media' => 'nullable|array|max:10',
            'media.*' => 'file|mimes:jpeg,png,jpg,webp,mp4,mov|max:51200',
            'campaign_id' => 'nullable|integer|exists:bizboost_campaigns,id',
        ]);

        $user = $request->user();
        
        // Check limit again
        $canCreate = $this->subscriptionService->canIncrement($user, 'posts_per_month', 'bizboost');
        if (!$canCreate['allowed']) {
            return back()->with('error', $canCreate['reason']);
        }

        $business = $this->getBusiness($request);

        // Verify campaign belongs to this business if provided
        $campaignId = null;
        if (!empty($validated['campaign_id'])) {
            $campaign = $business->campaigns()->find($validated['campaign_id']);
            // Allow adding posts to draft, active, or paused campaigns
            if ($campaign && in_array($campaign->status, ['draft', 'active', 'paused'])) {
                $campaignId = $campaign->id;
            }
        }

        $post = $business->posts()->create([
            'caption' => $validated['caption'],
            'title' => $validated['title'] ?? null,
            'status' => $validated['status'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'platform_targets' => $validated['platform_targets'] ?? [],
            'post_type' => $validated['post_type'] ?? 'standard',
            'campaign_id' => $campaignId,
        ]);

        // Handle media uploads
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $index => $file) {
                $isVideo = in_array($file->getMimeType(), ['video/mp4', 'video/quicktime']);
                $path = $file->store('bizboost/posts', 'public');
                
                $post->media()->create([
                    'type' => $isVideo ? 'video' : 'image',
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'sort_order' => $index,
                ]);
            }
        }

        $message = $validated['status'] === 'scheduled' 
            ? 'Post scheduled successfully.' 
            : 'Post saved as draft.';

        // Redirect to campaign if post was created for a campaign
        if ($campaignId) {
            return redirect()->route('bizboost.campaigns.show', $campaignId)
                ->with('success', $message);
        }

        return redirect()->route('bizboost.posts.index')
            ->with('success', $message);
    }

    public function show(Request $request, int $id): Response
    {
        $business = $this->getBusiness($request);
        $post = $business->posts()->with('media')->findOrFail($id);

        // dd($post);

        return Inertia::render('BizBoost/Posts/Show', [
            'post' => $post,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $business = $this->getBusiness($request);
        $post = $business->posts()->with('media')->findOrFail($id);

        if ($post->status === 'published') {
            return redirect()->route('bizboost.posts.show', $id)
                ->with('error', 'Published posts cannot be edited.');
        }

        $canSchedule = $this->subscriptionService->hasFeature(
            $request->user(), 'scheduling', 'bizboost'
        );

        $canAutoPost = $this->subscriptionService->hasFeature(
            $request->user(), 'auto_posting', 'bizboost'
        );

        $integrations = $business->integrations()
            ->where('status', 'active')
            ->get();

        return Inertia::render('BizBoost/Posts/Edit', [
            'post' => $post,
            'canSchedule' => $canSchedule,
            'canAutoPost' => $canAutoPost,
            'integrations' => $integrations,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'caption' => 'required|string|max:2200',
            'title' => 'nullable|string|max:255',
            'status' => 'required|in:draft,scheduled',
            'scheduled_at' => 'required_if:status,scheduled|nullable|date|after:now',
            'platform_targets' => 'nullable|array',
            'post_type' => 'nullable|in:standard,story,reel',
        ]);

        $business = $this->getBusiness($request);
        $post = $business->posts()->findOrFail($id);

        if ($post->status === 'published') {
            return back()->with('error', 'Published posts cannot be edited.');
        }

        $post->update($validated);

        return back()->with('success', 'Post updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $post = $business->posts()->findOrFail($id);

        // Delete media from storage
        foreach ($post->media as $media) {
            Storage::disk('public')->delete($media->path);
            if ($media->thumbnail_path) {
                Storage::disk('public')->delete($media->thumbnail_path);
            }
        }

        $post->delete();

        return redirect()->route('bizboost.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    public function duplicate(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $post = $business->posts()->with('media')->findOrFail($id);

        // Check limit
        $result = $this->subscriptionService->canIncrement(
            $request->user(), 'posts_per_month', 'bizboost'
        );

        if (!$result['allowed']) {
            return back()->with('error', $result['reason']);
        }

        $newPost = $post->replicate();
        $newPost->status = 'draft';
        $newPost->scheduled_at = null;
        $newPost->published_at = null;
        $newPost->external_ids = null;
        $newPost->analytics = null;
        $newPost->error_message = null;
        $newPost->retry_count = 0;
        $newPost->save();

        // Duplicate media references (not files)
        foreach ($post->media as $media) {
            $newMedia = $media->replicate();
            $newMedia->post_id = $newPost->id;
            $newMedia->save();
        }

        return redirect()->route('bizboost.posts.edit', $newPost->id)
            ->with('success', 'Post duplicated. Edit and save when ready.');
    }

    public function publishNow(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $post = $business->posts()->findOrFail($id);

        // Check if user has auto_posting feature
        if (!$this->subscriptionService->hasFeature($request->user(), 'auto_posting', 'bizboost')) {
            return back()->with('error', 'Auto-posting requires Professional plan or higher.');
        }

        if (!in_array($post->status, ['draft', 'scheduled', 'failed'])) {
            return back()->with('error', 'This post cannot be published.');
        }

        if (empty($post->platform_targets)) {
            return back()->with('error', 'Please select at least one platform to publish to.');
        }

        // Dispatch the publish job
        PublishToSocialMediaJob::dispatch($post);

        $post->update(['status' => 'publishing']);

        // Broadcast real-time event
        broadcast(new PostPublished($business->id, [
            'id' => $post->id,
            'title' => $post->title,
            'caption' => substr($post->caption, 0, 100),
            'status' => 'publishing',
            'platform_targets' => $post->platform_targets,
        ]))->toOthers();

        return back()->with('success', 'Post is being published...');
    }

    public function retry(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $post = $business->posts()->findOrFail($id);

        if ($post->status !== 'failed') {
            return back()->with('error', 'Only failed posts can be retried.');
        }

        // Check if user has auto_posting feature
        if (!$this->subscriptionService->hasFeature($request->user(), 'auto_posting', 'bizboost')) {
            return back()->with('error', 'Auto-posting requires Professional plan or higher.');
        }

        // Dispatch the publish job
        PublishToSocialMediaJob::dispatch($post);

        $post->update([
            'status' => 'publishing',
            'error_message' => null,
        ]);

        return back()->with('success', 'Retrying post publication...');
    }

    public function getShareLinks(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $post = $business->posts()->with('media')->findOrFail($id);

        $caption = urlencode($post->caption);
        $mediaUrl = $post->media->first() 
            ? url(Storage::url($post->media->first()->path))
            : null;

        return response()->json([
            'caption' => $post->caption,
            'media_url' => $mediaUrl,
            'share_links' => [
                'facebook' => "https://www.facebook.com/sharer/sharer.php?quote={$caption}",
                'twitter' => "https://twitter.com/intent/tweet?text={$caption}",
                'whatsapp' => "https://wa.me/?text={$caption}",
                'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url=" . urlencode(route('bizboost.public.business', $business->slug)),
            ],
        ]);
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
