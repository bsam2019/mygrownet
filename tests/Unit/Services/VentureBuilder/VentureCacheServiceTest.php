<?php

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureCategoryModel;
use App\Services\VentureBuilder\VentureCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->service = new VentureCacheService();
});

describe('getVenturesMarketplace', function () {
    it('returns only funding ventures', function () {
        VentureModel::factory()->funding()->create(['title' => 'Visible']);
        VentureModel::factory()->create(['title' => 'Hidden Draft', 'status' => 'draft']);

        $result = $this->service->getVenturesMarketplace(null, null, 12);

        expect($result['total'])->toBe(1);
        expect($result['data'][0]['title'])->toBe('Visible');
    });

    it('caches results', function () {
        VentureModel::factory()->funding()->create(['title' => 'Cached Venture']);

        $first = $this->service->getVenturesMarketplace(null, null, 12);
        $cachedKey = 'ventures.marketplace.' . md5(serialize([null, null, 12]));
        expect(Cache::has($cachedKey))->toBeTrue();
    });
});

describe('clearVentureCache', function () {
    it('flushes venture tags', function () {
        Cache::tags(['ventures'])->put('test_key', 'value', 300);

        $this->service->clearVentureCache();

        expect(Cache::tags(['ventures'])->get('test_key'))->toBeNull();
    });
});

describe('getAdminDashboardStats', function () {
    it('returns correct stats', function () {
        VentureModel::factory()->funding()->create(['total_raised' => 50000]);
        VentureModel::factory()->funding()->create(['total_raised' => 30000]);
        VentureModel::factory()->create(['status' => 'draft']);

        $stats = $this->service->getAdminDashboardStats();

        expect($stats['total_ventures'])->toBe(3);
        expect($stats['active_ventures'])->toBe(2);
        expect($stats['total_raised'])->toBe(80000.0);
    });

    it('caches admin stats', function () {
        VentureModel::factory()->funding()->create();

        $this->service->getAdminDashboardStats();

        expect(Cache::has('ventures.admin.stats'))->toBeTrue();
    });
});

describe('clearAdminStats', function () {
    it('clears the admin stats cache', function () {
        Cache::put('ventures.admin.stats', ['cached' => true], 300);

        $this->service->clearAdminStats();

        expect(Cache::has('ventures.admin.stats'))->toBeFalse();
    });
});
