<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\SitePost;
use App\Infrastructure\GrowBuilder\Models\SitePostCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SitePostController extends Controller
{
    /**
     * List posts
     */
    public function index(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $query = SitePost::forSite($site->id)
            ->with(['author', 'categories']);

        // Non-admins can only see their own posts
        if ($user->role?->level < 100) {
            $query->where('author_id', $user->id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->latest()->paginate(10);

        return Inertia::render('SiteMember/Posts/Index', [
            'site' => $this->getSiteData($site),
            'user' => $this->getUserData($user),
            'posts' => $posts,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Show create form
     */
    public function create(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $categories = SitePostCategory::forSite($site->id)
            ->ordered()
            ->get();

        return Inertia::render('SiteMember/Posts/Create', [
            'site' => $this->getSiteData($site),
            'user' => $this->getUserData($user),
            'categories' => $categories,
        ]);
    }

    /**
     * Store new post
     */
    public function store(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published,scheduled',
            'visibility' => 'required|in:public,members,private',
            'scheduled_at' => 'nullable|date|after:now',
            'categories' => 'nullable|array',
            'comments_enabled' => 'boolean',
        ]);

        $post = SitePost::create([
            'site_id' => $site->id,
            'author_id' => $user->id,
            'title' => $request->title,
            'slug' => SitePost::generateSlug($request->title, $site->id),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'featured_image' => $request->featured_image,
            'status' => $request->status,
            'visibility' => $request->visibility,
            'published_at' => $request->status === 'published' ? now() : null,
            'scheduled_at' => $request->scheduled_at,
            'comments_enabled' => $request->comments_enabled ?? true,
        ]);

        if ($request->filled('categories')) {
            $post->categories()->sync($request->categories);
        }

        return redirect()
            ->route('site.member.posts.index', ['subdomain' => $subdomain])
            ->with('success', 'Post created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $post = $this->getPost($site->id, $user, $id);

        $categories = SitePostCategory::forSite($site->id)
            ->ordered()
            ->get();

        return Inertia::render('SiteMember/Posts/Edit', [
            'site' => $this->getSiteData($site),
            'user' => $this->getUserData($user),
            'post' => $post->load('categories'),
            'categories' => $categories,
        ]);
    }

    /**
     * Update post
     */
    public function update(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $post = $this->getPost($site->id, $user, $id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published,scheduled,archived',
            'visibility' => 'required|in:public,members,private',
            'scheduled_at' => 'nullable|date',
            'categories' => 'nullable|array',
            'comments_enabled' => 'boolean',
        ]);

        $post->update([
            'title' => $request->title,
            'slug' => SitePost::generateSlug($request->title, $site->id, $post->id),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'featured_image' => $request->featured_image,
            'status' => $request->status,
            'visibility' => $request->visibility,
            'published_at' => $request->status === 'published' && !$post->published_at 
                ? now() 
                : $post->published_at,
            'scheduled_at' => $request->scheduled_at,
            'comments_enabled' => $request->comments_enabled ?? true,
        ]);

        $post->categories()->sync($request->categories ?? []);

        return redirect()
            ->route('site.member.posts.index', ['subdomain' => $subdomain])
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Delete post
     */
    public function destroy(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $post = $this->getPost($site->id, $user, $id);
        $post->delete();

        return redirect()
            ->route('site.member.posts.index', ['subdomain' => $subdomain])
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Get post with authorization check
     */
    protected function getPost(int $siteId, $user, int $id): SitePost
    {
        $query = SitePost::forSite($siteId)->where('id', $id);

        // Non-admins can only edit their own posts
        if ($user->role?->level < 100) {
            $query->where('author_id', $user->id);
        }

        return $query->firstOrFail();
    }

    protected function getSiteData($site): array
    {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->subdomain,
            'logo' => $site->logo,
            'theme' => $site->theme,
        ];
    }

    protected function getUserData($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ? [
                'name' => $user->role->name,
                'slug' => $user->role->slug,
            ] : null,
            'permissions' => $user->role 
                ? $user->role->permissions->pluck('slug')->toArray() 
                : [],
        ];
    }
}
