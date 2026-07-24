<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreatorAdminController extends Controller
{
    public function __construct(
        private CreatorProfileRepositoryInterface $creatorRepo,
        private VideoRepositoryInterface $videoRepo
    ) {}

    public function index(Request $request)
    {
        $query = $this->creatorRepo->query()->with('user');

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $creators = $query->paginate(20)->through(function ($creator) {
            $query = $this->videoRepo->query();
            $videoCount = (clone $query)->where('creator_id', $creator->id)->count();
            $publishedVideoCount = (clone $query)->where('creator_id', $creator->id)
                ->where('is_published', true)
                ->count();
            $totalViews = (clone $query)->where('creator_id', $creator->id)->sum('view_count');

            return [
                'id' => $creator->id,
                'user' => [
                    'id' => $creator->user->id,
                    'name' => $creator->user->name,
                    'email' => $creator->user->email,
                    'avatar' => $creator->user->profile_photo_url ?? null,
                ],
                'channel_name' => $creator->channel_name,
                'bio' => $creator->bio,
                'status' => $creator->status,
                'is_verified' => $creator->is_verified ?? false,
                'subscriber_count' => $creator->subscriber_count ?? 0,
                'video_count' => $videoCount,
                'published_video_count' => $publishedVideoCount,
                'total_views' => $totalViews,
                'created_at' => $creator->created_at->format('Y-m-d H:i'),
            ];
        });

        return Inertia::render('GrowStream/Admin/Creators', [
            'creators' => $creators,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(int $id)
    {
        $creator = $this->creatorRepo->findById($id);
        if (!$creator) {
            return back()->with('error', 'Creator not found.');
        }

        $creator->load('user');

        $query = $this->videoRepo->query();
        $videos = (clone $query)->where('creator_id', $creator->id)
            ->with('categories')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($video) => [
                'id' => $video->id,
                'title' => $video->title,
                'thumbnail_url' => $video->thumbnail_url,
                'view_count' => $video->view_count,
                'is_published' => $video->is_published,
                'created_at' => $video->created_at->format('Y-m-d H:i'),
            ]);

        $stats = [
            'total_videos' => (clone $query)->where('creator_id', $creator->id)->count(),
            'published_videos' => (clone $query)->where('creator_id', $creator->id)
                ->where('is_published', true)
                ->count(),
            'total_views' => (clone $query)->where('creator_id', $creator->id)->sum('view_count'),
            'total_likes' => (clone $query)->where('creator_id', $creator->id)->sum('like_count'),
            'avg_views_per_video' => (clone $query)->where('creator_id', $creator->id)
                ->avg('view_count'),
        ];

        return Inertia::render('GrowStream/Admin/CreatorDetail', [
            'creator' => [
                'id' => $creator->id,
                'user' => [
                    'id' => $creator->user->id,
                    'name' => $creator->user->name,
                    'email' => $creator->user->email,
                    'avatar' => $creator->user->profile_photo_url ?? null,
                ],
                'channel_name' => $creator->channel_name,
                'bio' => $creator->bio,
                'status' => $creator->status,
                'is_verified' => $creator->is_verified ?? false,
                'subscriber_count' => $creator->subscriber_count ?? 0,
                'created_at' => $creator->created_at->format('Y-m-d H:i'),
            ],
            'videos' => $videos,
            'stats' => $stats,
        ]);
    }

    public function approve(int $id)
    {
        $creator = $this->creatorRepo->findById($id);
        if (!$creator) {
            return back()->with('error', 'Creator not found.');
        }

        $this->creatorRepo->update($creator, ['status' => 'approved']);

        return back()->with('success', 'Creator approved successfully.');
    }

    public function suspend(int $id)
    {
        $creator = $this->creatorRepo->findById($id);
        if (!$creator) {
            return back()->with('error', 'Creator not found.');
        }

        $this->creatorRepo->update($creator, ['status' => 'suspended']);

        $this->videoRepo->query()
            ->where('creator_id', $creator->id)
            ->update(['is_published' => false]);

        return back()->with('success', 'Creator suspended successfully.');
    }

    public function activate(int $id)
    {
        $creator = $this->creatorRepo->findById($id);
        if (!$creator) {
            return back()->with('error', 'Creator not found.');
        }

        $this->creatorRepo->update($creator, ['status' => 'approved']);

        return back()->with('success', 'Creator activated successfully.');
    }
}
