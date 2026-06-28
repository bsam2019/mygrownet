<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\CommunityService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommunityController extends Controller
{
    public function __construct(protected CommunityService $communityService) {}

    public function index(Request $request)
    {
        $type = $request->get('type');
        $filters = $request->only(['type', 'location']);

        return Inertia::render('LifePlus/Community/Index', [
            'posts' => $this->communityService->getPosts($filters),
            'filters' => $filters,
        ]);
    }

    public function notices()
    {
        return Inertia::render('LifePlus/Community/Notices', [
            'notices' => $this->communityService->getNotices(),
        ]);
    }

    public function events()
    {
        return Inertia::render('LifePlus/Community/Events', [
            'events' => $this->communityService->getEvents(),
        ]);
    }

    public function lostFound()
    {
        return Inertia::render('LifePlus/Community/LostFound', [
            'posts' => $this->communityService->getLostFound(),
        ]);
    }

    public function show(int $id)
    {
        $post = $this->communityService->getPost($id);

        if (!$post) {
            return redirect()->route('lifeplus.community.index')
                ->with('error', 'Post not found');
        }

        return Inertia::render('LifePlus/Community/Show', [
            'post' => $post,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:notice,event,lost_found',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'event_date' => 'nullable|date|after:now',
            'image_url' => 'nullable|url',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $post = $this->communityService->createPost(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($post, 201);
        }

        return redirect()->route('lifeplus.community.index')
            ->with('success', 'Post created');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
        ]);

        $post = $this->communityService->updatePost($id, auth()->id(), $validated);

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($post);
        }

        return back()->with('success', 'Post updated');
    }

    public function destroy(int $id)
    {
        $deleted = $this->communityService->deletePost($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        return redirect()->route('lifeplus.community.index')
            ->with('success', 'Post deleted');
    }
}
