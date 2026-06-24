<?php

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Services\VentureBuilder\VentureService;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

uses(RefreshDatabase::class);

const VALID_TRANSITIONS = [
    'draft' => ['review', 'cancelled'],
    'review' => ['approved', 'draft', 'cancelled'],
    'approved' => ['funding', 'cancelled'],
    'funding' => ['funded', 'cancelled'],
    'funded' => ['active', 'cancelled'],
    'active' => ['completed'],
];

beforeEach(function () {
    $this->service = new VentureService();
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

describe('transitionStatus', function () {
    foreach (VALID_TRANSITIONS as $from => $tos) {
        foreach ($tos as $to) {
            it("allows transition from {$from} to {$to}", function () use ($from, $to) {
                $venture = VentureModel::factory()->create(['status' => $from]);

                $result = $this->service->transitionStatus($venture, $to);

                expect($result->status)->toBe($to);
            });
        }
    }

    it('disallows invalid transitions', function () {
        $venture = VentureModel::factory()->create(['status' => 'draft']);

        expect(fn () => $this->service->transitionStatus($venture, 'active'))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('logs audit event on status change', function () {
        $venture = VentureModel::factory()->create(['status' => 'draft']);

        $this->service->transitionStatus($venture, 'review');

        expect(AuditLog::where('event_type', 'venture_status_changed')->count())->toBe(1);
    });

    it('dispatches status changed event', function () {
        $venture = VentureModel::factory()->create(['status' => 'draft']);
        Illuminate\Support\Facades\Event::fake();

        $this->service->transitionStatus($venture, 'review');

        Illuminate\Support\Facades\Event::assertDispatched(\App\Events\VentureBuilder\VentureStatusChanged::class);
    });
});

describe('calculateShares', function () {
    it('calculates shares based on share price', function () {
        $venture = VentureModel::factory()->create(['share_price' => 200]);

        $shares = $this->service->calculateShares(10000, $venture);

        expect($shares)->toBe(50.0);
    });

    it('uses default K100 when share_price is null', function () {
        $venture = VentureModel::factory()->create(['share_price' => null]);

        $shares = $this->service->calculateShares(5000, $venture);

        expect($shares)->toBe(50.0);
    });

    it('returns 0 when amount is less than share price', function () {
        $venture = VentureModel::factory()->create(['share_price' => 500]);

        $shares = $this->service->calculateShares(100, $venture);

        expect($shares)->toBe(0.0);
    });
});

describe('registerCompany', function () {
    it('registers a company for funded ventures', function () {
        $venture = VentureModel::factory()->funded()->create();

        $result = $this->service->registerCompany($venture, 'Test Corp', 'REG-001', 10.5);

        expect($result->company_name)->toBe('Test Corp');
        expect($result->company_registration_number)->toBe('REG-001');
        expect($result->company_formation_date->isToday())->toBeTrue();
        expect($result->mygrownet_equity_percentage)->toBe(10.5);
    });

    it('rejects company registration for non-funded ventures', function () {
        $venture = VentureModel::factory()->create(['status' => 'draft']);

        expect(fn () => $this->service->registerCompany($venture, 'Test Corp', 'REG-001'))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('logs audit event on company registration', function () {
        $venture = VentureModel::factory()->funded()->create();

        $this->service->registerCompany($venture, 'Test Corp', 'REG-001');

        expect(AuditLog::where('event_type', 'venture_company_registered')->count())->toBe(1);
    });
});

describe('checkFundingComplete', function () {
    it('marks venture as funded when target reached', function () {
        $venture = VentureModel::factory()->funding()->create([
            'funding_target' => 50000,
            'total_raised' => 50000,
        ]);

        $completed = $this->service->checkFundingComplete($venture);

        expect($completed)->toBeTrue();
        expect($venture->fresh()->status)->toBe('funded');
    });

    it('does not change status when target not reached', function () {
        $venture = VentureModel::factory()->funding()->create([
            'funding_target' => 50000,
            'total_raised' => 25000,
        ]);

        $completed = $this->service->checkFundingComplete($venture);

        expect($completed)->toBeFalse();
        expect($venture->fresh()->status)->toBe('funding');
    });

    it('dispatches funding completed event', function () {
        $venture = VentureModel::factory()->funding()->create([
            'funding_target' => 50000,
            'total_raised' => 50000,
        ]);
        Illuminate\Support\Facades\Event::fake();

        $this->service->checkFundingComplete($venture);

        Illuminate\Support\Facades\Event::assertDispatched(\App\Events\VentureBuilder\VentureFundingCompleted::class);
    });
});
