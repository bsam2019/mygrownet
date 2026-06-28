<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeriesManagementController extends Controller
{
    /**
     * Get all series for admin
     */
    public function index(Request $request): JsonResponse
    {
        $query = VideoSeries::with(['creator', 'categories'])
            ->withCount(['videos', 'publishedVideos']);

        // Filter by published
        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
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

    /**
     * Create new series
     */
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

            $series = VideoSeries::create([
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

            // Attach categories
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

    /**
     * Get series details with episodes
     */
    public function show(VideoSeries $series): JsonResponse
    {
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

    /**
     * Update series
     */
    public function update(Request $request, VideoSeries $series): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'content_type' => 'sometimes|in:series,course,workshop_series',
            'access_level' => 'sometimes|in:free,basic,premium,institutional',
        ]);

        try {
            DB::beginTransaction();

            $series->update($request->only([
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

            // Update categories
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

    /**
     * Publish series
     */
    public function publish(VideoSeries $series): JsonResponse
    {
        $series->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $series,
            'message' => 'Series published successfully',
        ]);
    }

    /**
     * Unpublish series
     */
    public function unpublish(VideoSeries $series): JsonResponse
    {
        $series->update([
            'is_published' => false,
        ]);

        return response()->json([
            'success' => true,
            'data' => $series,
            'message' => 'Series unpublished successfully',
        ]);
    }

    /**
     * Delete series
     */
    public function destroy(VideoSeries $series): JsonResponse
    {
        try {
            // Check if series has videos
            if ($series->videos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete series with videos. Remove all videos first.',
                ], 400);
            }

            $series->delete();

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

    /**
     * Reorder episodes in a series
     */
    public function reorderEpisodes(Request $request, VideoSeries $series): JsonResponse
    {
        $request->validate([
            'episodes' => 'required|array',
            'episodes.*.video_id' => 'required|exists:growstream_videos,id',
            'episodes.*.season_number' => 'required|integer|min:1',
            'episodes.*.episode_number' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->episodes as $episode) {
                Video::where('id', $episode['video_id'])
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
