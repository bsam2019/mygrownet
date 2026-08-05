<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class EloquentVideoViewRepository implements VideoViewRepositoryInterface
{
    public function findById(int $id): ?VideoView
    {
        return VideoView::find($id);
    }

    public function recordView(int $videoId, ?int $userId = null, ?string $ip = null, ?string $userAgent = null): VideoView
    {
        return VideoView::create([
            'video_id' => $videoId,
            'user_id' => $userId,
            'watch_duration' => 0,
            'completion_percentage' => 0,
            'ip_address' => $ip,
            'browser' => $userAgent,
            'viewed_at' => now(),
        ]);
    }

    public function getViewsByVideo(int $videoId, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): Collection
    {
        $query = VideoView::where('video_id', $videoId);

        if ($from !== null) {
            $query->where('viewed_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('viewed_at', '<=', $to);
        }

        return $query->get();
    }

    public function getViewsByDate(int $videoId, \DateTimeInterface $date): int
    {
        return VideoView::where('video_id', $videoId)
            ->whereDate('viewed_at', $date)
            ->count();
    }

    public function getTotalViews(int $videoId): int
    {
        return VideoView::where('video_id', $videoId)->count();
    }

    public function getUniqueViewers(int $videoId): int
    {
        return VideoView::where('video_id', $videoId)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');
    }

    public function getViewsAnalytics(int $videoId, string $period = 'daily'): array
    {
        $rows = VideoView::where('video_id', $videoId)
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                $row->date = Carbon::parse($row->date);

                return $row;
            })
            ->keyBy('date');

        if ($period === 'weekly') {
            return $rows
                ->groupBy(fn ($row) => $row->date->format('Y-W'))
                ->map(fn ($group) => [
                    'date' => $group->first()->date->toDateString(),
                    'views' => $group->sum('views'),
                ])
                ->values()
                ->all();
        }

        if ($period === 'monthly') {
            return $rows
                ->groupBy(fn ($row) => $row->date->format('Y-m'))
                ->map(fn ($group) => [
                    'date' => $group->first()->date->toDateString(),
                    'views' => $group->sum('views'),
                ])
                ->values()
                ->all();
        }

        return $rows
            ->map(fn ($row) => [
                'date' => $row->date->toDateString(),
                'views' => (int) $row->views,
            ])
            ->values()
            ->all();
    }

    public function deleteByVideo(int $videoId): void
    {
        VideoView::where('video_id', $videoId)->delete();
    }

    public function sumPremiumWatchSecondsByUser(int $userId, ?\DateTimeInterface $from = null): int
    {
        $query = VideoView::query()
            ->join('growstream_videos as v', 'v.id', '=', 'growstream_video_views.video_id')
            ->where('growstream_video_views.user_id', $userId)
            ->where('v.access_level', '!=', 'free');

        if ($from !== null) {
            $query->where('growstream_video_views.viewed_at', '>=', $from);
        }

        return (int) $query->sum('v.duration');
    }

    public function query(): Builder
    {
        return VideoView::query();
    }
}
