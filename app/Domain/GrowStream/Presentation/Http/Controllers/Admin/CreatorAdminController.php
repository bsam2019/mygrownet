<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CreatorAdminController extends Controller
{
    /**
     * Display a listing of creators
     */
    public function index(Request $request)
    {
        $query = CreatorProfile::with('user');

        // Search
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $creators = $query->paginate(20)->through(function ($creator) {
            $videoCount = Video::where('creator_id', $creator->id)->count();
            $publishedVideoCount = Video::where('creator_id', $creator->id)
                ->where('is_published', true)
                ->count();
            $totalViews = Video::where('creator_id', $creator->id)->sum('view_count');

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

    /**
     * Display the specified creator
     */
    public function show(CreatorProfile $creator)
    {
        $creator->load('user');

        // Get creator's videos
        $videos = Video::where('creator_id', $creator->id)
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

        // Get statistics
        $stats = [
            'total_videos' => Video::where('creator_id', $creator->id)->count(),
            'published_videos' => Video::where('creator_id', $creator->id)
                ->where('is_published', true)
                ->count(),
            'total_views' => Video::where('creator_id', $creator->id)->sum('view_count'),
            'total_likes' => Video::where('creator_id', $creator->id)->sum('like_count'),
            'avg_views_per_video' => Video::where('creator_id', $creator->id)
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

    /**
     * Approve a creator application
     */
    public function approve(CreatorProfile $creator)
    {
        $creator->status = 'approved';
        $creator->save();

        // TODO: Send approval notification to creator
        // Notification::send($creator->user, new CreatorApprovedNotification());

        return back()->with('success', 'Creator approved successfully.');
    }

    /**
     * Suspend a creator account
     */
    public function suspend(CreatorProfile $creator)
    {
        $creator->status = 'suspended';
        $creator->save();

        // Unpublish all creator's videos
        Video::where('creator_id', $creator->id)
            ->update(['is_published' => false]);

        // TODO: Send suspension notification to creator
        // Notification::send($creator->user, new CreatorSuspendedNotification());

        return back()->with('success', 'Creator suspended successfully.');
    }

    /**
     * Activate a creator account
     */
    public function activate(CreatorProfile $creator)
    {
        $creator->status = 'approved';
        $creator->save();

        // TODO: Send activation notification to creator
        // Notification::send($creator->user, new CreatorActivatedNotification());

        return back()->with('success', 'Creator activated successfully.');
    }
}
