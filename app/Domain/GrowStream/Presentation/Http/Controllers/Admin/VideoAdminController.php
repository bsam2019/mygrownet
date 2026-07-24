<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VideoAdminController extends Controller
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo
    ) {}

    public function index(Request $request)
    {
        $query = $this->videoRepo->query()->with(['creator.user', 'categories']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('upload_status', $request->status);
        }

        if ($request->has('is_published')) {
            $query->where('is_published', $request->is_published === 'true');
        }

        if ($request->creator_id) {
            $query->where('creator_id', $request->creator_id);
        }

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

        $creators = CreatorProfile::with('user')
            ->get()
            ->map(fn($creator) => [
                'id' => $creator->id,
                'name' => $creator->user->name,
            ]);

        $categories = VideoCategory::orderBy('name')->get();

        return Inertia::render('GrowStream/Admin/Videos', [
            'videos' => $videos,
            'creators' => $creators,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'is_published', 'creator_id']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'creator_id' => 'required|exists:growstream_creator_profiles,id',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:512000',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|max:5120',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:growstream_categories,id',
            'is_published' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . Str::random(8),
                'description' => $request->description,
                'creator_id' => $request->creator_id,
                'upload_status' => 'uploading',
                'is_published' => $request->is_published ?? false,
            ];

            if ($request->hasFile('video_file')) {
                $path = $request->file('video_file')->store('growstream/videos', 'public');
                $data['video_url'] = Storage::url($path);
                $data['upload_status'] = 'processing';
            } elseif ($request->video_url) {
                $data['video_url'] = $request->video_url;
                $data['upload_status'] = 'ready';
            }

            $video = $this->videoRepo->save($data);

            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('growstream/thumbnails', 'public');
                $this->videoRepo->update($video, ['thumbnail_url' => Storage::url($path)]);
            }

            if ($request->categories) {
                $video->categories()->attach($request->categories);
            }

            if ($request->is_published) {
                $this->videoRepo->update($video, ['published_at' => now()]);
            }

            DB::commit();

            return back()->with('success', 'Video uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to upload video: ' . $e->getMessage());
        }
    }

    public function show(int $id)
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return back()->with('error', 'Video not found.');
        }

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

    public function update(Request $request, int $id)
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return back()->with('error', 'Video not found.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|max:5120',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:growstream_categories,id',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
            ];

            if ($video->isDirty('title')) {
                $updateData['slug'] = Str::slug($request->title) . '-' . Str::random(8);
            }

            $this->videoRepo->update($video, $updateData);

            if ($request->hasFile('thumbnail')) {
                if ($video->thumbnail_url) {
                    $oldPath = str_replace('/storage/', '', $video->thumbnail_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $path = $request->file('thumbnail')->store('growstream/thumbnails', 'public');
                $this->videoRepo->update($video, ['thumbnail_url' => Storage::url($path)]);
            }

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

    public function destroy(int $id)
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return back()->with('error', 'Video not found.');
        }

        DB::beginTransaction();
        try {
            if ($video->video_url && Str::startsWith($video->video_url, '/storage/')) {
                $path = str_replace('/storage/', '', $video->video_url);
                Storage::disk('public')->delete($path);
            }

            if ($video->thumbnail_url && Str::startsWith($video->thumbnail_url, '/storage/')) {
                $path = str_replace('/storage/', '', $video->thumbnail_url);
                Storage::disk('public')->delete($path);
            }

            $this->videoRepo->delete($video);

            DB::commit();

            return back()->with('success', 'Video deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete video: ' . $e->getMessage());
        }
    }

    public function publish(int $id)
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return back()->with('error', 'Video not found.');
        }

        $this->videoRepo->update($video, [
            'is_published' => true,
            'published_at' => now(),
        ]);

        return back()->with('success', 'Video published successfully.');
    }

    public function unpublish(int $id)
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            return back()->with('error', 'Video not found.');
        }

        $this->videoRepo->update($video, ['is_published' => false]);

        return back()->with('success', 'Video unpublished successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:publish,unpublish,delete',
            'video_ids' => 'required|array',
            'video_ids.*' => 'exists:growstream_videos,id',
        ]);

        $videos = $this->videoRepo->query()->whereIn('id', $request->video_ids)->get();

        DB::beginTransaction();
        try {
            foreach ($videos as $video) {
                switch ($request->action) {
                    case 'publish':
                        $this->videoRepo->update($video, [
                            'is_published' => true,
                            'published_at' => $video->published_at ?? now(),
                        ]);
                        break;
                    case 'unpublish':
                        $this->videoRepo->update($video, ['is_published' => false]);
                        break;
                    case 'delete':
                        $this->videoRepo->delete($video);
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
