<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Services\VideoManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModerationController extends Controller
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo,
        private VideoManagementService $videoManagementService,
    ) {}

    public function queue(Request $request): Response
    {
        $query = $this->videoRepo->query()
            ->where('moderation_status', 'pending_review')
            ->with(['creator.user', 'categories']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        $videos = $query->latest()->paginate(20);

        $stats = [
            'pending' => (clone $query)->count(),
            'approved' => $this->videoRepo->query()->where('moderation_status', 'approved')->count(),
            'rejected' => $this->videoRepo->query()->where('moderation_status', 'rejected')->count(),
        ];

        return Inertia::render('GrowStream/Admin/Moderation', [
            'videos' => $videos,
            'stats' => $stats,
            'filters' => $request->only(['search']),
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $this->videoManagementService->approveVideoReview($id, auth()->id());

        return back()->with('success', 'Video approved.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $this->videoManagementService->rejectVideoReview($id, $request->reason, auth()->id());

        return back()->with('success', 'Video rejected.');
    }

    public function publish(int $id): RedirectResponse
    {
        $this->videoManagementService->approveVideoReview($id, auth()->id());

        $video = $this->videoRepo->findById($id);
        if ($video) {
            $this->videoRepo->update($video, [
                'is_published' => true,
                'published_at' => now(),
            ]);
        }

        return back()->with('success', 'Video approved and published.');
    }
}
