<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\AnalyticsEvent;
use App\Domain\BizBoost\Repositories\AnalyticsEventRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentAnalyticsEventRepository implements AnalyticsEventRepositoryInterface
{
    public function findByBusiness(int $businessId, array $conditions = []): array
    {
        $query = DB::table('bizboost_analytics_events')->where('business_id', $businessId);

        if (!empty($conditions['event_type'])) {
            $query->where('event_type', $conditions['event_type']);
        }
        if (!empty($conditions['source'])) {
            $query->where('source', $conditions['source']);
        }
        if (!empty($conditions['post_id'])) {
            $query->where('post_id', $conditions['post_id']);
        }
        if (!empty($conditions['recorded_from'])) {
            $query->where('recorded_at', '>=', $conditions['recorded_from']);
        }

        return $query->get()->map(fn($r) => AnalyticsEvent::reconstitute((array) $r))->toArray();
    }

    public function save(AnalyticsEvent $entity): AnalyticsEvent
    {
        $data = $entity->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);

        $id = DB::table('bizboost_analytics_events')->insertGetId($data);
        $row = DB::table('bizboost_analytics_events')->where('id', $id)->first();
        return AnalyticsEvent::reconstitute((array) $row);
    }
}