<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SitePost;
use Illuminate\Http\Request;

class SiteBlogController extends Controller
{
    /**
     * Display blog listing page
     */
    public function index(Request $request, string $subdomain)
    {
        $site = GrowBuilderSite::where('subdomain', $subdomain)
            ->where('status', 'published')
            ->firstOrFail();

        $posts = SitePost::forSite($site->id)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->latest('published_at')
            ->paginate(12);

        return view('growbuilder.blog.index', [
            'site' => $site,
            'posts' => $posts,
            'subdomain' => $subdomain,
        ]);
    }

    /**
     * Display single blog post
     */
    public function show(Request $request, string $subdomain, string $slug)
    {
        $site = GrowBuilderSite::where('subdomain', $subdomain)
            ->where('status', 'published')
            ->firstOrFail();

        $post = SitePost::forSite($site->id)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->firstOrFail();

        // Increment view count
        $post->increment('views_count');

        // Get related posts
        $relatedPosts = SitePost::forSite($site->id)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('growbuilder.blog.show', [
            'site' => $site,
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'subdomain' => $subdomain,
        ]);
    }
}
