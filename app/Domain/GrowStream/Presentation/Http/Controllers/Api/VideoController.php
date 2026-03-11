<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    /**
     * Get list of videos
     */
    public function index(Request $request): JsonResponse
    {
        $query = Video::with(['creator', 'categories', 'series'])
            ->published()
            ->ready();

        // Filter by content type
        if ($request->has('type')) {
            $query->byContentType($request->type);
        }

        // Filter by access level
        if ($request->has('access_level')) {
            $query->where('access_level', $request->access_level);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $videos = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $videos->items(),
            'meta' => [
                'current_page' => $videos->currentPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
                'last_page' => $videos->lastPage(),
            ],
            'links' => [
                'first' => $videos->url(1),
                'last' => $videos->url($videos->lastPage()),
                'prev' => $videos->previousPageUrl(),
                'next' => $videos->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Get featured videos
     */
    public function featured(): JsonResponse
    {
        $videos = Video::with(['creator', 'categories'])
            ->published()
            ->ready()
            ->featured()
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $videos,
        ]);
    }

    /**
     * Get trending videos
     */
    public function trending(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        
        $videos = Video::with(['creator', 'categories'])
            ->published()
            ->ready()
            ->trending($days)
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $videos,
        ]);
    }

    /**
     * Get single video details
     */
    public function show(Video $video): JsonResponse
    {
        if (!$video->isPublished() || !$video->isReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Video not available',
            ], 404);
        }

        $video->load(['creator', 'categories', 'tags', 'series']);

        // Get next and previous episodes if part of series
        $nextEpisode = $video->getNextEpisode();
        $previousEpisode = $video->getPreviousEpisode();

        return response()->json([
            'success' => true,
            'data' => [
                'video' => $video,
                'next_episode' => $nextEpisode,
                'previous_episode' => $previousEpisode,
            ],
        ]);
    }
}
