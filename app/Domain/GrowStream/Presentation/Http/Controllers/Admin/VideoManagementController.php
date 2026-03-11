<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Infrastructure\Events\VideoUploaded;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoTag;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoManagementController extends Controller
{
    /**
     * Get all videos for admin
     */
    public function index(Request $request): JsonResponse
    {
        $query = Video::with(['creator', 'categories', 'series']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('upload_status', $request->status);
        }

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
        ]);
    }

    /**
     * Upload video
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,mkv,webm|max:' . (config('growstream.upload.max_file_size') / 1024),
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content_type' => 'required|in:movie,series,episode,lesson,short,workshop,webinar',
            'access_level' => 'required|in:free,basic,premium,institutional',
        ]);

        try {
            DB::beginTransaction();

            // Create video record
            $video = Video::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'long_description' => $request->long_description,
                'content_type' => $request->content_type,
                'access_level' => $request->access_level,
                'language' => $request->get('language', 'en'),
                'content_rating' => $request->get('content_rating', 'NR'),
                'skill_level' => $request->skill_level,
                'creator_id' => Auth::id(),
                'video_provider' => config('growstream.default_provider'),
                'upload_status' => 'uploading',
            ]);

            // Upload to provider
            $provider = VideoProviderFactory::make();
            $response = $provider->upload($request->file('video'), [
                'title' => $request->title,
                'video_id' => $video->id,
            ]);

            // Update video with provider details
            $video->update([
                'provider_video_id' => $response->providerVideoId,
                'playback_url' => $response->playbackUrl,
                'thumbnail_url' => $response->thumbnailUrl,
                'duration' => $response->duration,
                'file_size' => $response->fileSize,
                'upload_status' => $response->status,
                'processing_completed_at' => now(),
            ]);

            DB::commit();

            // Dispatch event for async processing
            event(new VideoUploaded($video));

            return response()->json([
                'success' => true,
                'data' => $video,
                'message' => 'Video uploaded successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get video details for editing
     */
    public function show(Video $video): JsonResponse
    {
        $video->load(['creator', 'categories', 'tags', 'series']);

        return response()->json([
            'success' => true,
            'data' => $video,
        ]);
    }

    /**
     * Update video
     */
    public function update(Request $request, Video $video): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'content_type' => 'sometimes|in:movie,series,episode,lesson,short,workshop,webinar',
            'access_level' => 'sometimes|in:free,basic,premium,institutional',
        ]);

        try {
            DB::beginTransaction();

            $updateData = $request->only([
                'title',
                'slug',
                'description',
                'long_description',
                'content_type',
                'access_level',
                'language',
                'content_rating',
                'skill_level',
                'is_featured',
                'is_downloadable',
                'series_id',
                'season_number',
                'episode_number',
                'meta_title',
                'meta_description',
            ]);

            // Handle featured_at timestamp
            if ($request->has('is_featured')) {
                if ($request->is_featured && !$video->is_featured) {
                    // Being featured for the first time or re-featured
                    $updateData['featured_at'] = now();
                } elseif (!$request->is_featured && $video->is_featured) {
                    // Being unfeatured
                    $updateData['featured_at'] = null;
                }
            }

            $video->update($updateData);

            // Update categories
            if ($request->has('category_ids')) {
                $video->categories()->sync($request->category_ids);
            }

            // Update tags
            if ($request->has('tags')) {
                $this->syncTags($video, $request->tags);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $video->fresh(['categories', 'tags']),
                'message' => 'Video updated successfully',
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
     * Publish video
     */
    public function publish(Video $video): JsonResponse
    {
        if (!$video->isReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Video is not ready for publishing',
            ], 400);
        }

        $video->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $video,
            'message' => 'Video published successfully',
        ]);
    }

    /**
     * Unpublish video
     */
    public function unpublish(Video $video): JsonResponse
    {
        $video->update([
            'is_published' => false,
        ]);

        return response()->json([
            'success' => true,
            'data' => $video,
            'message' => 'Video unpublished successfully',
        ]);
    }

    /**
     * Delete video
     */
    public function destroy(Video $video): JsonResponse
    {
        try {
            // Delete from provider
            if ($video->provider_video_id) {
                $provider = VideoProviderFactory::make($video->video_provider);
                $provider->delete($video->provider_video_id);
            }

            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete,feature,unfeature',
            'video_ids' => 'required|array',
            'video_ids.*' => 'exists:growstream_videos,id',
        ]);

        $videos = Video::whereIn('id', $request->video_ids)->get();

        foreach ($videos as $video) {
            match ($request->action) {
                'publish' => $video->update(['is_published' => true, 'published_at' => now()]),
                'unpublish' => $video->update(['is_published' => false]),
                'delete' => $video->delete(),
                'feature' => $video->update(['is_featured' => true, 'featured_at' => now()]),
                'unfeature' => $video->update(['is_featured' => false, 'featured_at' => null]),
            };
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->action) . ' action completed for ' . count($videos) . ' videos',
        ]);
    }

    /**
     * Get form data (categories, series, etc.)
     */
    public function formData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => VideoCategory::active()->get(),
                'series' => VideoSeries::published()->get(),
                'content_types' => config('growstream.content_types'),
                'access_levels' => config('growstream.access_levels'),
                'content_ratings' => ['G', 'PG', 'PG-13', 'R', 'NR'],
                'skill_levels' => ['beginner', 'intermediate', 'advanced', 'expert'],
            ],
        ]);
    }

    /**
     * Sync tags for video
     */
    protected function syncTags(Video $video, array $tags): void
    {
        $tagIds = [];

        foreach ($tags as $tagName) {
            $tag = VideoTag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );
            $tagIds[] = $tag->id;
        }

        $video->tags()->sync($tagIds);
    }
}
