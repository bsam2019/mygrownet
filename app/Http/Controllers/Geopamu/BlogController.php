<?php

namespace App\Http\Controllers\Geopamu;

use App\Http\Controllers\Controller;
use App\Models\GeopamuBlogPost;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $posts = GeopamuBlogPost::with('author')
            ->published()
            ->recent()
            ->paginate(9);

        $categories = GeopamuBlogPost::published()
            ->select('category')
            ->distinct()
            ->pluck('category');

        return Inertia::render('Geopamu/Blog/Index', [
            'posts' => $posts,
            'categories' => $categories
        ]);
    }

    public function show(string $slug): Response
    {
        $post = GeopamuBlogPost::with('author')
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $post->incrementViews();

        $relatedPosts = GeopamuBlogPost::published()
            ->where('category', $post->category)
            ->where('id', '!=', $post->id)
            ->recent()
            ->limit(3)
            ->get();

        return Inertia::render('Geopamu/Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ]);
    }

    public function category(string $category): Response
    {
        $posts = GeopamuBlogPost::with('author')
            ->published()
            ->where('category', $category)
            ->recent()
            ->paginate(9);

        return Inertia::render('Geopamu/Blog/Index', [
            'posts' => $posts,
            'selectedCategory' => $category
        ]);
    }
}
