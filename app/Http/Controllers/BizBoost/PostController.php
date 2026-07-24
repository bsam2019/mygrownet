<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\MarketingService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\PostRepositoryInterface;
use App\Domain\BizBoost\Repositories\TemplateRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomTemplateRepositoryInterface;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function __construct(
        private MarketingService $marketingService,
        private BusinessService $businessService,
        private PostRepositoryInterface $postRepo,
        private TemplateRepositoryInterface $templateRepo,
        private CustomTemplateRepositoryInterface $customTemplateRepo,
        private IntegrationRepositoryInterface $integrationRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $filters = $request->only(['search', 'status', 'platform', 'date_from', 'date_to', 'sort']);
        $posts = $this->marketingService->getPosts($business->id, $filters);

        return Inertia::render('BizBoost/Posts/Index', [
            'posts' => $posts,
            'filters' => $filters,
            'stats' => $this->marketingService->getPostStats($business->id),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Posts/Create', [
            'templates' => $this->templateRepo->findByBusiness($business->id),
            'customTemplates' => $this->customTemplateRepo->findByBusiness($business->id),
            'integrations' => $this->integrationRepo->findByBusiness($business->id, ['status' => 'connected']),
            'platforms' => config('modules.bizboost.social_platforms', ['facebook', 'instagram', 'twitter', 'linkedin', 'whatsapp']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:facebook,instagram,twitter,linkedin,whatsapp',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,pdf|max:15360',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
            'status' => 'required|in:draft,scheduled,published',
            'template_id' => 'nullable|integer',
            'custom_template_id' => 'nullable|integer',
            'link' => 'nullable|url|max:500',
            'call_to_action' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'location_id' => 'nullable|integer|exists:biz_boost_locations,id',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->createPost($business->id, $validated, $request->file('media', []));

        $message = $validated['status'] === 'scheduled'
            ? 'Post scheduled successfully.'
            : 'Post created successfully.';

        return redirect()->route('bizboost.posts.index')->with('success', $message);
    }

    public function edit(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $post = $this->postRepo->findById($business->id, $id);

        if (!$post) {
            abort(404);
        }

        return Inertia::render('BizBoost/Posts/Edit', [
            'post' => $post->toArray(),
            'templates' => $this->templateRepo->findByBusiness($business->id),
            'customTemplates' => $this->customTemplateRepo->findByBusiness($business->id),
            'integrations' => $this->integrationRepo->findByBusiness($business->id, ['status' => 'connected']),
            'platforms' => config('modules.bizboost.social_platforms', ['facebook', 'instagram', 'twitter', 'linkedin', 'whatsapp']),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:facebook,instagram,twitter,linkedin,whatsapp',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,pdf|max:15360',
            'scheduled_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,published',
            'template_id' => 'nullable|integer',
            'custom_template_id' => 'nullable|integer',
            'link' => 'nullable|url|max:500',
            'call_to_action' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'location_id' => 'nullable|integer|exists:biz_boost_locations,id',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->updatePost($business->id, $id, $validated, $request->file('media', []));

        return redirect()->route('bizboost.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->deletePost($business->id, $id);

        return redirect()->route('bizboost.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    public function publish(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $result = $this->marketingService->publishPost($business->id, $id);

        if (!$result) {
            return back()->with('error', 'Failed to publish post. No connected social accounts found.');
        }

        return redirect()->route('bizboost.posts.index')
            ->with('success', 'Post published successfully.');
    }

    public function analytics(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $post = $this->postRepo->findById($business->id, $id);

        if (!$post) {
            abort(404);
        }

        return Inertia::render('BizBoost/Posts/Analytics', [
            'post' => $post->toArray(),
            'analytics' => $this->marketingService->getPostAnalytics($business->id, $id),
        ]);
    }
}