<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeriesManagementController extends Controller
{
    public function __construct(
        private VideoSeriesRepositoryInterface $seriesRepo,
        private VideoRepositoryInterface $videoRepo
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->seriesRepo->query();
        $query->with(['creator', 'categories'])
            ->withCount(['videos', 'publishedVideos']);

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content_type' => 'required|in:series,course,workshop_series',
            'access_level' => 'required|in:free,basic,premium,institutional',
            'total_seasons' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $series = $this->seriesRepo->save([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'long_description' => $request->long_description,
                'content_type' => $request->content_type,
                'access_level' => $request->access_level,
                'total_seasons' => $request->get('total_seasons', 1),
                'creator_id' => Auth::id(),
                'poster_url' => $request->poster_url,
                'trailer_url' => $request->trailer_url,
                'release_year' => $request->release_year,
                'language' => $request->get('language', 'en'),
                'content_rating' => $request->get('content_rating', 'NR'),
            ]);

            if ($request->has('category_ids')) {
                $series->categories()->attach($request->category_ids);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $series->load('categories'),
                'message' => 'Series created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Creation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $series = $this->seriesRepo->findById($id);
        if (!$series) {
            return response()->json(['success' => false, 'error' => 'Series not found'], 404);
        }

        $series->load([
            'creator',
            'categories',
            'videos' => function ($query) {
                $query->orderBy('season_number')
                      ->orderBy('episode_number');
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $series,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $series = $this->seriesRepo->findById($id);
        if (!$series) {
            return response()->json(['success' => false, 'error' => 'Series not found'], 404);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'content_type' => 'sometimes|in:series,course,workshop_series',
            'access_level' => 'sometimes|in:free,basic,premium,institutional',
        ]);

        try {
            DB::beginTransaction();

            $this->seriesRepo->update($series, $request->only([
                'title',
                'slug',
                'description',
                'long_description',
                'content_type',
                'access_level',
                'total_seasons',
                'poster_url',
                'trailer_url',
                'release_year',
                'language',
                'content_rating',
                'is_featured',
            ]));

            if ($request->has('category_ids')) {
                $series->categories()->sync($request->category_ids);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $series->fresh('categories'),
                'message' => 'Series updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function publish(int $id): JsonResponse
    {
        $series = $this->seriesRepo->findById($id);
        if (!$series) {
            return response()->json(['success' => false, 'error' => 'Series not found'], 404);
        }

        $this->seriesRepo->update($series, [
            'is_published' => true,
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $series->fresh(),
            'message' => 'Series published successfully',
        ]);
    }

    public function unpublish(int $id): JsonResponse
    {
        $series = $this->seriesRepo->findById($id);
        if (!$series) {
            return response()->json(['success' => false, 'error' => 'Series not found'], 404);
        }

        $this->seriesRepo->update($series, [
            'is_published' => false,
        ]);

        return response()->json([
            'success' => true,
            'data' => $series->fresh(),
            'message' => 'Series unpublished successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $series = $this->seriesRepo->findById($id);
        if (!$series) {
            return response()->json(['success' => false, 'error' => 'Series not found'], 404);
        }

        try {
            if ($series->videos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete series with videos. Remove all videos first.',
                ], 400);
            }

            $this->seriesRepo->delete($series);

            return response()->json([
                'success' => true,
                'message' => 'Series deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reorderEpisodes(Request $request, int $id): JsonResponse
    {
        $series = $this->seriesRepo->findById($id);
        if (!$series) {
            return response()->json(['success' => false, 'error' => 'Series not found'], 404);
        }

        $request->validate([
            'episodes' => 'required|array',
            'episodes.*.video_id' => 'required|exists:growstream_videos,id',
            'episodes.*.season_number' => 'required|integer|min:1',
            'episodes.*.episode_number' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->episodes as $episode) {
                $this->videoRepo->query()
                    ->where('id', $episode['video_id'])
                    ->where('series_id', $series->id)
                    ->update([
                        'season_number' => $episode['season_number'],
                        'episode_number' => $episode['episode_number'],
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Episodes reordered successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Reorder failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
