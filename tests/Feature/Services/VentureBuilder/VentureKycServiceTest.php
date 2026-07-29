<?php

use App\Services\VentureBuilder\VentureKycService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new VentureKycService();
});

describe('requiresKyc', function () {
    it('returns true when total confirmed investment is >= 10000', function () {
        $user = User::factory()->create();
        $venture = \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel::factory()->funding()->create();
        \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $user->id,
            'venture_id' => $venture->id,
            'amount' => 12000,
        ]);

        expect($this->service->requiresKyc($user))->toBeTrue();
    });

    it('returns false when total confirmed investment is below 10000', function () {
        $user = User::factory()->create();
        $venture = \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel::factory()->funding()->create();
        \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $user->id,
            'venture_id' => $venture->id,
            'amount' => 5000,
        ]);

        expect($this->service->requiresKyc($user))->toBeFalse();
    });

    it('returns false when user has no investments', function () {
        $user = User::factory()->create();

        expect($this->service->requiresKyc($user))->toBeFalse();
    });
});

describe('isKycVerified', function () {
    it('returns true when user has id_verified_at', function () {
        $user = User::factory()->create(['id_verified_at' => now()]);

        expect($this->service->isKycVerified($user))->toBeTrue();
    });

    it('returns false when user has no id_verified_at', function () {
        $user = User::factory()->create(['id_verified_at' => null]);

        expect($this->service->isKycVerified($user))->toBeFalse();
    });
});

describe('canInvest', function () {
    it('returns empty issues for small investment by verified user', function () {
        $user = User::factory()->create(['id_verified_at' => now()]);

        $issues = $this->service->canInvest($user, 5000);

        expect($issues)->toBeEmpty();
    });

    it('flags KYC issue for investment >= 10000 when unverified', function () {
        $user = User::factory()->create(['id_verified_at' => null]);

        $issues = $this->service->canInvest($user, 10000);

        expect($issues)->toHaveCount(1);
        expect($issues[0])->toContain('KYC verification required');
    });

    it('no KYC issue for investment below 10000 when unverified', function () {
        $user = User::factory()->create(['id_verified_at' => null]);

        $issues = $this->service->canInvest($user, 5000);

        expect($issues)->toBeEmpty();
    });
});
