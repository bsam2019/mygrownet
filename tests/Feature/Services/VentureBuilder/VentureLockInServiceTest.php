<?php

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Services\VentureBuilder\VentureLockInService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new VentureLockInService();
    $this->user = User::factory()->create();
});

describe('isLockInPeriodActive', function () {
    it('returns true when within 12 months of payment', function () {
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'payment_confirmed_at' => now()->subMonths(1),
        ]);

        expect($this->service->isLockInPeriodActive($investment))->toBeTrue();
    });

    it('returns false when beyond 12 months', function () {
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'payment_confirmed_at' => now()->subMonths(13),
        ]);

        expect($this->service->isLockInPeriodActive($investment))->toBeFalse();
    });

    it('uses created_at fallback when payment_confirmed_at is null', function () {
        $investment = VentureInvestmentModel::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'payment_confirmed_at' => null,
            'created_at' => now()->subMonths(1),
        ]);

        expect($this->service->isLockInPeriodActive($investment))->toBeTrue();
    });
});

describe('getRemainingLockInDays', function () {
    it('returns days remaining in lock-in period', function () {
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'payment_confirmed_at' => now()->subMonths(6),
        ]);

        $days = $this->service->getRemainingLockInDays($investment);

        expect($days)->toBeGreaterThan(0);
        expect($days)->toBeLessThanOrEqual(184);
    });

    it('returns 0 when lock-in period has expired', function () {
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'payment_confirmed_at' => now()->subMonths(13),
        ]);

        expect($this->service->getRemainingLockInDays($investment))->toBe(0);
    });
});

describe('assertNotLocked', function () {
    it('throws when lock-in is active', function () {
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'payment_confirmed_at' => now()->subMonths(1),
        ]);

        expect(fn () => $this->service->assertNotLocked($investment))
            ->toThrow(\RuntimeException::class, 'lock-in');
    });

    it('passes when lock-in has expired', function () {
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'payment_confirmed_at' => now()->subMonths(13),
        ]);

        $this->service->assertNotLocked($investment);

        expect(true)->toBeTrue();
    });
});
