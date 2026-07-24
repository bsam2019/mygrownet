<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Infrastructure\Events\VideoUploaded;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoTag;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoManagementController extends Controller
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->videoRepo->query();
        $query->with(['creator', 'categories', 'series']);

        if ($request->has('status')) {
            $query->where('upload_status', $request->status);
        }

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

            $video = $this->videoRepo->save([
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

            $provider = VideoProviderFactory::make();
            $response = $provider->upload($request->file('video'), [
                'title' => $request->title,
                'video_id' => $video->id,
            ]);

            $this->videoRepo->update($video, [
                'provider_video_id' => $response->providerVideoId,
                'playback_url' => $response->playbackUrl,
                'thumbnail_url' => $response->thumbnailUrl,
                'duration' => $response->duration,
                'file_size' => $response->fileSize,
                'upload_status' => $response->status,
                'processing_completed_at' => now(),
            ]);

            DB::commit();

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

    public function show(int $id): JsonResponse
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return response()->json(['success' => false, 'error' => 'Video not found'], 404);
        }

        $video->load(['creator', 'categories', 'tags', 'series']);

        return response()->json([
            'success' => true,
            'data' => $video,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return response()->json(['success' => false, 'error' => 'Video not found'], 404);
        }

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

            if ($request->has('is_featured')) {
                if ($request->is_featured && !$video->is_featured) {
                    $updateData['featured_at'] = now();
                } elseif (!$request->is_featured && $video->is_featured) {
                    $updateData['featured_at'] = null;
                }
            }

            $this->videoRepo->update($video, $updateData);

            if ($request->has('category_ids')) {
                $video->categories()->sync($request->category_ids);
            }

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

    public function publish(int $id): JsonResponse
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return response()->json(['success' => false, 'error' => 'Video not found'], 404);
        }

        if (!$video->isReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Video is not ready for publishing',
            ], 400);
        }

        $this->videoRepo->update($video, [
            'is_published' => true,
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $video->fresh(),
            'message' => 'Video published successfully',
        ]);
    }

    public function unpublish(int $id): JsonResponse
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return response()->json(['success' => false, 'error' => 'Video not found'], 404);
        }

        $this->videoRepo->update($video, [
            'is_published' => false,
        ]);

        return response()->json([
            'success' => true,
            'data' => $video->fresh(),
            'message' => 'Video unpublished successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return response()->json(['success' => false, 'error' => 'Video not found'], 404);
        }

        try {
            if ($video->provider_video_id) {
                $provider = VideoProviderFactory::make($video->video_provider);
                $provider->delete($video->provider_video_id);
            }

            $this->videoRepo->delete($video);

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

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete,feature,unfeature',
            'video_ids' => 'required|array',
            'video_ids.*' => 'exists:growstream_videos,id',
        ]);

        $videos = $this->videoRepo->query()->whereIn('id', $request->video_ids)->get();

        foreach ($videos as $video) {
            match ($request->action) {
                'publish' => $this->videoRepo->update($video, ['is_published' => true, 'published_at' => now()]),
                'unpublish' => $this->videoRepo->update($video, ['is_published' => false]),
                'delete' => $this->videoRepo->delete($video),
                'feature' => $this->videoRepo->update($video, ['is_featured' => true, 'featured_at' => now()]),
                'unfeature' => $this->videoRepo->update($video, ['is_featured' => false, 'featured_at' => null]),
            };
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->action) . ' action completed for ' . count($videos) . ' videos',
        ]);
    }

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

    protected function syncTags($video, array $tags): void
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
