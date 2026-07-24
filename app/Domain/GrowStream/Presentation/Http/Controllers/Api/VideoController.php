<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->videoRepo->query();
        $query->with(['creator', 'categories', 'series'])
            ->published()
            ->ready();

        if ($request->has('type')) {
            $query->byContentType($request->type);
        }

        if ($request->has('access_level')) {
            $query->where('access_level', $request->access_level);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

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

    public function featured(): JsonResponse
    {
        $videos = $this->videoRepo->featured(10, ['creator', 'categories']);

        return response()->json([
            'success' => true,
            'data' => $videos,
        ]);
    }

    public function trending(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $videos = $this->videoRepo->trending($days, 20, ['creator', 'categories']);

        return response()->json([
            'success' => true,
            'data' => $videos,
        ]);
    }

    public function show($slug): JsonResponse
    {
        $video = $this->videoRepo->findBySlug($slug);

        if (!$video || !$video->isPublished() || !$video->isReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Video not available',
            ], 404);
        }

        $video->load(['creator', 'categories', 'tags', 'series']);

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
