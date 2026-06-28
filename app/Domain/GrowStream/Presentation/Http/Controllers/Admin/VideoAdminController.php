<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VideoAdminController extends Controller
{
    /**
     * Display a listing of videos
     */
    public function index(Request $request)
    {
        $query = Video::with(['creator.user', 'categories']);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('upload_status', $request->status);
        }

        // Filter by published
        if ($request->has('is_published')) {
            $query->where('is_published', $request->is_published === 'true');
        }

        // Filter by creator
        if ($request->creator_id) {
            $query->where('creator_id', $request->creator_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $videos = $query->paginate(20)->through(fn($video) => [
            'id' => $video->id,
            'title' => $video->title,
            'slug' => $video->slug,
            'description' => $video->description,
            'thumbnail_url' => $video->thumbnail_url,
            'duration' => $video->duration,
            'upload_status' => $video->upload_status,
            'is_published' => $video->is_published,
            'published_at' => $video->published_at?->format('Y-m-d H:i'),
            'view_count' => $video->view_count,
            'like_count' => $video->like_count ?? 0,
            'creator' => [
                'id' => $video->creator->id,
                'name' => $video->creator->user->name,
                'avatar' => $video->creator->user->profile_photo_url ?? null,
            ],
            'categories' => $video->categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
            ]),
            'is_featured' => $video->is_featured ?? false,
            'is_starter_kit_content' => $video->is_starter_kit_content ?? false,
            'created_at' => $video->created_at->format('Y-m-d H:i'),
        ]);

        // Get creators for filter dropdown
        $creators = CreatorProfile::with('user')
            ->get()
            ->map(fn($creator) => [
                'id' => $creator->id,
                'name' => $creator->user->name,
            ]);

        // Get categories for filter
        $categories = VideoCategory::orderBy('name')->get();

        return Inertia::render('GrowStream/Admin/Videos', [
            'videos' => $videos,
            'creators' => $creators,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'is_published', 'creator_id']),
        ]);
    }

    /**
     * Store a newly created video
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'creator_id' => 'required|exists:growstream_creator_profiles,id',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:512000', // 500MB max
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|max:5120', // 5MB max
            'categories' => 'nullable|array',
            'categories.*' => 'exists:growstream_categories,id',
            'is_published' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $video = new Video();
            $video->title = $request->title;
            $video->slug = Str::slug($request->title) . '-' . Str::random(8);
            $video->description = $request->description;
            $video->creator_id = $request->creator_id;
            $video->upload_status = 'uploading';
            $video->is_published = $request->is_published ?? false;

            // Handle video upload
            if ($request->hasFile('video_file')) {
                $path = $request->file('video_file')->store('growstream/videos', 'public');
                $video->video_url = Storage::url($path);
                $video->upload_status = 'processing';
                
                // TODO: Queue video processing job
                // ProcessVideoJob::dispatch($video);
            } elseif ($request->video_url) {
                $video->video_url = $request->video_url;
                $video->upload_status = 'ready';
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('growstream/thumbnails', 'public');
                $video->thumbnail_url = Storage::url($path);
            }

            $video->save();

            // Attach categories
            if ($request->categories) {
                $video->categories()->attach($request->categories);
            }

            // Publish if requested
            if ($request->is_published) {
                $video->published_at = now();
                $video->save();
            }

            DB::commit();

            return back()->with('success', 'Video uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to upload video: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified video
     */
    public function show(Video $video)
    {
        $video->load(['creator.user', 'categories']);

        return Inertia::render('GrowStream/Admin/VideoDetail', [
            'video' => [
                'id' => $video->id,
                'title' => $video->title,
                'slug' => $video->slug,
                'description' => $video->description,
                'video_url' => $video->video_url,
                'thumbnail_url' => $video->thumbnail_url,
                'duration' => $video->duration,
                'upload_status' => $video->upload_status,
                'is_published' => $video->is_published,
                'published_at' => $video->published_at?->format('Y-m-d H:i'),
                'view_count' => $video->view_count,
                'like_count' => $video->like_count ?? 0,
                'creator' => [
                    'id' => $video->creator->id,
                    'name' => $video->creator->user->name,
                    'avatar' => $video->creator->user->profile_photo_url ?? null,
                ],
                'categories' => $video->categories,
                'is_featured' => $video->is_featured ?? false,
                'is_starter_kit_content' => $video->is_starter_kit_content ?? false,
                'starter_kit_points_reward' => $video->starter_kit_points_reward ?? 0,
                'created_at' => $video->created_at->format('Y-m-d H:i'),
                'updated_at' => $video->updated_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Update the specified video
     */
    public function update(Request $request, Video $video)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:5120',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:growstream_categories,id',
        ]);

        DB::beginTransaction();
        try {
            $video->title = $request->title;
            $video->description = $request->description;

            // Update slug if title changed significantly
            if ($video->isDirty('title')) {
                $video->slug = Str::slug($request->title) . '-' . Str::random(8);
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail
                if ($video->thumbnail_url) {
                    $oldPath = str_replace('/storage/', '', $video->thumbnail_url);
                    Storage::disk('public')->delete($oldPath);
                }
                
                $path = $request->file('thumbnail')->store('growstream/thumbnails', 'public');
                $video->thumbnail_url = Storage::url($path);
            }

            $video->save();

            // Sync categories
            if ($request->has('categories')) {
                $video->categories()->sync($request->categories);
            }

            DB::commit();

            return back()->with('success', 'Video updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update video: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified video
     */
    public function destroy(Video $video)
    {
        DB::beginTransaction();
        try {
            // Delete video file
            if ($video->video_url && Str::startsWith($video->video_url, '/storage/')) {
                $path = str_replace('/storage/', '', $video->video_url);
                Storage::disk('public')->delete($path);
            }

            // Delete thumbnail
            if ($video->thumbnail_url && Str::startsWith($video->thumbnail_url, '/storage/')) {
                $path = str_replace('/storage/', '', $video->thumbnail_url);
                Storage::disk('public')->delete($path);
            }

            // Delete video record
            $video->delete();

            DB::commit();

            return back()->with('success', 'Video deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete video: ' . $e->getMessage());
        }
    }

    /**
     * Publish a video
     */
    public function publish(Video $video)
    {
        $video->is_published = true;
        $video->published_at = now();
        $video->save();

        return back()->with('success', 'Video published successfully.');
    }

    /**
     * Unpublish a video
     */
    public function unpublish(Video $video)
    {
        $video->is_published = false;
        $video->save();

        return back()->with('success', 'Video unpublished successfully.');
    }

    /**
     * Perform bulk actions on videos
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete',
            'video_ids' => 'required|array',
            'video_ids.*' => 'exists:growstream_videos,id',
        ]);

        $videos = Video::whereIn('id', $request->video_ids)->get();

        DB::beginTransaction();
        try {
            foreach ($videos as $video) {
                switch ($request->action) {
                    case 'publish':
                        $video->is_published = true;
                        $video->published_at = $video->published_at ?? now();
                        $video->save();
                        break;
                    case 'unpublish':
                        $video->is_published = false;
                        $video->save();
                        break;
                    case 'delete':
                        $video->delete();
                        break;
                }
            }

            DB::commit();

            return back()->with('success', "Bulk action '{$request->action}' completed successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }
}
