<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\AttributionLink;
use App\Domain\GrowStream\Repositories\AttributionLinkRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentAttributionLinkRepository implements AttributionLinkRepositoryInterface
{
    public function findById(int $id): ?AttributionLink
    {
        return AttributionLink::find($id);
    }

    public function findBySession(string $visitorSessionId): ?AttributionLink
    {
        return AttributionLink::where('visitor_session_id', $visitorSessionId)->first();
    }

    public function save(array $data): AttributionLink
    {
        return AttributionLink::create($data);
    }

    public function update(AttributionLink $link, array $data): AttributionLink
    {
        $link->update($data);

        return $link->fresh();
    }

    public function bindConversion(string $visitorSessionId, int $userId): void
    {
        AttributionLink::where('visitor_session_id', $visitorSessionId)
            ->whereNull('converted_user_id')
            ->update(['converted_user_id' => $userId]);
    }

    public function accumulateWatchMinutes(string $visitorSessionId, int $minutes): void
    {
        if ($minutes <= 0) {
            return;
        }

        AttributionLink::where('visitor_session_id', $visitorSessionId)
            ->increment('watch_minutes_attributed', $minutes);
    }

    public function forCreator(int $creatorId, array $relations = []): \Illuminate\Database\Eloquent\Collection
    {
        return AttributionLink::with($relations)
            ->where('creator_id', $creatorId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function query(): Builder
    {
        return AttributionLink::query();
    }
}
