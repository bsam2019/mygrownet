<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator;

use App\Domain\GrowStream\Infrastructure\Jobs\ProcessVideoJob;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use App\Domain\GrowStream\Jobs\ProcessThumbnailJob;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Services\VideoManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CreatorVideoController extends Controller
{
    public function __construct(
        private CreatorProfileRepositoryInterface $creatorRepo,
        private VideoRepositoryInterface $videoRepo,
        private VideoManagementService $videoManagementService,
    ) {}

    private function requireCreator(): CreatorProfile
    {
        $creator = $this->creatorRepo->findByUserId(auth()->id());

        abort_unless($creator !== null && $creator->status === 'approved', 403, 'Creator application not approved.');

        return $creator;
    }

    public function index(Request $request): Response
    {
        $creator = $this->requireCreator();

        $query = $this->videoRepo->query()
            ->where('creator_id', $creator->id)
            ->with(['categories', 'tags']);

        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->moderation_status);
        }

        $videos = $query->latest()->paginate(20);

        return Inertia::render('GrowStream/Creator/Videos', [
            'videos' => $videos,
            'filters' => $request->only(['moderation_status']),
        ]);
    }

    public function create(): Response
    {
        $this->requireCreator();

        $categories = VideoCategory::orderBy('name')->get();

        return Inertia::render('GrowStream/Creator/Upload', [
            'categories' => $categories,
            'contentTypes' => config('growstream.content_types', []),
            'accessLevels' => config('growstream.access_levels', []),
            'maxFileSize' => (int) config('growstream.upload.max_file_size', 5 * 1024 * 1024 * 1024),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $creator = $this->requireCreator();

        abort_unless($creator->canUploadMore(), 429, 'Monthly upload limit reached.');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'content_type' => 'required|in:'.implode(',', array_keys(config('growstream.content_types', []))),
            'access_level' => 'required|in:'.implode(',', array_keys(config('growstream.access_levels', []))),
            'categories' => 'nullable|array',
            'categories.*' => 'exists:growstream_video_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'video_url' => 'required|url',
            'rights_declaration' => 'required|accepted',
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.config('growstream.thumbnails.max_size', 2048),
                'dimensions:min_width=640,min_height=360',
            ],
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'title' => $request->title,
                'slug' => Str::slug($request->title).'-'.Str::lower(Str::random(8)),
                'description' => $request->description ?? '',
                'content_type' => $request->content_type,
                'access_level' => $request->access_level,
                'creator_id' => $creator->id,
                'is_published' => false,
                'video_provider' => 'local',
                'video_url' => $request->video_url,
                'playback_url' => $request->video_url,
                'upload_status' => 'ready',
                'moderation_status' => $creator->is_verified ? 'approved' : 'pending_review',
            ];

            $video = $this->videoRepo->save($data);

            if ($request->categories) {
                $video->categories()->attach($request->categories);
            }

            if ($request->tags) {
                $this->videoManagementService->syncTags($video->id, $request->tags);
            }

            // Handle custom thumbnail upload (non-blocking)
            if ($request->hasFile('thumbnail')) {
                $this->queueThumbnailProcessing($video->id, $request->file('thumbnail'));
            }

            DB::commit();

            $message = $creator->is_verified
                ? 'Video uploaded successfully and auto-approved.'
                : 'Video uploaded successfully. It is now pending review by our team.';

            return redirect()->route('growstream.creator.videos.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to upload video: '.$e->getMessage());
        }
    }

    /**
     * Initialize a resumable tus upload. Creates a pending video row and a
     * Cloudflare tus session, returning the one-time upload URL the client
     * streams chunks to.
     */
    public function tusInit(Request $request): \Illuminate\Http\JsonResponse
    {
        $creator = $this->requireCreator();

        abort_unless($creator->canUploadMore(), 429, 'Monthly upload limit reached.');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'content_type' => 'required|in:'.implode(',', array_keys(config('growstream.content_types', []))),
            'access_level' => 'required|in:'.implode(',', array_keys(config('growstream.access_levels', []))),
            'file_size' => 'required|integer|min:1|max:'.(int) config('growstream.upload.max_file_size', 5 * 1024 * 1024 * 1024),
            'categories' => 'nullable|array',
            'categories.*' => 'exists:growstream_video_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'rights_declaration' => 'required|accepted',
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.config('growstream.thumbnails.max_size', 2048),
                'dimensions:min_width=640,min_height=360',
            ],
        ]);

        $provider = VideoProviderFactory::make();

        $tus = $provider->createTusUpload(
            (int) $request->file_size,
            [
                'max_duration_seconds' => (int) config('growstream.upload.max_duration_seconds', 10800),
                'name' => $request->title,
            ],
        );

        DB::beginTransaction();
        try {
            $video = $this->videoRepo->save([
                'title' => $request->title,
                'slug' => Str::slug($request->title).'-'.Str::lower(Str::random(8)),
                'description' => $request->description ?? '',
                'content_type' => $request->content_type,
                'access_level' => $request->access_level,
                'creator_id' => $creator->id,
                'is_published' => false,
                'upload_status' => 'uploading',
                'moderation_status' => $creator->is_verified ? 'approved' : 'pending_review',
                'video_provider' => (string) config('growstream.default_provider', 'digitalocean'),
                'provider_video_id' => $tus['uid'],
            ]);

            if ($request->categories) {
                $video->categories()->attach($request->categories);
            }

            if ($request->tags) {
                $this->videoManagementService->syncTags($video->id, $request->tags);
            }

            // Handle custom thumbnail upload (non-blocking)
            if ($request->hasFile('thumbnail')) {
                $this->queueThumbnailProcessing($video->id, $request->file('thumbnail'));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            try {
                $provider->delete($tus['uid']);
            } catch (\Throwable $ignored) {
            }

            return response()->json(['error' => 'Failed to initialize upload: '.$e->getMessage()], 422);
        }

        return response()->json([
            'video_id' => $video->id,
            'provider_video_id' => $tus['uid'],
            'upload_url' => $tus['upload_url'],
        ]);
    }

    /**
     * Mark a tus upload complete. The client has finished streaming all bytes
     * to Cloudflare; we flip the video to processing so the pipeline finalizes it.
     */
    public function tusComplete(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $creator = $this->requireCreator();

        $video = $this->videoRepo->findById($id);
        abort_unless($video !== null && $video->creator_id === $creator->id, 404);
        abort_unless($video->upload_status === 'uploading', 422, 'Video is not in an uploading state.');

        $provider = VideoProviderFactory::make();

        $updates = [
            'upload_status' => 'processing',
            'processing_started_at' => now(),
        ];

        try {
            $details = $provider->getVideo($video->provider_video_id);
            $updates['playback_url'] = $details->playbackUrl;
            $updates['thumbnail_url'] = $details->thumbnailUrl;
        } catch (\Throwable $e) {
            // Cloudflare may still be transcoding; the ProcessVideoJob poller
            // will backfill playback/thumbnail URLs once ready.
        }

        $this->videoRepo->update($video, $updates);

        ProcessVideoJob::dispatch($video->id);

        return response()->json(['success' => true]);
    }

    public function edit(int $id): Response|RedirectResponse
    {
        $creator = $this->requireCreator();

        $video = $this->videoRepo->findById($id);
        abort_unless($video !== null && $video->creator_id === $creator->id, 404);

        $video->load(['categories', 'tags']);

        return Inertia::render('GrowStream/Creator/Upload', [
            'video' => $video,
            'categories' => VideoCategory::orderBy('name')->get(),
            'contentTypes' => config('growstream.content_types', []),
            'accessLevels' => config('growstream.access_levels', []),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $creator = $this->requireCreator();

        $video = $this->videoRepo->findById($id);
        abort_unless($video !== null && $video->creator_id === $creator->id, 404);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'content_type' => 'required|in:'.implode(',', array_keys(config('growstream.content_types', []))),
            'access_level' => 'required|in:'.implode(',', array_keys(config('growstream.access_levels', []))),
            'categories' => 'nullable|array',
            'categories.*' => 'exists:growstream_video_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.config('growstream.thumbnails.max_size', 2048),
                'dimensions:min_width=640,min_height=360',
            ],
        ]);

        DB::beginTransaction();
        try {
            $this->videoRepo->update($video, [
                'title' => $request->title,
                'description' => $request->description ?? '',
                'content_type' => $request->content_type,
                'access_level' => $request->access_level,
                'slug' => Str::slug($request->title).'-'.Str::lower(Str::random(8)),
            ]);

            $video->categories()->sync($request->categories ?? []);
            $this->videoManagementService->syncTags($video->id, $request->tags ?? []);

            // Handle custom thumbnail upload (non-blocking)
            if ($request->hasFile('thumbnail')) {
                $this->queueThumbnailProcessing($video->id, $request->file('thumbnail'));
            }

            DB::commit();

            return redirect()->route('growstream.creator.videos.index')
                ->with('success', 'Video updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to update video: '.$e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $creator = $this->requireCreator();

        $video = $this->videoRepo->findById($id);
        abort_unless($video !== null && $video->creator_id === $creator->id, 404);

        $this->videoRepo->delete($video);

        return redirect()->route('growstream.creator.videos.index')
            ->with('success', 'Video deleted successfully.');
    }

    public function analytics(): Response|RedirectResponse
    {
        $creator = $this->requireCreator();

        $videos = $this->videoRepo->findByCreator($creator->id, ['views']);

        $totalViews = $videos->sum('view_count');
        $publishedCount = $videos->filter(fn ($video) => $video->is_published)->count();
        $totalWatchTime = $videos->sum('total_watch_time');
        $avgWatchTime = $publishedCount > 0 ? (int) round($totalWatchTime / max(1, $publishedCount)) : 0;

        $topVideos = $videos->sortByDesc('view_count')->take(5)->values();

        $attribution = [];
        if (auth()->user()->id === $creator->user_id || auth()->user()->hasRole(['admin', 'superadmin', 'Administrator'])) {
            $attribution = $this->attributionSummary($creator->id);
        }

        return Inertia::render('GrowStream/Creator/Analytics', [
            'stats' => [
                'total_videos' => $videos->count(),
                'published_videos' => $publishedCount,
                'total_views' => $totalViews,
                'total_watch_time_hours' => round($totalWatchTime / 3600, 2),
                'avg_watch_time_seconds' => $avgWatchTime,
            ],
            'topVideos' => $topVideos,
            'attribution' => $attribution,
        ]);
    }

    /**
     * Aggregate attribution (silent tracking) for the creator's share links.
     */
    private function attributionSummary(int $creatorId): array
    {
        $events = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\AttributionLink::where('creator_id', $creatorId)
            ->selectRaw('source, COUNT(*) as clicks, SUM(CASE WHEN converted_user_id IS NOT NULL THEN 1 ELSE 0 END) as conversions')
            ->groupBy('source')
            ->get();

        $totalClicks = $events->sum('clicks');
        $totalConversions = $events->sum('conversions');
        $rate = $totalClicks > 0 ? round(($totalConversions / $totalClicks) * 100, 1) : 0;

        return [
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'conversion_rate' => $rate,
            'sources' => $events->map(fn ($e) => [
                'source' => $e->source ?: 'direct',
                'clicks' => (int) $e->clicks,
                'conversions' => (int) $e->conversions,
            ])->values(),
        ];
    }

    public function payouts(): Response|RedirectResponse
    {
        $creator = $this->requireCreator();

        $earningsRepo = app(\App\Domain\GrowStream\Repositories\CreatorEarningRepositoryInterface::class);
        $payoutRepo = app(\App\Domain\GrowStream\Repositories\CreatorPayoutRepositoryInterface::class);

        $totalEarnings = (float) ($creator->total_earnings ?? 0);
        $pending = $earningsRepo->totalPendingForCreator($creator->id);
        $totalPaid = $payoutRepo->totalPaidForCreator($creator->id);

        $payouts = $payoutRepo->forCreator($creator->id)->take(10);
        $lastPayout = $payouts->first();

        return Inertia::render('GrowStream/Creator/Payouts', [
            'summary' => [
                'total_balance' => round($totalEarnings, 2),
                'total_paid' => round($totalPaid, 2),
                'last_payout_amount' => round((float) ($lastPayout->amount ?? 0), 2),
                'last_payout_date' => $lastPayout->paid_at ?? $lastPayout->created_at ?? null,
                'threshold' => (float) config('growstream.creator.minimum_payout', 100),
                'pending' => round($pending, 2),
            ],
            'payouts' => $payouts,
        ]);
    }

    /**
     * Queue thumbnail processing job for uploaded thumbnail file.
     * Stores file temporarily and dispatches background job.
     */
    private function queueThumbnailProcessing(int $videoId, \Illuminate\Http\UploadedFile $thumbnail): void
    {
        // Store thumbnail temporarily in local storage
        $tempPath = 'thumbnails/temp/'.Str::random(40).'.'.$thumbnail->extension();
        Storage::disk('local')->putFileAs(
            dirname($tempPath),
            $thumbnail,
            basename($tempPath)
        );

        // Dispatch job to process in background (non-blocking)
        ProcessThumbnailJob::dispatch($videoId, $tempPath);
    }
}
