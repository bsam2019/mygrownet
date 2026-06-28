<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    /**
     * Get list of series
     */
    public function index(Request $request): JsonResponse
    {
        $query = VideoSeries::with(['creator'])
            ->published();

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
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

        $series = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $series->items(),
            'meta' => [
                'current_page' => $series->currentPage(),
                'per_page' => $series->perPage(),
                'total' => $series->total(),
                'last_page' => $series->lastPage(),
            ],
        ]);
    }

    /**
     * Get single series details with episodes
     */
    public function show(VideoSeries $series): JsonResponse
    {
        $series->load(['creator', 'videos' => function ($query) {
            $query->published()->ready();
        }]);

        // Group episodes by season
        $seasons = $series->getSeasons();
        $episodesBySeason = [];
        
        foreach ($seasons as $seasonNumber) {
            $episodesBySeason[$seasonNumber] = $series->getEpisodesBySeason($seasonNumber);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'series' => $series,
                'seasons' => $seasons,
                'episodes_by_season' => $episodesBySeason,
            ],
        ]);
    }
}
