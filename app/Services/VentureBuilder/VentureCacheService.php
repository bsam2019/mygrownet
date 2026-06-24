<?php

namespace App\Services\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use Illuminate\Support\Facades\Cache;

class VentureCacheService
{
    public function getVenturesMarketplace(?string $category, ?string $search, int $perPage = 12): array
    {
        $cacheKey = 'ventures.marketplace.' . md5(serialize([$category, $search, $perPage]));

        return Cache::remember($cacheKey, 300, function () use ($category, $search, $perPage) {
            $query = VentureModel::with(['category', 'creator'])
                ->where('status', 'funding')
                ->when($category, function ($q, $cat) {
                    $q->whereHas('category', fn($cq) => $cq->where('slug', $cat));
                })
                ->when($search, function ($q, $s) {
                    $q->where(function ($wq) use ($s) {
                        $wq->where('title', 'like', "%{$s}%")
                            ->orWhere('description', 'like', "%{$s}%");
                    });
                })
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc');

            return $query->paginate($perPage)->toArray();
        });
    }

    public function clearVentureCache(): void
    {
        Cache::tags(['ventures'])->flush();
    }

    public function getAdminDashboardStats(): array
    {
        return Cache::remember('ventures.admin.stats', 300, function () {
            return [
                'total_ventures' => VentureModel::count(),
                'active_ventures' => VentureModel::whereIn('status', ['funding', 'funded', 'active'])->count(),
                'total_raised' => VentureModel::sum('total_raised'),
                'total_investors' => VentureInvestmentModel::distinct('user_id')->count('user_id'),
                'pending_investments' => VentureInvestmentModel::where('status', 'pending')->count(),
            ];
        });
    }

    public function clearAdminStats(): void
    {
        Cache::forget('ventures.admin.stats');
    }
}
