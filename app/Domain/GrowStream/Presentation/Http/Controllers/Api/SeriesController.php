<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    public function __construct(
        private VideoSeriesRepositoryInterface $seriesRepo
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->seriesRepo->query();
        $query->with(['creator'])->published();

        if ($request->has('type')) {
            $query->byType($request->type);
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

    public function show(string $slug): JsonResponse
    {
        $series = $this->seriesRepo->findBySlug($slug);

        if (!$series) {
            return response()->json(['success' => false, 'error' => 'Series not found'], 404);
        }

        $series->load(['creator', 'videos' => function ($query) {
            $query->published()->ready();
        }]);

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
