<?php

namespace App\Http\Controllers\Geopamu\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeopamuBlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): Response
    {
        $posts = GeopamuBlogPost::with('author')
            ->latest()
            ->paginate(15);

        return Inertia::render('Geopamu/Admin/Blog/Index', [
            'posts' => $posts
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Geopamu/Admin/Blog/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'category' => 'required|string',
            'tags' => 'nullable|array',
            'status' => 'required|in:draft,published'
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();
        
        if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = GeopamuBlogPost::create($validated);

        return redirect()->route('geopamu.admin.blog.edit', $post->id)
            ->with('success', 'Blog post created successfully!');
    }

    public function edit(GeopamuBlogPost $post): Response
    {
        return Inertia::render('Geopamu/Admin/Blog/Edit', [
            'post' => $post
        ]);
    }

    public function update(Request $request, GeopamuBlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'category' => 'required|string',
            'tags' => 'nullable|array',
            'status' => 'required|in:draft,published'
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        
        if ($validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        return back()->with('success', 'Blog post updated successfully!');
    }

    public function destroy(GeopamuBlogPost $post)
    {
        $post->delete();

        return redirect()->route('geopamu.admin.blog.index')
            ->with('success', 'Blog post deleted successfully!');
    }
}
